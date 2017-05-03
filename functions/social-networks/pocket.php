<?php

/**
 * Functions to add a Pocket share button to the available buttons
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
add_filter( 'swp_button_options', 'swp_pocket_options_function',20 );
function swp_pocket_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['pocket'] = array(
		'type' => 'checkbox',
		'content' => 'Pocket',
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
add_filter( 'swp_add_networks', 'swp_pocket_network' );
function swp_pocket_network( $networks ) {
	$networks[] = 'pocket';
	return $networks;
};

/**
 * #3: Generate the API Share Count Request URL
 *
 * @since  1.0.0
 * @access public
 * @param  string $url The permalink of the page or post for which to fetch share counts
 * @return int 0 Return 0 since this network doesn't support share counts
 */
function swp_pocket_request_link( $url ) {
	return 0;
}

/**
 * #4: Parse the response to get the share count
 *
 * @since  1.0.0
 * @access public
 * @param  string $response The raw response returned from the API request
 * @return int 0 Return zero since this network doesn't support share counts
 */
function swp_format_pocket_response( $response ) {
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
add_filter( 'swp_network_buttons', 'swp_pocket_button_html',10 );
function swp_pocket_button_html( $array ) {

	if ( (isset( $array['options']['newOrderOfIcons']['pocket'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['pocket'] ))  ) :

		// Collect the Title
		$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );
		if ( ! $title ) :
			$title = get_the_title();
		endif;

		++$array['count'];

		$array['resource']['pocket'] = '<div class="nc_tweetContainer swp_pocket" data-id="' . $array['count'] . '" data-network="pocket">';
		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'pocket' , $array['postID'] ) ) );
		$array['resource']['pocket'] .= '<a rel="nofollow" href="https://getpocket.com/save?url=' . $link . '&title=' . $title . '" data-link="https://getpocket.com/save?url=' . $link . '&title=' . $title . '" class="nc_tweet">';
		$array['resource']['pocket'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-pocket"></i><span class="swp_share"> ' . __( 'Pocket','social-warfare' ) . '</span></span></span></span>';
		$array['resource']['pocket'] .= '</a>';
		$array['resource']['pocket'] .= '</div>';

	endif;

	return $array;

};
