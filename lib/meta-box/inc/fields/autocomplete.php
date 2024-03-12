<?php
defined( 'ABSPATH' ) || die;

/**
 * The autocomplete field.
 */
class SWPMB_Autocomplete_Field extends SWPMB_Multiple_Values_Field {
	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-autocomplete', SWPMB_CSS_URL . 'autocomplete.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-autocomplete', 'path', SWPMB_CSS_DIR . 'autocomplete.css' );
		wp_enqueue_script( 'swpmb-autocomplete', SWPMB_JS_URL . 'autocomplete.js', [ 'jquery-ui-autocomplete' ], SWPMB_VER, true );

		SWPMB_Helpers_Field::localize_script_once( 'swpmb-autocomplete', 'SWPMB_Autocomplete', [
			'delete' => __( 'Delete', 'meta-box' ),
		] );
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		if ( ! is_array( $meta ) ) {
			$meta = [ $meta ];
		}

		// Filter out empty values in case the array started with empty or 0 values
		$meta = array_filter( $meta, function ( $index ) use ( $meta ) {
			return $meta[ $index ] !== '';
		}, ARRAY_FILTER_USE_KEY );

		$field   = apply_filters( 'swpmb_autocomplete_field', $field, $meta );
		$options = $field['options'];

		if ( is_array( $field['options'] ) ) {
			$options = [];
			foreach ( $field['options'] as $value => $label ) {
				$options[] = [
					'value' => (string) $value,
					'label' => $label,
				];
			}
			$options = wp_json_encode( $options );
		}

		// Input field that triggers autocomplete.
		// This field doesn't store field values, so it doesn't have "name" attribute.
		// The value(s) of the field is store in hidden input(s). See below.
		$html = sprintf(
			'<input type="text" class="swpmb-autocomplete-search">
			<input type="hidden" name="%s" class="swpmb-autocomplete" data-options="%s" disabled>',
			esc_attr( $field['field_name'] ),
			esc_attr( $options )
		);

		$html .= '<div class="swpmb-autocomplete-results">';

		// Each value is displayed with label and 'Delete' option.
		// The hidden input has to have ".swpmb-*" class to make clone work.
		$tpl = '
			<div class="swpmb-autocomplete-result">
				<div class="label">%s</div>
				<div class="actions">%s</div>
				<input type="hidden" class="swpmb-autocomplete-value" name="%s" value="%s">
			</div>
		';

		if ( is_array( $field['options'] ) ) {
			foreach ( $field['options'] as $value => $label ) {
				if ( ! in_array( $value, $meta ) ) {
					continue;
				}
				$html .= sprintf(
					$tpl,
					esc_html( $label ),
					esc_html__( 'Delete', 'meta-box' ),
					esc_attr( $field['field_name'] ),
					esc_attr( $value )
				);
			}
		} else {
			$meta = array_filter( $meta );
			foreach ( $meta as $value ) {
				$label = apply_filters( 'swpmb_autocomplete_result_label', $value, $field );
				$html .= sprintf(
					$tpl,
					esc_html( $label ),
					esc_html__( 'Delete', 'meta-box' ),
					esc_attr( $field['field_name'] ),
					esc_attr( $value )
				);
			}
		}

		$html .= '</div>'; // .swpmb-autocomplete-results.

		return $html;
	}
}
