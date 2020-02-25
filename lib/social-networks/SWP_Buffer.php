<?php

// Bail out if we don't have access to the class we need to extend.
if( false === class_exists( 'SWP_Social_Network' ) ) {
	return;
}

/**
 * Buffer
 *
 * Class to add a Buffer share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | CREATED
 * @since     2.2.4 | 02 MAY 2017 | Refactored functions & updated docblocking
 * @since     3.0.0 | 05 APR 2018 | Rebuilt into a class-based system.
 *
 */
class SWP_Buffer extends SWP_Social_Network {


	/**
	 * The Magic __construct Method
	 *
	 * This method is used to instantiate the social network object. It does three things.
	 * First it sets the object properties for each network. Then it adds this object to
	 * the globally accessible swp_social_networks array. Finally, it fetches the active
	 * state (does the user have this button turned on?) so that it can be accessed directly
	 * within the object.
	 *
	 * @since  3.0.0 | 06 APR 2018 | Created
	 * @param  none
	 * @return none
	 * @access public
	 *
	 */
	public function __construct() {

		// Update the class properties for this network
		$this->name           = __( 'Buffer','social-warfare' );
		$this->cta            = __( 'Buffer','social-warfare' );
		$this->key            = 'buffer';
		$this->default        = 'false';
        $this->premium        = 'pro';
		$this->base_share_url = 'https://bufferapp.com/add?url=';

		$this->init_social_network();
	}


	/**
	 * Generate the API Share Count Request URL
	 *
	 * @since  1.0.0 | 06 APR 2018 | Created
	 * @access public
	 * @param  string $url The permalink of the page or post for which to fetch share counts
	 * @return string $request_url The complete URL to be used to access share counts via the API
	 *
	 */
	public function get_api_link( $url ) {
		return 'https://api.bufferapp.com/1/links/shares.json?url=' . $url;
	}


	/**
	 * Parse the response to get the share count
	 *
	 * @since  1.0.0 | 06 APR 2018 | Created
	 * @access public
	 * @param  string $response The raw response returned from the API request
	 * @return int $total_activity The number of shares reported from the API
	 *
	 */
	public function parse_api_response( $response ) {
        $response = json_decode( $response, true );
    	return isset( $response['shares'] )?intval( $response['shares'] ) : 0;
	}

    /**
     * Generate the share link
     *
     * This is the link that is being clicked on which will open up the share
     * dialogue. Thie method is only used for networks that use this exact same pattern.
     * For anything that accepts more than just the post permalink as a URL parameter,
     * those networks will have to overwrite this method with their own custom method
     * in their respective child classes.
     *
     * @since  3.0.0 | 08 APR 2018 | Created
     * @param  array $array The array of information passed in from the buttons panel.
     * @return string The generated link
     * @access public
     *
     */
    public function generate_share_link( $post_data ) {

		$title = get_post_meta( $post_data['ID'] , 'swp_og_title' , true );

		if ( !$title ) :
			$title = isset( $post_data['post_title'] ) ? $post_data['post_title'] : get_the_title();
		endif;

		$title = urlencode( $title );

        $share_link = $this->base_share_url . $this->get_shareable_permalink( $post_data ) . '&text=' . $title;

        return $share_link;
    }
}
