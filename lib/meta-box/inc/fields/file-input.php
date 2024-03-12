<?php
defined( 'ABSPATH' ) || die;

/**
 * The file input field which allows users to enter a file URL or select it from the Media Library.
 */
class SWPMB_File_Input_Field extends SWPMB_Input_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_style( 'swpmb-file-input', SWPMB_CSS_URL . 'file-input.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-file-input', 'path', SWPMB_CSS_DIR . 'file-input.css' );
		wp_enqueue_script( 'swpmb-file-input', SWPMB_JS_URL . 'file-input.js', [ 'jquery' ], SWPMB_VER, true );
		SWPMB_Helpers_Field::localize_script_once( 'swpmb-file-input', 'swpmbFileInput', [
			'frameTitle' => esc_html__( 'Select File', 'meta-box' ),
		] );
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
		$attributes = self::get_attributes( $field, $meta );
		$meta_array = explode( '.', $meta );
		$file_ext   = strtolower( end( $meta_array ) );
		$extensions = [ 'jpeg', 'jpg', 'png', 'gif' ];
		return sprintf(
			'<div class="swpmb-file-input-image %s">
				<img src="%s">
			</div>
			<div class="swpmb-file-input-inner">
				<input %s>
				<a href="#" class="swpmb-file-input-select button">%s</a>
				<a href="#" class="swpmb-file-input-remove button %s">%s</a>
			</div>',
			in_array( $file_ext, $extensions, true ) ? '' : 'swpmb-file-input-hidden',
			$meta,
			self::render_attributes( $attributes ),
			esc_html__( 'Select', 'meta-box' ),
			$meta ? '' : 'hidden',
			esc_html__( 'Remove', 'meta-box' )
		);
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes         = parent::get_attributes( $field, $value );
		$attributes['type'] = 'text';

		return $attributes;
	}
}
