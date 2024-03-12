<?php
defined( 'ABSPATH' ) || die;

/**
 * The select field.
 */
class SWPMB_Select_Field extends SWPMB_Choice_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-select', SWPMB_CSS_URL . 'select.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-select', 'path', SWPMB_CSS_DIR . 'select.css' );
		wp_enqueue_script( 'swpmb-select', SWPMB_JS_URL . 'select.js', [ 'jquery' ], SWPMB_VER, true );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$options                     = self::transform_options( $field['options'] );
		$attributes                  = self::call( 'get_attributes', $field, $meta );
		$attributes['data-selected'] = $meta;
		$walker                      = new SWPMB_Walker_Select( $field, $meta );
		$output                      = sprintf(
			'<select %s>',
			self::render_attributes( $attributes )
		);
		if ( ! $field['multiple'] && $field['placeholder'] ) {
			$output .= '<option value="">' . esc_html( $field['placeholder'] ) . '</option>';
		}
		$output .= $walker->walk( $options, $field['flatten'] ? -1 : 0 );
		$output .= '</select>';
		$output .= self::get_select_all_html( $field );
		return $output;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );
		$field = $field['multiple'] ? SWPMB_Multiple_Values_Field::normalize( $field ) : $field;
		$field = wp_parse_args( $field, [
			'select_all_none' => false,
		] );

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
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, [
			'multiple' => $field['multiple'],
		] );

		return $attributes;
	}

	/**
	 * Get html for select all|none for multiple select.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function get_select_all_html( $field ) {
		if ( $field['multiple'] && $field['select_all_none'] ) {
			return '<div class="swpmb-select-all-none">' . __( 'Select', 'meta-box' ) . ': <a data-type="all" href="#">' . __( 'All', 'meta-box' ) . '</a> | <a data-type="none" href="#">' . __( 'None', 'meta-box' ) . '</a></div>';
		}
		return '';
	}
}
