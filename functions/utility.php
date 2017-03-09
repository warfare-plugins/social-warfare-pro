<?php
/**
 * General utility helper functions.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2016, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     2.1.0
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
 * @since 2.2.3 | Created | 09 MAR 2017
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

	// First make sure this feature is activated
	if( isset( $swp_user_options['advanced_pinterest_image'] ) && true === $swp_user_options['advanced_pinterest_image'] ):

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
			if( isset( $swp_user_options['advanced_pinterest_image_location'] ) && 'hidden' === $swp_user_options['advanced_pinterest_image_location'] ) :

				// Compile the image
				$image_html = '<img style="display:none;" src="'.$pinterest_image_url.'" data-pin-url="'.$permalink.'" data-pin-media="'.$pinterest_image_url.'" data-pin-description="'.$pinterest_description.'" />';

				// Add the hidden image to the content string
				$content .= $image_html;

			// Check if the this image is not hidden and do not add display:none to it.
			elseif( isset( $swp_user_options['advanced_pinterest_image_location'] ) && 'hidden' !== $swp_user_options['advanced_pinterest_image_location'] ) :

				// Compile the image
				$image_html = '<img class="no_pin" src="'.$pinterest_image_url.'" alt="'.$pinterest_description.'" data-pin-url="'.$permalink.'" data-pin-media="'.$pinterest_image_url.'" data-pin-description="'.$pinterest_description.'" />';

				// Add the visible image to the top of the content
				if('top' === $swp_user_options['advanced_pinterest_image_location']):
					$content = $image_html . $content;
				endif;

				// Add the visible image to the bottom of the content
				if('bottom' === $swp_user_options['advanced_pinterest_image_location']):
					$content .= $image_html;
				endif;

			endif;

		endif;

	endif;

	return $content;

}
