<?php

/**
 * Functions to add a Reddit share button to the available buttons
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
add_filter( 'swp_button_options' , 'swp_reddit_options_function' , 20 );
function swp_reddit_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['reddit'] = array(
		'type' => 'checkbox',
		'content' => 'Reddit',
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
add_filter( 'swp_add_networks', 'swp_reddit_network' );
function swp_reddit_network( $networks ) {
	$networks[] = 'reddit';
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
function swp_reddit_request_link( $url ) {
	$request_url = 'https://www.reddit.com/api/info.json?url=' . $url;
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
function swp_format_reddit_response( $response ) {

	// Parse the JSON response into an associative array
	$response = json_decode( $response, true );
	$score = 0;

	// Check to ensure that there was a response
	if ( isset( $response ) && isset( $response['data'] ) && isset( $response['data']['children'] ) ) :

		// Loop through each post on reddit adding the score to our total
		foreach ( $response['data']['children'] as $child ) :
			$score += (int) $child['data']['score'];
			endforeach;
		endif;

	// Return the score to Social Warfare for caching and presentation
	return $score;
}

/**
 * #5: Create the HTML to display the share button
 *
 * @since  1.0.0
 * @access public
 * @param  array $array The array of information used to create and display each social panel of buttons
 * @return array $array The modified array which will now contain the html for this button
 */
add_filter( 'swp_network_buttons', 'swp_reddit_button_html',10 );
function swp_reddit_button_html( $array ) {

	// If we've already generated this button, just use our existing html
	if ( isset( $_GLOBALS['sw']['buttons'][ $array['postID'] ]['reddit'] ) ) :
		$array['resource']['reddit'] = $_GLOBALS['sw']['buttons'][ $array['postID'] ]['reddit'];

	// If not, let's check if Facebook is activated and create the button HTML
	elseif ( (isset( $array['options']['newOrderOfIcons']['reddit'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['reddit'] ))  ) :

		if ( isset( $array['shares']['reddit'] ) ) :
			$array['totes'] += intval( $array['shares']['reddit'] );
		endif;
		++$array['count'];

		// Collect the Title
		$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );
		if ( ! $title ) :
			$title = get_the_title();
		endif;

		$array['resource']['reddit'] = '<div class="nc_tweetContainer swp_reddit" data-id="' . $array['count'] . '" data-network="reddit">';
		$link = $array['url'];
		$array['resource']['reddit'] .= '<a rel="nofollow" target="_blank" href="https://www.reddit.com/submit?url=' . $link . '&title=' . urlencode( $title ) . '" data-link="https://www.reddit.com/submit?url=' . $link . '&title=' . urlencode( $title ) . '" class="nc_tweet">';
		if ( $array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && isset( $array['shares']['reddit'] ) && $array['shares']['reddit'] > 0 ) :
			$array['resource']['reddit'] .= '<span class="iconFiller">';
			$array['resource']['reddit'] .= '<span class="spaceManWilly">';
			$array['resource']['reddit'] .= '<i class="sw sw-reddit"></i>';
			$array['resource']['reddit'] .= '<span class="swp_share"> ' . __( 'Reddit','social-warfare' ) . '</span>';
			$array['resource']['reddit'] .= '</span></span>';
			$array['resource']['reddit'] .= '<span class="swp_count">' . swp_kilomega( $array['shares']['reddit'] ) . '</span>';
		else :
			$array['resource']['reddit'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-reddit"></i><span class="swp_share"> ' . __( 'Reddit','social-warfare' ) . '</span></span></span></span>';
		endif;
		$array['resource']['reddit'] .= '</a>';
		$array['resource']['reddit'] .= '</div>';

		// Store these buttons so that we don't have to generate them for each set
		$_GLOBALS['sw']['buttons'][ $array['postID'] ]['reddit'] = $array['resource']['reddit'];

	endif;

	return $array;

};
