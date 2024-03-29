<?php
defined( 'ABSPATH' ) || die;

/**
 * The heading field which displays a simple heading text.
 */
class SWPMB_Heading_Field extends SWPMB_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-heading', SWPMB_CSS_URL . 'heading.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-heading', 'path', SWPMB_CSS_DIR . 'heading.css' );
	}

	protected static function begin_html( array $field ) : string {
		$attributes = empty( $field['id'] ) ? '' : " id='{$field['id']}'";
		return sprintf( '<h4%s>%s</h4>', $attributes, $field['name'] );
	}

	protected static function end_html( array $field ) : string {
		return self::input_description( $field );
	}
}
