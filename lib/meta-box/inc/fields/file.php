<?php
defined( 'ABSPATH' ) || die;

/**
 * The file upload file which allows users to upload files via the default HTML <input type="file">.
 */
class SWPMB_File_Field extends SWPMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-file', SWPMB_CSS_URL . 'file.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-file', 'path', SWPMB_CSS_DIR . 'file.css' );
		wp_enqueue_script( 'swpmb-file', SWPMB_JS_URL . 'file.js', [ 'jquery-ui-sortable' ], SWPMB_VER, true );

		SWPMB_Helpers_Field::localize_script_once( 'swpmb-file', 'swpmbFile', [
			// Translators: %d is the number of files in singular form.
			'maxFileUploadsSingle' => __( 'You may only upload maximum %d file', 'meta-box' ),
			// Translators: %d is the number of files in plural form.
			'maxFileUploadsPlural' => __( 'You may only upload maximum %d files', 'meta-box' ),
		] );
	}

	public static function add_actions() {
		add_action( 'post_edit_form_tag', [ __CLASS__, 'post_edit_form_tag' ] );
		add_action( 'wp_ajax_swpmb_delete_file', [ __CLASS__, 'ajax_delete_file' ] );
	}

	public static function post_edit_form_tag() {
		echo ' enctype="multipart/form-data"';
	}

	public static function ajax_delete_file() {
		$request  = swpmb_request();
		$field_id = (string) $request->filter_post( 'field_id' );
		$type     = str_contains( $request->filter_post( 'field_name' ), '[' ) ? 'child' : 'top';
		check_ajax_referer( "swpmb-delete-file_{$field_id}" );

		if ( 'child' === $type ) {
			$field_group = explode( '[', $request->filter_post( 'field_name' ) );
			$field_id    = $field_group[0]; // This is top parent field_id.
		}
		// Make sure the file to delete is in the custom field.
		$attachment  = $request->post( 'attachment_id' );
		$object_id   = $request->filter_post( 'object_id' );
		$object_type = (string) $request->filter_post( 'object_type' );
		$field       = swpmb_get_field_settings( $field_id, [ 'object_type' => $object_type ], $object_id );
		$field_value = self::raw_meta( $object_id, $field );

		if ( ! self::in_array_r( $attachment, $field_value ) ) {
			wp_send_json_error( __( 'Error: Invalid file', 'meta-box' ) );
		}
		// Delete the file.
		if ( is_numeric( $attachment ) ) {
			$result = wp_delete_attachment( $attachment );
		} else {
			$path   = str_replace( home_url( '/' ), trailingslashit( ABSPATH ), $attachment );
			$result = unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
		}

		if ( $result ) {
			wp_send_json_success();
		}
		wp_send_json_error( __( 'Error: Cannot delete file', 'meta-box' ) );
	}

	/**
	 * Recursively search needle in haystack
	 */
	protected static function in_array_r( $needle, $haystack, $strict = false ) : bool {
		foreach ( $haystack as $item ) {
			if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && self::in_array_r( $needle, $item, $strict ) ) ) {
					return true;
			}
		}
		return false;
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 *
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$meta      = array_filter( (array) $meta );
		$i18n_more = apply_filters( 'swpmb_file_add_string', _x( '+ Add new file', 'file upload', 'meta-box' ), $field );
		$html      = self::get_uploaded_files( $meta, $field );

		// Show form upload.
		$attributes          = self::get_attributes( $field, $meta );
		$attributes['type']  = 'file';
		$attributes['name']  = "{$field['input_name']}[]";
		$attributes['class'] = 'swpmb-file-input';

		/*
		 * Use JavaScript to toggle 'required' attribute, because:
		 * - Field might already have value (uploaded files).
		 * - Be able to detect when uploading multiple files.
		 */
		if ( $attributes['required'] ) {
			$attributes['data-required'] = 1;
			$attributes['required']      = false;
		}

		// Upload new files.
		$html .= sprintf(
			'<div class="swpmb-file-new"><input %s>',
			self::render_attributes( $attributes )
		);
		if ( 1 !== $field['max_file_uploads'] ) {
			$html .= sprintf(
				'<a class="swpmb-file-add" href="#"><strong>%s</strong></a>',
				$i18n_more
			);
		}
		$html .= '</div>';

		$html .= sprintf(
			'<input type="hidden" class="swpmb-file-index" name="%s" value="%s">',
			$field['index_name'],
			$field['input_name']
		);

		return $html;
	}

	/**
	 * Get HTML for uploaded files.
	 *
	 * @param array $files List of uploaded files.
	 * @param array $field Field parameters.
	 * @return string
	 */
	protected static function get_uploaded_files( $files, $field ) {
		$delete_nonce = wp_create_nonce( "swpmb-delete-file_{$field['id']}" );
		$output       = '';

		foreach ( (array) $files as $k => $file ) {
			// Ignore deleted files (if users accidentally deleted files or uses `force_delete` without saving post).
			if ( get_attached_file( $file ) || $field['upload_dir'] ) {
				$output .= static::file_html( $file, $k, $field );
			}
		}

		return sprintf(
			'<ul class="swpmb-files" data-field_id="%s" data-field_name="%s" data-delete_nonce="%s" data-force_delete="%s" data-max_file_uploads="%s" data-mime_type="%s">%s</ul>',
			$field['id'],
			$field['field_name'],
			$delete_nonce,
			$field['force_delete'] ? 1 : 0,
			$field['max_file_uploads'],
			$field['mime_type'],
			$output
		);
	}

	/**
	 * Get HTML for uploaded file.
	 *
	 * @param int   $file  Attachment (file) ID.
	 * @param int   $index File index.
	 * @param array $field Field data.
	 * @return string
	 */
	protected static function file_html( $file, $index, $field ) {
		$i18n_delete = apply_filters( 'swpmb_file_delete_string', _x( 'Delete', 'file upload', 'meta-box' ) );
		$i18n_edit   = apply_filters( 'swpmb_file_edit_string', _x( 'Edit', 'file upload', 'meta-box' ) );
		$attributes  = self::get_attributes( $field, $file );

		if ( ! $file ) {
			return '';
		}

		if ( $field['upload_dir'] ) {
			$data = self::file_info_custom_dir( $file, $field );
		} else {
			$data      = [
				'icon'      => wp_get_attachment_image( $file, [ 48, 64 ], true ),
				'name'      => basename( get_attached_file( $file ) ),
				'url'       => wp_get_attachment_url( $file ),
				'title'     => get_the_title( $file ),
				'edit_link' => '',
			];
			$edit_link = get_edit_post_link( $file );
			if ( $edit_link ) {
				$data['edit_link'] = sprintf( '<a href="%s" class="swpmb-file-edit" target="_blank">%s</a>', $edit_link, $i18n_edit );
			}
		}

		return sprintf(
			'<li class="swpmb-file">
				<div class="swpmb-file-icon">%s</div>
				<div class="swpmb-file-info">
					<a href="%s" target="_blank" class="swpmb-file-title">%s</a>
					<div class="swpmb-file-name">%s</div>
					<div class="swpmb-file-actions">
						%s
						<a href="#" class="swpmb-file-delete" data-attachment_id="%s">%s</a>
					</div>
				</div>
				<input type="hidden" name="%s[%s]" value="%s">
			</li>',
			$data['icon'],
			esc_url( $data['url'] ),
			esc_html( $data['title'] ),
			esc_html( $data['name'] ),
			$data['edit_link'],
			esc_attr( $file ),
			esc_html( $i18n_delete ),
			esc_attr( $attributes['name'] ),
			esc_attr( $index ),
			esc_attr( $file )
		);
	}

	protected static function file_info_custom_dir( string $file, array $field ) : array {
		$path     = wp_normalize_path( trailingslashit( $field['upload_dir'] ) . basename( $file ) );
		$ext      = pathinfo( $path, PATHINFO_EXTENSION );
		$icon_url = wp_mime_type_icon( wp_ext2type( $ext ) );
		$data     = [
			'icon'      => '<img width="48" height="64" src="' . esc_url( $icon_url ) . '" alt="">',
			'name'      => basename( $path ),
			'path'      => $path,
			'url'       => $file,
			'title'     => preg_replace( '/\.[^.]+$/', '', basename( $path ) ),
			'edit_link' => '',
		];
		return $data;
	}

	/**
	 * Get meta values to save.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return array|mixed
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$input = $field['index'] ?? $field['input_name'];

		// @codingStandardsIgnoreLine
		if ( empty( $input ) || empty( $_FILES[ $input ] ) ) {
			return $new;
		}

		$new = array_filter( (array) $new );

		$count = self::transform( $input );
		for ( $i = 0; $i < $count; $i ++ ) {
			$attachment = self::handle_upload( "{$input}_{$i}", $post_id, $field );
			if ( $attachment && ! is_wp_error( $attachment ) ) {
				$new[] = $attachment;
			}
		}

		return $new;
	}

	/**
	 * Get meta values to save for cloneable fields.
	 *
	 * @param array $new         The submitted meta value.
	 * @param array $old         The existing meta value.
	 * @param int   $object_id   The object ID.
	 * @param array $field       The field settings.
	 * @param array $data_source Data source. Either $_POST or custom array. Used in group to get uploaded files.
	 *
	 * @return mixed
	 */
	public static function clone_value( $new, $old, $object_id, $field, $data_source = null ) {
		if ( ! $data_source ) {
			// @codingStandardsIgnoreLine
			$data_source = $_POST;
		}

		$indexes = $data_source[ "_index_{$field['id']}" ] ?? [];
		foreach ( $indexes as $key => $index ) {
			$field['index'] = $index;

			$old_value   = $old[ $key ] ?? [];
			$value       = $new[ $key ] ?? [];
			$value       = self::value( $value, $old_value, $object_id, $field );
			$new[ $key ] = self::filter( 'sanitize', $value, $field, $old_value, $object_id );
		}

		return $new;
	}

	/**
	 * Handle file upload.
	 * Consider upload to Media Library or custom folder.
	 *
	 * @param string $file_id File ID in $_FILES when uploading.
	 * @param int    $post_id Post ID.
	 * @param array  $field   Field settings.
	 *
	 * @return \WP_Error|int|string WP_Error if has error, attachment ID if upload in Media Library, URL to file if upload to custom folder.
	 */
	protected static function handle_upload( $file_id, $post_id, $field ) {
		return $field['upload_dir'] ? self::handle_upload_custom_dir( $file_id, $field ) : media_handle_upload( $file_id, $post_id );
	}

	/**
	 * Transform $_FILES from $_FILES['field']['key']['index'] to $_FILES['field_index']['key'].
	 *
	 * @param string $input_name The field input name.
	 *
	 * @return int The number of uploaded files.
	 */
	protected static function transform( $input_name ): int {
		// phpcs:disable
		foreach ( $_FILES[ $input_name ] as $key => $list ) {
			foreach ( $list as $index => $value ) {
				$file_key = sanitize_text_field( "{$input_name}_{$index}" );
				if ( ! isset( $_FILES[ $file_key ] ) ) {
					$_FILES[ $file_key ] = [];
				}
				$_FILES[ $file_key ][ $key ] = $value;
			}
		}

		return count( $_FILES[ $input_name ]['name'] );
		// phpcs:enable
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'std'                      => [],
			'force_delete'             => false,
			'max_file_uploads'         => 0,
			'mime_type'                => '',
			'upload_dir'               => '',
			'unique_filename_callback' => null,
		] );

		$field['multiple']   = true;
		$field['input_name'] = "_file_{$field['id']}";
		$field['index_name'] = "_index_{$field['id']}";

		return $field;
	}

	/**
	 * Get the field value. Return meaningful info of the files.
	 *
	 * @param  array    $field   Field parameters.
	 * @param  array    $args    Not used for this field.
	 * @param  int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return mixed Full info of uploaded files
	 */
	public static function get_value( $field, $args = [], $post_id = null ) {
		$value = parent::get_value( $field, $args, $post_id );
		if ( ! $field['clone'] ) {
			$value = static::files_info( $field, $value, $args );
		} else {
			$return = [];
			foreach ( $value as $subvalue ) {
				$return[] = static::files_info( $field, $subvalue, $args );
			}
			$value = $return;
		}
		if ( isset( $args['limit'] ) ) {
			$value = array_slice( $value, 0, intval( $args['limit'] ) );
		}
		return $value;
	}

	/**
	 * Get uploaded files information.
	 *
	 * @param array $field Field parameters.
	 * @param array $files Files IDs.
	 * @param array $args  Additional arguments (for image size).
	 * @return array
	 */
	public static function files_info( $field, $files, $args ) {
		$return = [];
		foreach ( (array) $files as $file ) {
			$info = static::file_info( $file, $args, $field );
			if ( $info ) {
				$return[ $file ] = $info;
			}
		}
		return $return;
	}

	/**
	 * Get uploaded file information.
	 *
	 * @param int   $file  Attachment file ID (post ID). Required.
	 * @param array $args  Array of arguments (for size).
	 * @param array $field Field settings.
	 *
	 * @return array|bool False if file not found. Array of (id, name, path, url) on success.
	 */
	public static function file_info( $file, $args = [], $field = [] ) {
		if ( ! empty( $field['upload_dir'] ) ) {
			return self::file_info_custom_dir( $file, $field );
		}

		$path = get_attached_file( $file );
		if ( ! $path ) {
			return false;
		}

		return wp_parse_args(
			[
				'ID'    => $file,
				'name'  => basename( $path ),
				'path'  => $path,
				'url'   => wp_get_attachment_url( $file ),
				'title' => get_the_title( $file ),
			],
			wp_get_attachment_metadata( $file )
		);
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param array    $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $value['url'] ), esc_html( $value['title'] ) );
	}

	/**
	 * Handle upload for files in custom directory.
	 *
	 * @param string $file_id File ID in $_FILES when uploading.
	 * @param array  $field   Field settings.
	 *
	 * @return string URL to uploaded file.
	 */
	public static function handle_upload_custom_dir( $file_id, $field ) {
		// @codingStandardsIgnoreStart
		if ( empty( $_FILES[ $file_id ] ) ) {
			return;
		}
		$file = $_FILES[ $file_id ];
		// @codingStandardsIgnoreEnd

		// Use a closure to filter upload directory. Requires PHP >= 5.3.0.
		$filter_upload_dir = function( $uploads ) use ( $field ) {
			$uploads['path']    = $field['upload_dir'];
			$uploads['url']     = self::convert_path_to_url( $field['upload_dir'] );
			$uploads['subdir']  = '';
			$uploads['basedir'] = $field['upload_dir'];

			return $uploads;
		};

		// Make sure upload dir is inside WordPress.
		$upload_dir = wp_normalize_path( untrailingslashit( $field['upload_dir'] ) );
		$root       = wp_normalize_path( untrailingslashit( ABSPATH ) );
		if ( ! str_starts_with( $upload_dir, $root ) ) {
			return;
		}

		// Let WordPress handle upload to the custom directory.
		add_filter( 'upload_dir', $filter_upload_dir );
		$overrides = [
			'test_form'                => false,
			'unique_filename_callback' => $field['unique_filename_callback'],
		];
		$file_info = wp_handle_upload( $file, $overrides );
		remove_filter( 'upload_dir', $filter_upload_dir );

		return empty( $file_info['url'] ) ? null : $file_info['url'];
	}

	public static function convert_path_to_url( string $path ) : string {
		$path          = wp_normalize_path( untrailingslashit( $path ) );
		$root          = wp_normalize_path( untrailingslashit( ABSPATH ) );
		$relative_path = str_replace( $root, '', $path );

		return home_url( $relative_path );
	}
}
