<?php

/**
 * VKontakte
 *
 * Class to add a VKontakte share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2020, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     4.0.0 | 21 FEB 2020 | CREATED
 *
 */
class SWP_VKontakte extends SWP_Social_Network {


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
			$this->name           = __( 'VKontakte','social-warfare' );
			$this->cta            = __( 'Share','social-warfare' );
			$this->key            = 'vk';
			$this->default        = 'false';

			// This is the link that is clicked on to share an article to their network.
			$this->base_share_url = 'http://vk.com/share.php?url=';

			$this->init_social_network();
		}


		/**
		 * Generate the API Share Count Request URL
		 *
		 * @since  4.0.0 | 21 FEB 2020 | Created
		 * @access public
		 * @param  string $url The permalink of the page or post for which to fetch share counts
		 * @return string $request_url The complete URL to be used to access share counts via the API
		 *
		 */
		public function get_api_link( $url ) {
			return 'https://vk.com/share.php?act=count&url='. $url;
		}


		/**
		 * Parse the response to get the share count
		 *
		 * @since  4.0.0 | 21 FEB 2020 | Created
		 * @param  string  $response The raw response returned from the API request
		 * @return integer The number of shares reported from the API
		 *
		 */
		public function parse_api_response( $response ) {

			// Fetch the numerals from the API response.
			$match_count = preg_match_all("/[0-9]\d*/", $response, $counts_array );

			// If we had a valid REGEX match...
			if( $match_count > 0 ) {
				$share_count = 0;
				foreach( $counts_array[0] as $current_count ) {
					$share_count = $share_count + $current_count;
				}

				return $share_count;
			}

			// Return 0 if no valid counts were able to be extracted.
			return 0;
		}
}
