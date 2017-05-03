<?php

/**
 * Functions to add a Hacker News share button to the available buttons
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
add_filter( 'swp_button_options', 'swp_hacker_news_options_function',20 );
function swp_hacker_news_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['hacker_news'] = array(
		'type' => 'checkbox',
		'content' => 'Hacker News',
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
add_filter( 'swp_add_networks', 'swp_hacker_news_network' );
function swp_hacker_news_network( $networks ) {
	$networks[] = 'hacker_news';
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
function swp_hacker_news_request_link( $url ) {
	$request_url = 'https://hn.algolia.com/api/v1/search?tags=story&restrictSearchableAttributes=url&query=' . $url;
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
function swp_format_hacker_news_response( $response ) {
	$response = json_decode( $response, true );
	return $response['nbHits'];
}

/**
 * #5: Create the HTML to display the share button
 *
 * @since  1.0.0
 * @access public
 * @param  array $array The array of information used to create and display each social panel of buttons
 * @return array $array The modified array which will now contain the html for this button
 */
add_filter( 'swp_network_buttons', 'swp_hacker_news_button_html',10 );
function swp_hacker_news_button_html( $array ) {

	if ( (isset( $array['options']['newOrderOfIcons']['hacker_news'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['hacker_news'] ))  ) :

		// Collect the Title
		$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );
		if ( ! $title ) :
			$title = get_the_title();
		endif;
		++$array['count'];

		$array['resource']['hacker_news'] = '<div class="nc_tweetContainer swp_hacker_news" data-id="' . $array['count'] . '" data-network="hacker_news">';
		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'email' , $array['postID'] ) ) );
		$array['resource']['hacker_news'] .= '<a rel="nofollow" target="_blank" href="http://news.ycombinator.com/submitlink?u=' . $link . '&t=' . urlencode( $title ) . '" data-link="http://news.ycombinator.com/submitlink?u=' . $link . '&t=' . urlencode( $title ) . '" class="nc_tweet">';
		if ( $array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && $array['shares']['hacker_news'] > 0 ) :
			$array['resource']['hacker_news'] .= '<span class="iconFiller">';
			$array['resource']['hacker_news'] .= '<span class="spaceManWilly">';
			$array['resource']['hacker_news'] .= '<i class="sw sw-hacker_news"></i>';
			$array['resource']['hacker_news'] .= '<span class="swp_share"> ' . __( 'Vote','social-warfare' ) . '</span>';
			$array['resource']['hacker_news'] .= '</span></span>';
			$array['resource']['hacker_news'] .= '<span class="swp_count">' . swp_kilomega( $array['shares']['hacker_news'] ) . '</span>';
		else :
			$array['resource']['hacker_news'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-hacker_news"></i><span class="swp_share"> ' . __( 'Vote','social-warfare' ) . '</span></span></span></span>';
		endif;
		$array['resource']['hacker_news'] .= '</a>';
		$array['resource']['hacker_news'] .= '</div>';

	endif;

	return $array;

};
