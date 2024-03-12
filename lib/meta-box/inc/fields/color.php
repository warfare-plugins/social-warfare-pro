<?php
defined( 'ABSPATH' ) || die;

/**
 * The color field which uses WordPress color picker to select a color.
 */
class SWPMB_Color_Field extends SWPMB_Input_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-color', SWPMB_CSS_URL . 'color.css', [ 'wp-color-picker' ], SWPMB_VER );
		wp_style_add_data( 'swpmb-color', 'path', SWPMB_CSS_DIR . 'color.css' );

		$dependencies = [ 'wp-color-picker' ];
		$args         = func_get_args();
		$field        = reset( $args );
		if ( ! empty( $field['alpha_channel'] ) ) {
			wp_enqueue_script( 'wp-color-picker-alpha', SWPMB_JS_URL . 'wp-color-picker-alpha/wp-color-picker-alpha.min.js', [ 'wp-color-picker' ], SWPMB_VER, true );
			$dependencies = [ 'wp-color-picker-alpha' ];
		}
		wp_enqueue_script( 'swpmb-color', SWPMB_JS_URL . 'color.js', $dependencies, SWPMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 *
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'alpha_channel' => false,
			'js_options'    => [],
		] );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'defaultColor' => false,
			'hide'         => true,
			'palettes'     => true,
		] );

		$field = parent::normalize( $field );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes         = parent::get_attributes( $field, $value );
		$attributes         = wp_parse_args( $attributes, [
			'data-options' => wp_json_encode( $field['js_options'] ),
		] );
		$attributes['type'] = 'text';

		if ( $field['alpha_channel'] ) {
			$attributes['data-alpha-enabled']    = 'true';
			$attributes['data-alpha-color-type'] = 'hex';
		}

		return $attributes;
	}

	/**
	 * Format a single value for the helper functions. Sub-fields should overwrite this method if necessary.
	 *
	 * @param array    $field   Field parameters.
	 * @param string   $value   The value.
	 * @param array    $args    Additional arguments. Rarely used. See specific fields for details.
	 * @param int|null $post_id Post ID. null for current post. Optional.
	 *
	 * @return string
	 */
	public static function format_single_value( $field, $value, $args, $post_id ) {
		return sprintf( "<span style='display:inline-block;width:20px;height:20px;border-radius:50%%;background:%s;'></span>", $value );
	}
}
