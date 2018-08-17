<?php

/**
 * A class of functions used to render shortcodes for the user
 *
 * The SWP_Pro_Shortcodes Class used to add our shorcodes to WordPress
 * registry of registered functions.
 *
 * @package   social-warfare-pro
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     3.2.0
 *
 */
class SWP_Pro_Pinterest_Shortcode {


	/**
	 * Constructs a new SWP_Shortcodes instance
	 *
	 * This function is used to add our shortcodes to WordPress' registry of
	 * shortcodes and to map our functions to each one.
	 *
	 * @since  3.0.0
	 * @param  none
	 * @return none
	 *
	 */
    public function __construct( $attributes = array() ) {
		global $post;
		$this->post_id = $post->ID;
        $this->attributes = $attributes;
	}


	/**
	 * Create the [pinterest_image] shortcode.
	 *
	 * @since  3.2.0 | 25 JUL 2018 | Created
	 * @param  array $atts  Shortcode parameters
	 * @return string       The rendered HTML for a Pinterest image.
	 *
	 */
    public function pinterest_image( $atts ) {
        global $post;

        $whitelist = ['id', 'width', 'height', 'class', 'alignment'];

        //* Instantiate and santiize each of the variables passed as attributes.
        foreach( $whitelist as $var ) {
            $$var = isset( $atts[$var] ) ? sanitize_text_field( trim ( $atts[$var] ) ) : "";
        }

        if ( empty( $id ) ) {
            $id = get_post_meta( $post->ID, 'swp_pinterest_image', true);
            $src = get_post_meta( $post->ID, 'swp_pinterest_image_url', true );
        } else {
            $src = wp_get_attachment_url( $id );
        }

        if ( !is_numeric( $id ) ) {
            return;
        }

        $image = get_post( $id );

        $description = SWP_Pro_Pinterest::get_pin_description( $id );

        if ( !empty( $width ) && !empty( $height ) ):
            $dimensions = ' width="' . $width . '"';
            $dimensions .= ' height="' . $height . '"';
        else :
            $dimensions = "";
        endif;

        if ( empty( $class ) ) :
            $class = "swp-pinterest-image";
        endif;

        $alignment = SWP_Pro_Pinterest::get_alignment( $alignment );

        $html = '<div class="swp-pinterest-image-wrap" ' . $alignment . '>';
            $html .= '<img src="' . $src . '"';
            $html .= $alignment;
            $html .= $dimensions;
            $html .= ' class="' . $class . '"';
            $html .= ' data-pin-description="' . $description . '"';
            $html .= ' />';
        $html .= '</div>';

        return $html;
    }
}
