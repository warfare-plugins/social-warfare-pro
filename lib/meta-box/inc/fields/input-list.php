<?php
defined( 'ABSPATH' ) || die;

/**
 * The input list field which displays choices in a list of inputs.
 */
class SWPMB_Input_List_Field extends SWPMB_Choice_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-input-list', SWPMB_CSS_URL . 'input-list.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-input-list', 'path', SWPMB_CSS_DIR . 'input-list.css' );
		wp_enqueue_script( 'swpmb-input-list', SWPMB_JS_URL . 'input-list.js', [], SWPMB_VER, true );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		$options = self::transform_options( $field['options'] );
		$walker  = new SWPMB_Walker_Input_List( $field, $meta );
		$output  = self::get_select_all_html( $field );
		$output .= sprintf(
			'<fieldset class="swpmb-input-list%s%s">',
			$field['collapse'] ? ' swpmb-collapse' : '',
			$field['inline'] ? ' swpmb-inline' : ''
		);
		$output .= $walker->walk( $options, $field['flatten'] ? -1 : 0 );
		$output .= '</fieldset>';

		return $output;
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = $field['multiple'] ? SWPMB_Multiple_Values_Field::normalize( $field ) : $field;
		$field = SWPMB_Input_Field::normalize( $field );
		$field = parent::normalize( $field );
		$field = wp_parse_args( $field, [
			'collapse'        => true,
			'inline'          => null,
			'select_all_none' => false,
		] );

		$field['flatten'] = $field['multiple'] ? $field['flatten'] : true;
		$field['inline']  = ! $field['multiple'] && ! isset( $field['inline'] ) ? true : $field['inline'];

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
		$attributes          = SWPMB_Input_Field::get_attributes( $field, $value );
		$attributes['id']    = false;
		$attributes['type']  = $field['multiple'] ? 'checkbox' : 'radio';
		$attributes['value'] = $value;

		return $attributes;
	}

	/**
	 * Get html for select all|none for multiple checkbox.
	 *
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function get_select_all_html( $field ) {
		if ( $field['multiple'] && $field['select_all_none'] ) {
			return sprintf( '<p class="swpmb-toggle-all-wrapper"><button class="swpmb-input-list-select-all-none button" data-name="%s">%s</button></p>', $field['id'], __( 'Toggle All', 'meta-box' ) );
		}
		return '';
	}
}
