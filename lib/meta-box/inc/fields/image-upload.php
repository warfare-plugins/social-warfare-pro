<?php
/**
 * The image upload field which allows users to drag and drop images.
 *
 * @package Meta Box
 */

/**
 * File advanced field class which users WordPress media popup to upload and select files.
 */
class SWPMB_Image_Upload_Field extends SWPMB_Image_Advanced_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		SWPMB_File_Upload_Field::admin_enqueue_scripts();
		wp_enqueue_script( 'swpmb-image-upload', SWPMB_JS_URL . 'image-upload.js', array( 'swpmb-file-upload', 'swpmb-image-advanced' ), SWPMB_VER, true );
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
