<?php
defined( 'ABSPATH' ) || die;

/**
 * The HTML5 range field.
 */
class SWPMB_Range_Field extends SWPMB_Number_Field {
	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return sprintf(
			'<div class="swpmb-range-inner">
				%s
				<span class="swpmb-range-output">%s</span>
			</div>',
			parent::html( $meta, $field ),
			$meta
		);
	}

	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-range', SWPMB_CSS_URL . 'range.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-range', 'path', SWPMB_CSS_DIR . 'range.css' );
		wp_enqueue_script( 'swpmb-range', SWPMB_JS_URL . 'range.js', [], SWPMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'max' => 10,
		] );
		$field = parent::normalize( $field );
		return $field;
	}

	/**
	 * Ensure number in range.
	 *
	 * @param mixed $new     The submitted meta value.
	 * @param mixed $old     The existing meta value.
	 * @param int   $post_id The post ID.
	 * @param array $field   The field parameters.
	 *
	 * @return int
	 */
	public static function value( $new, $old, $post_id, $field ) {
		$new = (float) $new;
		$min = (float) $field['min'];
		$max = (float) $field['max'];

		if ( $new < $min ) {
			return $min;
		}
		if ( $new > $max ) {
			return $max;
		}
		return $new;
	}
}
