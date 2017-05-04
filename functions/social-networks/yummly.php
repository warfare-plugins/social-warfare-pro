<?php

/**
 * Functions to add a Yummly share button to the available buttons
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
add_filter( 'swp_button_options' , 'swp_yummly_options_function' , 20 );
function swp_yummly_options_function( $options ) {

	// Create the new option in a variable to be inserted
	$options['content']['yummly'] = array(
		'type' => 'checkbox',
		'content' => 'Yummly',
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
add_filter( 'swp_add_networks', 'swp_yummly_network' );
function swp_yummly_network( $networks ) {
	$networks[] = 'yummly';
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
function swp_yummly_request_link( $url ) {
	$request_url = 'http://www.yummly.com/services/yum-count?url=' . $url;
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
function swp_format_yummly_response( $response ) {
	$response = json_decode( $response, true );
	return isset( $response['count'] )?intval( $response['count'] ):0;
}

/**
 * #5: Create the HTML to display the share button
 *
 * @since  1.0.0
 * @access public
 * @param  array $array The array of information used to create and display each social panel of buttons
 * @return array $array The modified array which will now contain the html for this button
 */
add_filter( 'swp_network_buttons', 'swp_yummly_button_html',10 );
function swp_yummly_button_html( $array ) {

	// If we've already generated this button, just use our existing html
	if ( isset( $_GLOBALS['sw']['buttons'][ $array['postID'] ]['yummly'] ) ) :
		$array['resource']['yummly'] = $_GLOBALS['sw']['buttons'][ $array['postID'] ]['yummly'];

	// If not, let's check if Yummly is activated and create the button HTML
	elseif ( (isset( $array['options']['newOrderOfIcons']['yummly'] ) && ! isset( $array['buttons'] )) || (isset( $array['buttons'] ) && isset( $array['buttons']['yummly'] ))  ) :

		if (

			// If a category is set and this post is in that category
			(
				isset( $array['options']['yummly_categories'] )
				&& $array['options']['yummly_categories'] != ''
				&& in_category( $array['options']['yummly_categories'] , $array['postID'] )
			)

			||

			// If a tag is set and this post is in that tag
			(
				isset( $array['options']['yummly_tags'] )
				&& $array['options']['yummly_tags'] != ''
				&& has_tag( $array['options']['yummly_tags'] , $array['postID'] )
			)

			||

			// If no tags or categories have been set
			(
				! isset( $array['options']['yummly_tags'] ) && ! isset( $array['options']['yummly_categories'] ) ||
				 $array['options']['yummly_categories'] == '' && $array['options']['yummly_tags'] == ''
			)

			) :

			$array['totes'] += intval( $array['shares']['yummly'] );
			++$array['count'];

			// Let's create a title
			if ( get_post_meta( $array['postID'] , 'nc_ogTitle' , true ) ) :

				// If the user defined an social media title, let's use it.
				$title = urlencode( urldecode( get_post_meta( $array['postID'] , 'nc_ogTitle' , true ) ) );

			else :

				// Otherwise we'll use the default post title
				$title = urlencode( urldecode( get_the_title() ) );

			endif;

			if ( get_post_meta( $array['postID'],'swp_open_graph_image_url' ) ) :
				$image = urlencode( urldecode( get_post_meta( $array['postID'],'swp_open_graph_image_url',true ) ) );
			else :
				$image = urlencode( urldecode( get_post_meta( $array['postID'],'swp_open_thumbnail_url',true ) ) );
			endif;

			$array['resource']['yummly'] = '<div class="nc_tweetContainer swp_yummly" data-id="' . $array['count'] . '" data-network="yummly">';
			// $link = urlencode(urldecode(swp_process_url( $array['url'] , 'yummly' , $array['postID'] )));
			$link = $array['url'];
			$array['resource']['yummly'] .= '<a rel="nofollow" target="_blank" href="http://www.yummly.com/urb/verify?url=' . $link . '&title=' . $title . '&image=' . $image . '&yumtype=button" data-link="http://www.yummly.com/urb/verify?url=' . $link . '&title=' . $title . '&image=' . $image . '&yumtype=button" class="nc_tweet">';
			if ( $array['options']['totesEach'] && $array['shares']['totes'] >= $array['options']['minTotes'] && $array['shares']['yummly'] > 0 ) :
				$array['resource']['yummly'] .= '<span class="iconFiller">';
				$array['resource']['yummly'] .= '<span class="spaceManWilly">';
				$array['resource']['yummly'] .= '<i class="sw sw-yummly"></i>';
				$array['resource']['yummly'] .= '<span class="swp_share"> ' . __( 'Yum','social-warfare' ) . '</span>';
				$array['resource']['yummly'] .= '</span></span>';
				$array['resource']['yummly'] .= '<span class="swp_count">' . swp_kilomega( $array['shares']['yummly'] ) . '</span>';
			else :
				$array['resource']['yummly'] .= '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><i class="sw sw-yummly"></i><span class="swp_share"> ' . __( 'Yum','social-warfare' ) . '</span></span></span></span>';
			endif;
			$array['resource']['yummly'] .= '</a>';
			$array['resource']['yummly'] .= '</div>';

			// Store these buttons so that we don't have to generate them for each set
			$_GLOBALS['sw']['buttons'][ $array['postID'] ]['yummly'] = $array['resource']['yummly'];

		endif;

	endif;

	return $array;

};
