<?php
/**
 * The beautiful select field which uses select2 library.
 *
 * @package Meta Box
 */

/**
 * Select advanced field which uses select2 library.
 */
class SWPMB_Select_Advanced_Field extends SWPMB_Select_Field {
	/**
	 * Enqueue scripts and styles.
	 */
	public static function admin_enqueue_scripts() {
		parent::admin_enqueue_scripts();
		wp_enqueue_style( 'swpmb-select2', SWPMB_CSS_URL . 'select2/select2.css', array(), '4.0.10' );
		wp_enqueue_style( 'swpmb-select-advanced', SWPMB_CSS_URL . 'select-advanced.css', array(), SWPMB_VER );

		wp_register_script( 'swpmb-select2', SWPMB_JS_URL . 'select2/select2.min.js', array( 'jquery' ), '4.0.10', true );

		// Localize.
		$dependencies = array( 'swpmb-select2', 'swpmb-select' );
		$locale       = str_replace( '_', '-', get_locale() );
		$locale_short = substr( $locale, 0, 2 );
		$locale       = file_exists( SWPMB_DIR . "js/select2/i18n/$locale.js" ) ? $locale : $locale_short;

		if ( file_exists( SWPMB_DIR . "js/select2/i18n/$locale.js" ) ) {
			wp_register_script( 'swpmb-select2-i18n', SWPMB_JS_URL . "select2/i18n/$locale.js", array( 'swpmb-select2' ), '4.0.10', true );
			$dependencies[] = 'swpmb-select2-i18n';
		}

		wp_enqueue_script( 'swpmb-select-advanced', SWPMB_JS_URL . 'select-advanced.js', $dependencies, SWPMB_VER, true );
	}

	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = wp_parse_args(
			$field,
			array(
				'js_options'  => array(),
				'placeholder' => __( 'Select an item', 'meta-box' ),
			)
		);

		$field = parent::normalize( $field );

		$field['js_options'] = wp_parse_args(
			$field['js_options'],
			array(
				'allowClear'  => true,
				'width'       => 'none',
				'placeholder' => $field['placeholder'],
			)
		);

		if ( version_compare( get_bloginfo( 'version' ), '5.3', '>=' ) ) {
			$field['class'] .= ' swpmb-select-advanced-dark';
		}

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
		$attributes = wp_parse_args(
			$attributes,
			array(
				'data-options' => wp_json_encode( $field['js_options'] ),
			)
		);

		return $attributes;
	}
}
