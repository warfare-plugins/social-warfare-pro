<?php

/**
 * Functions to add a Email share button to the available buttons
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
add_filter( 'swp_button_options', 'swp_email_options_function',20 );
function swp_email_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['email'] = array(
		'type' => 'checkbox',
		'content' => 'Email',
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
add_filter( 'swp_add_networks', 'swp_email_network' );
function swp_email_network( $networks ) {
	$networks[] = 'email';
	return $networks;
};

/**
 * #3: Generate the API Share Count Request URL
 *
 * @since  1.0.0
 * @access public
 * @param  string $url The permalink of the page or post for which to fetch share counts
 * @return int 0 There are no share counts for email. Zero tells the share count API function to ignore this network
 */
function swp_email_request_link( $url ) {
	return 0;
}

/**
 * #4: Parse the response to get the share count
 *
 * @since  1.0.0
 * @access public
 * @param  string $response The raw response returned from the API request
 * @return int 0 There are no share counts for email shares
 */
function swp_format_email_response( $response ) {
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
add_filter( 'swp_network_buttons', 'swp_email_button_html',10 );
function swp_email_button_html( $array ) {

	if ( (isset( $array['options']['newOrderOfIcons']['email'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['email'] ))  ) :

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

		$array['resource']['email'] = '<div class="nc_tweetContainer swp_email" data-id="' . $array['count'] . '" data-network="email">';
		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'email' , $array['postID'] ) ) );
		$array['resource']['email'] .= '<a rel="nofollow" href="mailto:?subject=' . str_replace( '&amp;','%26',rawurlencode( html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) ) ) . '&body=' . str_replace( '&amp;','%26',rawurlencode( html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ) ) ) . rawurlencode( __( 'Read Here: ','social-warfare' ) ) . $link . '" class="nc_tweet">';
		$array['resource']['email'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-email"></i><span class="swp_share"> ' . __( 'Email','social-warfare' ) . '</span></span></span></span>';
		$array['resource']['email'] .= '</a>';
		$array['resource']['email'] .= '</div>';

		endif;

	return $array;

};
