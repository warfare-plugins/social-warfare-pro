<?php
if ( class_exists( 'SWP_Social_Network' ) ) :

/**
 * Tumblr
 *
 * Class to add a Tumblr share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | CREATED
 * @since     2.2.4 | 02 MAY 2017 | Refactored functions & updated docblocking
 * @since     3.0.0 | 05 APR 2018 | Rebuilt into a class-based system.
 *
 */
class SWP_Tumblr extends SWP_Social_Network {

	/**
     * The API key for Tumblr.
     *
     * @var string
     */
    public $api_key = 'PiEjtbt2aJJFIUChMbXTpCnYAmfKQMowd1Z25SWCCS9SfDsa76';

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
		$this->name           = __( 'Tumblr','social-warfare' );
		$this->cta            = __( 'Share','social-warfare' );
		$this->key            = 'tumblr';
		$this->default        = false;
        $this->premium        = 'pro';
		$this->base_share_url = 'https://www.tumblr.com/widgets/share/tool?';
        $this->api_key        = 'PiEjtbt2aJJFIUChMbXTpCnYAmfKQMowd1Z25SWCCS9SfDsa76';

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
		return 'https://api.tumblr.com/v2/share/stats?url=' . $url;
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
    	return isset( $response['response']['note_count'] ) ? intval( $response['response']['note_count'] ) : 0;
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
        $parameters = 'posttype=link';
        $parameters .= '&canonicalUrl=' . $this->get_shareable_permalink( $post_data);
        if ( isset($post_data['post_title'] ) ) :
            $parameters .= '&title=' . urlencode( $post_data['post_title'] );
        endif;
        $share_link = $this->base_share_url . $parameters;
        return $share_link;
    }
}
endif;
