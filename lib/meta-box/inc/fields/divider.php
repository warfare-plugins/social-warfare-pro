<?php
defined( 'ABSPATH' ) || die;

/**
 * The divider field which displays a simple horizontal line.
 */
class SWPMB_Divider_Field extends SWPMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-divider', SWPMB_CSS_URL . 'divider.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-divider', 'path', SWPMB_CSS_DIR . 'divider.css' );
	}

	protected static function begin_html( array $field ) : string {
		$attributes = empty( $field['id'] ) ? '' : " id='{$field['id']}'";
		return "<hr$attributes>";
	}

	public static function end_html( array $field ) : string {
		return '';
	}
}
