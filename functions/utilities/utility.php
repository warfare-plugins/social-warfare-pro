<?php
/**
 * General utility helper functions.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     2.1.0 | Created | Unknown
 * @since     2.2.4 | Updated | 09 MAR 2017 | Created the advanced Pinterest image functions
 * @since     2.2.4 | Updated | 05 MAY 2017 | Made the advanced pinterest settings work with the post options
 */

/**
 * A function to queue up the function that will add the Pinterest image for browser extensions
 *
 * @since 2.2.4 | Created | 09 MAR 2017
 * @param none
 * @return none
 */
function swp_pre_insert_pinterest_image() {

	if( function_exists('is_amp_endpoint') ) {
		$amp = is_amp_endpoint();
	} else {
		$amp = false;
	}

	// Only hook into the_content filter if we is_singular() is true or they don't use excerpts
    if( true === is_singular() && false == is_feed() && false == $amp ):
        add_filter( 'the_content','swp_insert_pinterest_image', 10 );
    endif;

}

// Hook into the template_redirect so that is_singular() conditionals will be ready
add_action('template_redirect', 'swp_pre_insert_pinterest_image');

/**
 * A function to insert the Pinterest image for browser extensions
 *
 * @since  2.2.4 | 09 MAR 2017 | Created
 * @since  3.3.0 | 20 AUG 2018 | Refactored the method.
 * @access public
 *
 * @param  string $content The post content to filter
 * @return string $content The filtered content
 *
 */
function swp_insert_pinterest_image( $content ) {
    //* Call $swp_user_options for legacy code.
	global $post, $swp_user_options;
	$post_id = $post->ID;
	$pin_browser_extension = get_post_meta( $post_id , 'swp_pin_browser_extension' , true );
	$pin_browser_location = get_post_meta( $post_id , 'swp_pin_browser_extension_location' , true );

    // Bail early if not using a pinterest image.
    if ( 'off' == $pin_browser_extension ) :
        return $content;
    endif;

    if ( 'on' === $pin_browser_extension && !SWP_Utility::get_option( 'pin_browser_extension' ) ) :
         return $content;
    endif;

    $pinterest_image_url = get_post_meta( $post_id, 'swp_pinterest_image_url' , true );

    if ( empty( $pinterest_image_url ) || false === $pinterest_image_url ) :
        return $content;
    endif;

    // This post is using some kind of Pinterest Image, so prepare the data to compile an image.

    if ( class_exists( 'SWP_Utility' ) ) :
        $location = $pin_browser_location == 'defualt' ? SWP_Utility::get_option( 'pinterest_image_location' ) : $pin_browser_location;
    else :
        //* Legacy code.

        if( 'default' !== $pin_browser_location ):
            $location = $pin_browser_location;
        elseif( isset( $swp_user_options['pinterest_image_location'] ) ):
            $location = $swp_user_options['pinterest_image_location'];
        else:
            $location = 'hidden';
        endif;

    endif;

    //* Set up the Pinterest username, if it exists.
    if ( class_exists( 'SWP_Utility' ) ) :

        $id = SWP_Utility::get_option( 'pinterest_id' );
        $pinterest_username = $id ? ' via @' . str_replace( '@' , '' , $id ) : '';

    else:
        //* Legacy code.

        if ( !empty( $swp_user_options['pinterest_id'] ) ) :
            $pinterest_username = ' via @' . str_replace( '@' , '' , $swp_user_options['pinterest_id'] );
        else :
            $pinterest_username = '';
        endif;

    endif;


    $pinterest_description = get_post_meta( $post_id , 'swp_pinterest_description' , true );

	// If there is no custom description, use the post Title
	if( false === $pinterest_description || empty( $pinterest_image_url ) ):
		$pinterest_description = urlencode( html_entity_decode( get_the_title() . $pinterest_username, ENT_COMPAT, 'UTF-8' ) );
	endif;


	// If the image is hidden, give it the swp_hidden_pin_image class.
	if( 'hidden' === $location ) :

		$image_html = '<img class="no_pin swp_hidden_pin_image" src="' . $pinterest_image_url .
                      '" data-pin-url="' . get_the_permalink() .
                      '" data-pin-media="' . $pinterest_image_url .
                      '" alt="' . $pinterest_description .
                      '" data-pin-description="' . $pinterest_description .
                      '" />';

		$content .= $image_html;

    // Put the image in a container otherwise
	else :

        $extra_class = 'swp-pinterest-image-' . $location;

        $image_html = '<div class="swp-pinterest-image-wrapper ' . $extra_class . '">
                          <img class="swp-featured-pinterest-image" src="' . $pinterest_image_url .
                        '" alt="' . $pinterest_description .
                        '" data-pin-url="' . get_the_permalink() .
                        '" data-pin-media="' . $pinterest_image_url .
                        '" data-pin-description="' . $pinterest_description .
                        '" />
                      </div>';

		if('top' === $location):
			$content = $image_html . $content;
		endif;

		if('bottom' === $location):
			$content .= $image_html;
		endif;

	endif;

	return $content;

}
