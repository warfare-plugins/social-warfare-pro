<?php
defined( 'ABSPATH' ) || die;

/**
 * The beautiful select field using select2 library.
 */
class SWPMB_Select_Advanced_Field extends SWPMB_Select_Field {
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'swpmb-select2', SWPMB_CSS_URL . 'select2/select2.css', [], '4.0.10' );
		wp_style_add_data( 'swpmb-select2', 'path', SWPMB_CSS_DIR . 'select2/select2.css' );

		wp_enqueue_style( 'swpmb-select-advanced', SWPMB_CSS_URL . 'select-advanced.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-select-advanced', 'path', SWPMB_CSS_DIR . 'select-advanced.css' );

		wp_register_script( 'swpmb-select2', SWPMB_JS_URL . 'select2/select2.min.js', [ 'jquery' ], '4.0.10', true );

		// Localize.
		$dependencies = [ 'swpmb-select2', 'swpmb-select', 'underscore' ];
		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		$locale       = file_exists( SWPMB_DIR . "js/select2/i18n/$locale.js" ) ? $locale : $locale_short;

		if ( file_exists( SWPMB_DIR . "js/select2/i18n/$locale.js" ) ) {
			wp_register_script( 'swpmb-select2-i18n', SWPMB_JS_URL . "select2/i18n/$locale.js", [ 'swpmb-select2' ], '4.0.10', true );
			$dependencies[] = 'swpmb-select2-i18n';
		}

		wp_enqueue_script( 'swpmb-select-advanced', SWPMB_JS_URL . 'select-advanced.js', $dependencies, SWPMB_VER, true );
		SWPMB_Helpers_Field::localize_script_once( 'swpmb-select-advanced', 'swpmbSelect2', [
			'isAdmin' => is_admin(),
		]);
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args( $field, [
			'js_options'  => [],
			'placeholder' => __( 'Select an item', 'meta-box' ),
		] );

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args( $field['js_options'], [
			'allowClear'        => true,
			'dropdownAutoWidth' => true,
			'placeholder'       => $field['placeholder'],
			'width'             => 'style',
		] );

		return $field;
	}

	/**
	 * Get the attributes for a field.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $value Meta value.
	 * @return array
	 */
	public static function get_attributes( $field, $value = null ) {
		$attributes = parent::get_attributes( $field, $value );
		$attributes = wp_parse_args( $attributes, [
			'data-options' => wp_json_encode( $field['js_options'] ),
		] );

		return $attributes;
	}
}
