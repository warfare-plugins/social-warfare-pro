<?php
defined( 'ABSPATH' ) || die;

/**
 * The file upload field which allows users to drag and drop files to upload.
 */
class SWPMB_File_Upload_Field extends SWPMB_Media_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'swpmb-upload', SWPMB_CSS_URL . 'upload.css', [ 'swpmb-media' ], SWPMB_VER );
		wp_style_add_data( 'swpmb-upload', 'path', SWPMB_CSS_DIR . 'upload.css' );
		wp_enqueue_script( 'swpmb-file-upload', SWPMB_JS_URL . 'file-upload.js', [ 'swpmb-media' ], SWPMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'max_file_size' => 0,
		] );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'maxFileSize' => $field['max_file_size'],
		] );

		return $field;
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		require SWPMB_INC_DIR . 'templates/upload.php';
	}
}
