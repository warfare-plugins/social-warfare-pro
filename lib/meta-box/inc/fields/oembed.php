<?php
defined( 'ABSPATH' ) || die;

/**
 * The oEmbed field which allows users to enter oEmbed URLs.
 */
class SWPMB_OEmbed_Field extends SWPMB_Input_Field {
	/**
	 * Normalize parameters for field.
	 *
	 * @param array $field Field parameters.
	 * @return array
	 */
	public static function normalize( $field ) {
		$field = parent::normalize( $field );

		$field               = wp_parse_args( $field, [
			'not_available_string' => __( 'Embed HTML not available.', 'meta-box' ),
		] );
		$field['attributes'] = wp_parse_args( $field['attributes'], [
			'data-not-available' => $field['not_available_string'],
		] );

		return $field;
	}

	public static function admin_enqueue_scripts() {
		wp_enqueue_style( 'swpmb-oembed', SWPMB_CSS_URL . 'oembed.css', [], SWPMB_VER );
		wp_style_add_data( 'swpmb-oembed', 'path', SWPMB_CSS_DIR . 'oembed.css' );
		wp_enqueue_script( 'swpmb-oembed', SWPMB_JS_URL . 'oembed.js', [ 'jquery', 'underscore', 'swpmb' ], SWPMB_VER, true );
		wp_localize_script( 'swpmb-oembed', 'swpmbOembed', [
			'nonce' => wp_create_nonce( 'oembed_get' ),
		] );
	}

	public static function add_actions() {
		add_action( 'wp_ajax_swpmb_get_embed', [ __CLASS__, 'ajax_get_embed' ] );
	}

	public static function ajax_get_embed() {
		check_ajax_referer( 'oembed_get' );

		$request       = swpmb_request();
		$url           = (string) $request->filter_post( 'url', FILTER_SANITIZE_URL );
		$not_available = (string) $request->post( 'not_available' );
		wp_send_json_success( self::get_embed( $url, $not_available ) );
	}

	/**
	 * Get embed html from url.
	 *
	 * @param string $url           URL.
	 * @param string $not_available Not available string displayed to users.
	 * @return string
	 */
	public static function get_embed( $url, $not_available = '' ) {
		/**
		 * Set arguments for getting embeded HTML.
		 * Without arguments, default width will be taken from global $content_width, which can break UI in the admin.
		 *
		 * @link https://github.com/rilwis/meta-box/issues/801
		 * @see  WP_oEmbed::fetch()
		 * @see  WP_Embed::shortcode()
		 * @see  wp_embed_defaults()
		 */
		$args = [];
		if ( is_admin() ) {
			$args['width'] = 360;
		}

		// Try oembed first.
		$embed = wp_oembed_get( $url, $args );

		// If no oembed provides found, try WordPress auto embed.
		if ( ! $embed ) {
			global $wp_embed;
			$temp                           = $wp_embed->return_false_on_fail;
			$wp_embed->return_false_on_fail = true; // Do not fallback to make a link.
			$embed                          = $wp_embed->shortcode( $args, $url );
			$wp_embed->return_false_on_fail = $temp;
		}

		if ( $not_available ) {
			$not_available = '<div class="swpmb-oembed-not-available">' . wp_kses_post( $not_available ) . '</div>';
		}
		$not_available = apply_filters( 'swpmb_oembed_not_available_string', $not_available, $url );

		return $embed ? $embed : $not_available;
	}

	/**
	 * Get field HTML.
	 *
	 * @param mixed $meta  Meta value.
	 * @param array $field Field parameters.
	 * @return string
	 */
	public static function html( $meta, $field ) {
		return parent::html( $meta, $field ) . sprintf(
			'<span class="spinner"></span>
			<div class="swpmb-embed-media">%s</div>',
			$meta ? self::get_embed( $meta, $field['not_available_string'] ) : ''
		);
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
		$attributes['type'] = 'url';
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
		return self::get_embed( $value, $field['not_available_string'] );
	}
}
