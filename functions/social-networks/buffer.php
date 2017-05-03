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
add_filter( 'swp_button_options', 'swp_buffer_options_function',20 );
function swp_buffer_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['buffer'] = array(
		'type' => 'checkbox',
		'content' => 'Buffer',
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
add_filter( 'swp_add_networks', 'swp_buffer_network' );
function swp_buffer_network( $networks ) {
	$networks[] = 'buffer';
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
function swp_buffer_request_link( $url ) {
	$request_url = 'https://api.bufferapp.com/1/links/shares.json?url=' . $url;
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
function swp_format_buffer_response( $response ) {
	$response = json_decode( $response, true );
	return isset( $response['shares'] )?intval( $response['shares'] ):0;
}

/**
 * #5: Create the HTML to display the share button
 *
 * @since  1.0.0
 * @access public
 * @param  array $array The array of information used to create and display each social panel of buttons
 * @return array $array The modified array which will now contain the html for this button
 */
add_filter( 'swp_network_buttons', 'swp_buffer_button_html',10 );
function swp_buffer_button_html( $array ) {

	// If we've already generated this button, just use our existing html
	if ( isset( $_GLOBALS['sw']['buttons'][ $array['postID'] ]['buffer'] ) ) :

		$array['resource']['buffer'] = $_GLOBALS['sw']['buttons'][ $array['postID'] ]['buffer'];

	// If not, let's check if Buffer is activated and create the button HTML
	elseif ( (isset( $array['options']['newOrderOfIcons']['buffer'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['buffer'] ))  ) :

		// Collect the Title
		$title = get_post_meta( $array['postID'] , 'nc_ogTitle' , true );

		if ( ! $title ) :

			$title = get_the_title();

		endif;

		$array['totes'] += intval( $array['shares']['buffer'] );

		++$array['count'];

		$array['resource']['buffer'] = '<div class="nc_tweetContainer swp_buffer" data-id="' . $array['count'] . '" data-network="buffer">';

		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'buffer' , $array['postID'] ) ) );

		$array['resource']['buffer'] .= '<a rel="nofollow" target="_blank" href="http://bufferapp.com/add?url=' . $link . '&text=' . urlencode( html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) ) . '" data-link="http://bufferapp.com/add?url=' . $link . '&text=' . urlencode( html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) ) . '" class="nc_tweet buffer_link">';

		if ( $array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && $array['shares']['buffer'] > 0 ) :

			$array['resource']['buffer'] .= '<span class="iconFiller">';

			$array['resource']['buffer'] .= '<span class="spaceManWilly">';

			$array['resource']['buffer'] .= '<i class="sw sw-buffer"></i>';

			$array['resource']['buffer'] .= '<span class="swp_share"> ' . __( 'Buffer','social-warfare' ) . '</span>';

			$array['resource']['buffer'] .= '</span></span>';

			$array['resource']['buffer'] .= '<span class="swp_count">' . swp_kilomega( $array['shares']['buffer'] ) . '</span>';

		else :

			$array['resource']['buffer'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-buffer"></i><span class="swp_share"> ' . __( 'Buffer','social-warfare' ) . '</span></span></span></span>';

		endif;

		$array['resource']['buffer'] .= '</a>';

		$array['resource']['buffer'] .= '</div>';

		// Store these buttons so that we don't have to generate them for each set
		$_GLOBALS['sw']['buttons'][ $array['postID'] ]['buffer'] = $array['resource']['buffer'];

	endif;

	return $array;

};
