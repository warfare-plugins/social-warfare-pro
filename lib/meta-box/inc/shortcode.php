<?php
use MetaBox\Support\Arr;

class SWPMB_Shortcode {
	public function init() {
		add_shortcode( 'swpmb_meta', [ $this, 'register_shortcode' ] );
	}

	public function register_shortcode( $atts ) {
		$atts = wp_parse_args( $atts, [
			'id'                => '',
			'object_id'         => null,
			'attribute'         => '',
			'render_shortcodes' => 'true',
		] );
		Arr::change_key( $atts, 'post_id', 'object_id' );
		Arr::change_key( $atts, 'meta_key', 'id' );

		if ( empty( $atts['id'] ) ) {
			return '';
		}

		$field_id  = $atts['id'];
		$object_id = $atts['object_id'];
		
		unset( $atts['id'], $atts['object_id'] );

		$value = $this->get_value( $field_id, $object_id, $atts );
		$value = 'true' === $atts['render_shortcodes'] ? do_shortcode( $value ) : $value;

		$secure = apply_filters( 'swpmb_meta_shortcode_secure', true, $field_id, $atts, $object_id );
		$secure = apply_filters( "swpmb_meta_shortcode_secure_{$field_id}", $secure, $atts, $object_id );

		if ( $secure ) {
			$value = wp_kses_post( $value );
		}

		return $value;
	}

	private function get_value( $field_id, $object_id, $atts ) {
		$attribute = $atts['attribute'];
		if ( ! $attribute ) {
			return swpmb_the_value( $field_id, $atts, $object_id, false );
		}

		$value = swpmb_get_value( $field_id, $atts, $object_id );

		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) ) {
			return $value->$attribute;
		}

		if ( isset( $value[ $attribute ] ) ) {
			return $value[ $attribute ];
		}

		$value = wp_list_pluck( $value, $attribute );
		$value = implode( ',', array_filter( $value ) );

		return $value;
	}
}
