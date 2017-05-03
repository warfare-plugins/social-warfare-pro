<?php

/**
 * Functions to add a Buffer share button to the available buttons
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | CREATED | Unknown
 * @since     2.2.4 | UPDATED | 3 MAY 2017 | Refactored functions & updated docblocking
 */

defined( 'WPINC' ) || die;

/**
 * #1: Add the On/Off Switch and Sortable Option
 *
 * @since  1.0.0
 * @access public
 * @param  array $options The array of available plugin options
 * @return array $options The modified array of available plugin options
 */
add_filter( 'swp_button_options', 'swp_flipboard_options_function',20 );
function swp_flipboard_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['flipboard'] = array(
		'type' => 'checkbox',
		'content' => 'Flipboard',
		'default' => false,
		'premium' => true,
	);

	return $options;
};

/**
 * #2: Add it to the global network array
 *
 * @since  1.0.0
 * @access public
 * @param  array $networks The array of available plugin social networks
 * @return array $networks The modified array of available plugin social networks
 */
add_filter( 'swp_add_networks', 'swp_flipboard_network' );
function swp_flipboard_network( $networks ) {
	$networks[] = 'flipboard';
	return $networks;
};

/**
 * #3: Generate the API Share Count Request URL
 *
 * @since  1.0.0
 * @access public
 * @param  string $url The permalink of the page or post for which to fetch share counts
 * @return string $request_url The complete URL to be used to access share counts via the API
 */
function swp_flipboard_request_link( $url ) {
	return 0;
}

/**
 * #4: Parse the response to get the share count
 *
 * @since  1.0.0
 * @access public
 * @param  string $response The raw response returned from the API request
 * @return int $total_activity The number of shares reported from the API
 */
function swp_format_flipboard_response( $response ) {
	return 0;
}

/**
 * #5: Create the HTML to display the share button
 *
 * @since  1.0.0
 * @access public
 * @param  array $array The array of information used to create and display each social panel of buttons
 * @return array $array The modified array which will now contain the html for this button
 */
add_filter( 'swp_network_buttons', 'swp_flipboard_button_html',10 );
function swp_flipboard_button_html( $array ) {

	if ( (isset( $array['options']['newOrderOfIcons']['flipboard'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['flipboard'] ))  ) :

		// Collect the Title
		$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );
		if ( ! $title ) :
			$title = get_the_title();
		endif;

		// Collect the Description
		$description = get_post_meta( $array['postID'] , 'nc_ogDescription' , true );
		if ( ! $description ) :
			$description = swp_get_excerpt_by_id( $array['postID'] );
		endif;
		++$array['count'];

		$array['resource']['flipboard'] = '<div class="nc_tweetContainer swp_flipboard" data-id="' . $array['count'] . '" data-network="flipboard">';
		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'flipboard' , $array['postID'] ) ) );
		$array['resource']['flipboard'] .= '<a rel="nofollow" href="https://share.flipboard.com/bookmarklet/popout?v=2&title=Tools%20-%20Flipboard&url=' . $link . '" data-link="https://share.flipboard.com/bookmarklet/popout?v=2&title=Tools%20-%20Flipboard&url=' . $link . '" class="nc_tweet flipboard">';
		$array['resource']['flipboard'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-flipboard"></i><span class="swp_share"> ' . __( 'Flip','social-warfare' ) . '</span></span></span></span>';
		$array['resource']['flipboard'] .= '</a>';
		$array['resource']['flipboard'] .= '</div>';

	endif;

	return $array;

};
