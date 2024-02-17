<?php
defined( 'ABSPATH' ) || die;

/**
 * The image upload field which allows users to drag and drop images.
 */
class SWPMB_Image_Upload_Field extends SWPMB_Image_Advanced_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		SWPMB_File_Upload_Field::admin_enqueue_scripts();
		wp_enqueue_script( 'swpmb-image-upload', SWPMB_JS_URL . 'image-upload.js', [ 'swpmb-file-upload', 'swpmb-image-advanced' ], SWPMB_VER, true );
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
		return SWPMB_File_Upload_Field::normalize( $field );
	}

	/**
	 * Template for media item.
	 */
	public static function print_templates() {
		parent::print_templates();
		SWPMB_File_Upload_Field::print_templates();
	}
}
