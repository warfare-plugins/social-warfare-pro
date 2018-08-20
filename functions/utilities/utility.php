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
 * @since  2.2.4 | Created | 09 MAR 2017
 * @access public
 * @param  string $content The post content to filter
 * @return string $content The filtered content
 */
function swp_insert_pinterest_image( $content ) {
	global $post;
	$post_id = $post->ID;
	$pin_image = get_post_meta( $post_id , 'swp_pin_browser_extension' , true );
	$pin_image_location = get_post_meta( $post_id , 'swp_pin_browser_extension_location' , true );

    if ( 'off' == $pin_image && '' != $pin_image ) :
        return $content;
    endif;

    if (
        'on' === $pin_image &&
        '' != $pin_image &&
        !SWP_Utility::get_option( 'pin_browser_extension' )
        ) {
         return $content;
    }


	/**
	 * A conditional to see where the image should be displayed
	 *
	 */
	// First check to see if it's set at the post level
	if( '' != $pin_image_location && 'default' !== $pin_image_location ):
		$location = $pin_image_location;

	// Second, see if it's set in the options
	elseif( isset( $swp_user_options['pinterest_image_location'] ) ):
		$location = $swp_user_options['pinterest_image_location'];

	// Third, if nothing is set, set it to hidden.
	else:
		$location = 'hidden';
	endif;


	// Collect the user's custom defined Pinterest specific Image
	$pinterest_image_url = get_post_meta( $post_id, 'swp_pinterest_image_url' , true );

	// Exit if the user doesn't have a custom defined image
	if( false !== $pinterest_image_url && !empty($pinterest_image_url) ):

		// Fetch the user's custom Pinterest description
		$pinterest_description = get_post_meta( $post_id , 'swp_pinterest_description' , true );

		// Collect the user's Pinterest username
		if ( !empty( $swp_user_options['pinterest_id'] ) ) :
			$pinterest_username = ' via @' . str_replace( '@' , '' , $swp_user_options['pinterest_id'] );
		else :
			$pinterest_username = '';
		endif;

		// If there is no custom description, use the post Title
		if( false === $pinterest_description || empty($pinterest_image_url) ):
			$pinterest_description = urlencode( html_entity_decode( get_the_title() . $pinterest_username, ENT_COMPAT, 'UTF-8' ) );
		endif;

		// Fetch the Permalink
		$permalink = get_the_permalink();

		// Check if this image is hidden and add display:none to it.
		if( 'hidden' === $location ) :

			// Compile the image
			$image_html = '<img class="no_pin swp_hidden_pin_image" src="'.$pinterest_image_url.'" data-pin-url="'.$permalink.'" data-pin-media="'.$pinterest_image_url.'" alt="'.$pinterest_description.'" data-pin-description="'.$pinterest_description.'" />';

			// Add the hidden image to the content string
			$content .= $image_html;

		// Check if the this image is not hidden and do not add display:none to it.
		elseif( 'hidden' !== $location ) :

			// Compile the image
			$image_html = '<div class="swp-pinterest-image-wrapper"><img class="swp-featured-pinterest-image" src="'.$pinterest_image_url.'" alt="'.$pinterest_description.'" data-pin-url="'.$permalink.'" data-pin-media="'.$pinterest_image_url.'" data-pin-description="'.$pinterest_description.'" /></div>';

			// Add the visible image to the top of the content
			if('top' === $location):
				$content = $image_html . $content;
			endif;

			// Add the visible image to the bottom of the content
			if('bottom' === $location):
				$content .= $image_html;
			endif;

		endif;

	endif;


	return $content;

}
