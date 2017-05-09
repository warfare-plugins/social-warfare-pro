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
 * Get the current site's URL.
 *
 * @since  2.1.0
 * @return string The current site's URL.
 */
function swp_get_site_url() {

	$domain = get_option( 'siteurl' );

	if ( is_multisite() ) {
		$domain = network_site_url();
	}

	return $domain;
}

/**
 * A function to queue up the function that will add the Pinterest image for browser extensions
 *
 * @since 2.2.4 | Created | 09 MAR 2017
 * @param none
 * @return none
 */
function swp_pre_insert_pinterest_image() {

	// Only hook into the_content filter if we is_singular() is true or they don't use excerpts
    if( true === is_singular() ):
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

	// Fetch the user's settings
	global $swp_user_options, $post;
	$post_id = $post->ID;
	$swp_advanced_pin_image = get_post_meta( $post_id , 'swp_advanced_pinterest_image' , true );
	$swp_advanced_pin_image_location = get_post_meta( $post_id , 'swp_advanced_pinterest_image_location' , true );

	/**
	 * A conditional to see if this feature should be turned on or off on a given post
	 *
	 */
	// First check to see if it's turned on on the post
	if( '' != $swp_advanced_pin_image && 'on' === $swp_advanced_pin_image ):
		$status = true;

	// Second check to see if it's turned off on the post
	elseif( '' != $swp_advanced_pin_image && 'off' === $swp_advanced_pin_image ):
		$status = false;

	// Third check if it's turned on or off in the options
	elseif( isset( $swp_user_options['advanced_pinterest_image'] ) && true === $swp_user_options['advanced_pinterest_image'] ):
		$status = $swp_user_options['advanced_pinterest_image'];

	// Fourth, if nothing matches, turn it off
	else:
		$status = false;
	endif;

	/**
	 * A conditional to see where the image should be displayed
	 *
	 */
	// First check to see if it's set at the post level
	if( '' != $swp_advanced_pin_image_location && 'default' !== $swp_advanced_pin_image_location ):
		$location = $swp_advanced_pin_image_location;

	// Second, see if it's set in the options
	elseif( isset( $swp_user_options['advanced_pinterest_image_location'] ) ):
		$location = $swp_user_options['advanced_pinterest_image_location'];

	// Third, if nothing is set, set it to hidden.
	else:
		$location = 'hidden';
	endif;

	// First make sure this feature is activated
	if( true === $status ):

		// Collect the user's custom defined Pinterest specific Image
		$pinterest_image_url = get_post_meta( $post_id, 'swp_pinterest_image_url' , true );

		// Exit if the user doesn't have a custom defined image
		if( false !== $pinterest_image_url && !empty($pinterest_image_url) ):

			// Fetch the user's custom Pinterest description
			$pinterest_description = get_post_meta( $post_id , 'nc_pinterestDescription' , true );

			// Collect the user's Pinterest username
			if ( !empty( $swp_user_options['pinterestID'] ) ) :
				$pinterest_username = ' via @' . str_replace( '@' , '' , $swp_user_options['pinterestID'] );
			else :
				$pinterest_username = '';
			endif;

			// If there is no custom description, use the post Title
			if( false === $pinterest_description || empty($pinterest_image_url) ):
				urlencode( html_entity_decode( get_the_title() . $pinterest_username, ENT_COMPAT, 'UTF-8' ) );
			endif;

			// Fetch the Permalink
			$permalink = get_the_permalink();

			// Check if this image is hidden and add display:none to it.
			if( 'hidden' === $location ) :

				// Compile the image
				$image_html = '<img class="no_pin swp_hidden_pin_image" src="'.$pinterest_image_url.'" data-pin-url="'.$permalink.'" data-pin-media="'.$pinterest_image_url.'" data-pin-description="'.$pinterest_description.'" />';

				// Add the hidden image to the content string
				$content .= $image_html;

			// Check if the this image is not hidden and do not add display:none to it.
			elseif( 'hidden' !== $location ) :

				// Compile the image
				$image_html = '<img src="'.$pinterest_image_url.'" alt="'.$pinterest_description.'" data-pin-url="'.$permalink.'" data-pin-media="'.$pinterest_image_url.'" data-pin-description="'.$pinterest_description.'" />';

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

	endif;

	return $content;

}
