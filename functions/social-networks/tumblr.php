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
add_filter( 'swp_button_options' , 'swp_tumblr_options_function' , 20 );
function swp_tumblr_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['tumblr'] = array(
		'type' => 'checkbox',
		'content' => 'Tumblr',
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
add_filter( 'swp_add_networks', 'swp_tumblr_network' );
function swp_tumblr_network( $networks ) {
	$networks[] = 'tumblr';
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
function swp_tumblr_request_link( $url ) {
	$request_url = 'https://api.tumblr.com/v2/share/stats?url=' . $url;
	return $request_url;
}

/**
 * #4: Parse the response to get the share count
 *
 * @since  1.0.0
 * @access public
 * @param  string $response The raw response returned from the API request
 * @return int $total_activity The number of shares reported from the API
 */
function swp_format_tumblr_response( $response ) {
	$response = json_decode( $response, true );
	return isset( $response['response']['note_count'] )?intval( $response['response']['note_count'] ):0;
}

/**
 * #5: Create the HTML to display the share button
 *
 * @since  1.0.0
 * @access public
 * @param  array $array The array of information used to create and display each social panel of buttons
 * @return array $array The modified array which will now contain the html for this button
 */
add_filter( 'swp_network_buttons', 'swp_tumblr_button_html',10 );
function swp_tumblr_button_html( $array ) {

	// If we've already generated this button, just use our existing html
	if ( isset( $_GLOBALS['sw']['buttons'][ $array['postID'] ]['tumblr'] ) ) :
		$array['resource']['tumblr'] = $_GLOBALS['sw']['buttons'][ $array['postID'] ]['tumblr'];

	// If not, let's check if Tumblr is activated and create the button HTML
	elseif ( (isset( $array['options']['newOrderOfIcons']['tumblr'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['tumblr'] ))  ) :

		$array['totes'] += intval( $array['shares']['tumblr'] );
		++$array['count'];

		// Collect the Title
		$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );
		if ( ! $title ) :
			$title = get_the_title();
		endif;

		// Collect the Description
		$description = get_post_meta( $array['postID'] , 'nc_ogDescription' , true );

		$array['resource']['tumblr'] = '<div class="nc_tweetContainer swp_tumblr" data-id="' . $array['count'] . '" data-network="tumblr">';
		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'tumblr' , $array['postID'] ) ) );
		$array['resource']['tumblr'] .= '<a rel="nofollow" target="_blank" href="http://www.tumblr.com/share/link?url=' . $link . '&name=' . urlencode( $title ) . ($description ? '&description=' : '') . urlencode( $description ) . '" data-link="http://www.tumblr.com/share/link?url=' . $link . '&name=' . urlencode( $title ) . ($description ? '&description=' : '') . urlencode( $description ) . '" class="nc_tweet">';
		if ( $array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && $array['shares']['tumblr'] > 0 ) :
			$array['resource']['tumblr'] .= '<span class="iconFiller">';
			$array['resource']['tumblr'] .= '<span class="spaceManWilly">';
			$array['resource']['tumblr'] .= '<i class="sw sw-tumblr"></i>';
			$array['resource']['tumblr'] .= '<span class="swp_share"> ' . __( 'Share','social-warfare' ) . '</span>';
			$array['resource']['tumblr'] .= '</span></span>';
			$array['resource']['tumblr'] .= '<span class="swp_count">' . swp_kilomega( $array['shares']['tumblr'] ) . '</span>';
		else :
			$array['resource']['tumblr'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-tumblr"></i><span class="swp_share"> ' . __( 'Share','social-warfare' ) . '</span></span></span></span>';
		endif;
		$array['resource']['tumblr'] .= '</a>';
		$array['resource']['tumblr'] .= '</div>';

		// Store these buttons so that we don't have to generate them for each set
		$_GLOBALS['sw']['buttons'][ $array['postID'] ]['tumblr'] = $array['resource']['tumblr'];

	endif;

	return $array;

};
