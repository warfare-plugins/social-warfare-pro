<?php
defined( 'ABSPATH' ) || die;

/**
 * The Switch field.
 */
class SWPMB_Switch_Field extends SWPMB_Input_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-switch', SWPMB_CSS_URL . 'switch.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-switch', 'path', SWPMB_CSS_DIR . 'switch.css' );
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
		$attributes = self::get_attributes( $field, 1 );
		$output     = sprintf(
			'<label class="swpmb-switch-label swpmb-switch-label--' . esc_attr( $field['style'] ) . '">
				<input %s %s>
				<div class="swpmb-switch-status">
					<span class="swpmb-switch-slider"></span>
					<span class="swpmb-switch-on">' . $field['on_label'] . '</span>
					<span class="swpmb-switch-off">' . $field['off_label'] . '</span>
				</div>
				</label>
			',
			self::render_attributes( $attributes ),
			checked( ! empty( $meta ), 1, false )
		);

		return $output;
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
			'style'     => 'rounded',
			'on_label'  => '',
			'off_label' => '',
		] );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field The field parameters.
	 * @param mixed $value The attribute value.
	 *
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes         = parent::get_attributes( $field, $value );
		$attributes['type'] = 'checkbox';

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
		$on  = $field['on_label'] ?: __( 'On', 'meta-box' );
		$off = $field['off_label'] ?: __( 'Off', 'meta-box' );
		return $value ? $on : $off;
	}
}
