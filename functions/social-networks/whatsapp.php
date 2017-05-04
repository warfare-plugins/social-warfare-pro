<?php

/**
 * Functions to add a WhatsApp share button to the available buttons
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | CREATED | Unknown
 * @since     2.2.4 | UPDATED | 4 MAY 2017 | Refactored functions & updated docblocking
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
add_filter( 'swp_button_options' , 'swp_whatsapp_options_function' , 20 );
function swp_whatsapp_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['whatsapp'] = array(
		'type' => 'checkbox',
		'content' => 'WhatsApp',
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
add_filter( 'swp_add_networks', 'swp_whatsapp_network' );
function swp_whatsapp_network( $networks ) {
	$networks[] = 'whatsapp';
	return $networks;
};

/**
 * #3: Generate the API Share Count Request URL
 *
 * @since  1.0.0
 * @access public
 * @param  string $url The permalink of the page or post for which to fetch share counts
 * @return int 0 WhatsApp doesn't offer share counts
 */
function swp_whatsapp_request_link( $url ) {
	return 0;
}

/**
 * #4: Parse the response to get the share count
 *
 * @since  1.0.0
 * @access public
 * @param  string $response The raw response returned from the API request
 * @return int 0 WhatsApp doesn't offer share counts
 */
function swp_format_whatsapp_response( $response ) {
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
add_filter( 'swp_network_buttons' , 'swp_whatsapp_button_html' , 10 );
function swp_whatsapp_button_html( $array ) {

	// If we've already generated this button, just use our existing html
	if ( isset( $_GLOBALS['sw']['buttons'][ $array['postID'] ]['whatsapp'] ) ) :
		$array['resource']['whatsapp'] = $_GLOBALS['sw']['buttons'][ $array['postID'] ]['whatsapp'];

	// If not, let's check if WhatsApp is activated and create the button HTML
	elseif ( (isset( $array['options']['newOrderOfIcons']['whatsapp'] ) && ! isset( $array['buttons'] ))
	|| (isset( $array['buttons'] ) && isset( $array['buttons']['whatsapp'] ))  ) :

		$array['totes'] += intval( $array['shares']['whatsapp'] );
		++$array['count'];

		$array['resource']['whatsapp'] = '<div class="nc_tweetContainer swp_whatsapp" data-id="' . $array['count'] . '" data-network="whatsapp">';
		$link = urlencode( urldecode( swp_process_url( $array['url'] , 'whatsapp' , $array['postID'] ) ) );
		$array['resource']['whatsapp'] .= '<a rel="nofollow" target="_blank" onclick="window.location = this.href;" href="whatsapp://send?text=' . $link . '" data-link="whatsapp://send?text=' . $link . '" class="nc_tweet noPop" data-action="share/whatsapp/share">';
		if ( $array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && $array['shares']['whatsapp'] > 0 ) :
			$array['resource']['whatsapp'] .= '<span class="iconFiller">';
			$array['resource']['whatsapp'] .= '<span class="spaceManWilly">';
			$array['resource']['whatsapp'] .= '<i class="sw sw-whatsapp"></i>';
			$array['resource']['whatsapp'] .= '<span class="swp_share"> ' . __( 'WhatsApp','social-warfare' ) . '</span>';
			$array['resource']['whatsapp'] .= '</span></span>';
			$array['resource']['whatsapp'] .= '<span class="swp_count">' . swp_kilomega( $array['shares']['whatsapp'] ) . '</span>';
		else :
			$array['resource']['whatsapp'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-whatsapp"></i><span class="swp_share"> ' . __( 'WhatsApp','social-warfare' ) . '</span></span></span></span>';
		endif;
		$array['resource']['whatsapp'] .= '</a>';
		$array['resource']['whatsapp'] .= '</div>';

		// Store these buttons so that we don't have to generate them for each set
		$_GLOBALS['sw']['buttons'][ $array['postID'] ]['whatsapp'] = $array['resource']['whatsapp'];

	endif;

	return $array;

};
