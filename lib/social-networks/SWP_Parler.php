<?php

/**
 * Parler
 *
 * Class to add a Parler share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2020, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     4.3.0 | 11 DEC 2020 | Created
 *
 */
class SWP_Parler extends SWP_Social_Network {


	/**
	 * The Magic __construct Method
	 *
	 * This method is used to instantiate the social network object. It does three things.
	 * First it sets the object properties for each network. Then it adds this object to
	 * the globally accessible swp_social_networks array. Finally, it fetches the active
	 * state (does the user have this button turned on?) so that it can be accessed directly
	 * within the object.
	 *
	 * @since  4.3.0 | 11 DEC 2020 | Created
	 * @param  none
	 * @return none
	 * @access public
	 *
	 */
	public function __construct() {

		// Update the class properties for this network
		$this->name    = __( 'Parler','social-warfare' );
		$this->cta     = __( 'Share','social-warfare' );
		$this->key     = 'parler';
		$this->default = 'true';

		$this->init_social_network();
	}


	/**
	 * Generate the API Share Count Request URL
	 *
	 * If a zero is returned, the cURL processes will no that this network does
	 * not have an active API endpoint and will not make a remote call.
	 *
	 * This method is called by the SWP_Post_Cache class when rebuilding the
	 * cached share count data.
	 *
	 * @since  4.3.0 | 11 DEC 2020 | Created
	 * @param  string $url The permalink of the page or post for which to fetch share counts
	 * @return string $request_url The complete URL to be used to access share counts via the API
	 *
	 */
	public function get_api_link( $url ) {
		return 0;
	}


	/**
	 * Parse the response to get the share count
	 *
	 * This method is called by the SWP_Post_Cache class when rebuilding the
	 * cached share count data.
	 *
	 * @since  4.3.0 | 11 DEC 2020 | Created
	 * @access public
	 * @param  string $response The raw response returned from the API request
	 * @return int $total_activity The number of shares reported from the API
	 *
	 */
	public function parse_api_response( $response ) {
		return 0;
	}


	/**
	 * Generate the share link
	 *
	 * This is the link that is being clicked on which will open up the share
	 * dialogue.
	 *
	 * @since  3.0.0 | 07 APR 2018 | Created
	 * @since  3.4.0 | 17 NOV 2018 | Stripped down into smaller, subordinate methods.
	 * @param  array $post_data The array of information passed in from the buttons panel.
	 * @return string The generated link
	 *
	 */
	public function generate_share_link( $post_data ) {

		$post_title  = get_the_title( $post_data['ID'] );
		$permalink   = $this->get_shareable_permalink( $post_data );
		$intent_link = 'https://parler.com/new-post?message='.$post_title.'&url='. $permalink;

		return $intent_link;
	}
}
