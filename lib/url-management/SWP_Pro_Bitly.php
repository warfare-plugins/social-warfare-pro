<?php

/**
 * SWP_Pro_Bitly
 *
 * This class will manage and process the shortened URLs for shared links
 * if the user has shortlinks enabled and if they have Bitly selected as
 * their link shortening integration of choice. The link modifications
 * made by this class are added via filter and will be accessed by
 * applying the swp_link_shortening filter.
 *
 * @since 4.0.0 | 17 JUL 2019 | Created
 *
 */
if( false === class_exists( 'SWP_Link_Shortener' ) ) {
	return;
}

class SWP_Pro_Bitly extends SWP_Link_Shortener {


	/**
	 * Class properties that will be used to display and process this particular
	 * link shortener.
	 *
	 */
	public $key               = 'bitly';
	public $name              = 'Bitly';
	public $deactivation_hook = 'remove_bitly_authorization';
	public $activation_hook   = 'bitly_oauth';


	/**
	 * The Magic Constructor Method
	 *
	 * This method will simply queue up the Bitly processing methods to run on
	 * the appropriate hooks as needed.
	 *
	 * @since  4.0.0 | 17 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {

		// Check if we have an access token which means this has been authenticated.
		if( SWP_Utility::get_option( 'bitly_access_token' ) ) {
			$this->access_token;
			$this->active = true;
		}

		// The link for the authentication button.
		$this->authorization_link = "https://bitly.com/oauth/authorize?client_id=96c9b292c5503211b68cf4ab53f6e2f4b6d0defb&state=" . admin_url( 'admin-ajax.php') . "&redirect_uri=https://warfareplugins.com/bitly_oauth.php";

		parent::__construct();
	}


	/**
	 * Create a new Bitly short URL
	 *
	 * This is the method used to interface with the Bitly API with regard to creating
	 * new shortened URL's via their service.
	 *
	 * @since  3.0.0 | 04 APR 2018 | Created
	 * @since  4.0.0 | 17 JUL 2019 | Migrated into this standalone Bitly class.
	 * @param  string $url          The URL to be shortened
	 * @param  string $network      The social network on which this URL is being shared.
	 * @param  string $access_token The user's Bitly access token.
	 * @return string               The shortened URL.
	 *
	 */
	public function generate_new_shortlink( $url ) {


		/**
		 * First we need to compile the link that we'll use to contact the
		 * Bitly API.
		 *
		 */
		$api_request_url = 'https://api-ssl.bitly.com/v3/shorten';
		$api_request_url .= "?access_token=$this->access_token";
		$api_request_url .= "&longUrl=" . urlencode( $url );
		$api_request_url .= "&format=json";


		/**
		 * Fetch a response from the Bitly API and then parse it from JSON into
		 * an array that we can use.
		 *
		 */
		$response = SWP_CURL::file_get_contents_curl( $api_request_url );
		$result   = json_decode( $response , true );

		//* The user no longer uses Bitly for link shortening.
		if ( isset( $result['status_txt'] ) && 'INVALID_ARG_ACCESS_TOKEN' == $result['status_txt'] )   {
			SWP_Utility::delete_option( 'bitly_access_token' );
			SWP_Utility::delete_option( 'bitly_access_login' );

			//* Turn Bitly link shortening off for the user.
			SWP_Utility::update_option( 'bitly_authentication', false );
		}


		/**
		 * If we have a valid link, we'll use that. If not, we'll return false.
		 *
		 */
		if ( isset( $result['data']['url'] ) ) {
			return $result['data']['url'];
		}

		return false;
	}


	/**
	 * The Bitly OAuth Callback Function
	 *
	 * When authenticating Bitly to the plugin, Bitly uses a back-and-forth handshake
	 * system. This function will intercept the ping from Bitly's server, process the
	 * information and provide a response to Bitly.
	 *
	 * @since  3.0.0 | 04 APR 2018 | Created
	 * @since  4.0.0 | 17 JUL 2019 | Migrated into this standalone Bitly class.
	 * @param  void
	 * @return void A response is echoed to the screen for Bitly to read.
	 * @access public
	 *
	 */
	public function bitly_oauth() {


		/**
		 * If no access token or bitly login username is provided, then we're
		 * just going to store them in the database as empty strings.
		 *
		 */
		$access_token = '';
		$login        = '';


		/**
		 * If the callback contained a valid access token and login, then we'll
		 * use those and store them in the options field of the database.
		 *
		 */
		if( isset( $_GET['access_token'] ) && isset( $_GET['login'] ) ) {
			$access_token = $_GET['access_token'];
			$login        = $_GET['login'];
		}


		/**
		 * Update our options field in the database with our new values.
		 *
		 */
		SWP_Utility::update_option( 'bitly_access_token', $access_token );
		SWP_Utility::update_option( 'bitly_access_login', $login);


		/**
		 * We have to echo out the link to the settings page so that the file
		 * on our server that handles the handshake can initiate a nice clean
		 * redirect and put the user back in their own admin dashboard on our
		 * options page.
		 *
		 */
		echo admin_url( 'admin.php?page=social-warfare' );
	}


	/**
	 * An Admin-Ajax callback function for removing this link shorteners
	 * authorization token and login credentials.
	 *
	 * @since  4.0.0 | 23 JUL 2019 | Created
	 * @parm   void
	 * @return void
	 *
	 */
	public function remove_bitly_authorization() {
		SWP_Utility::update_option( 'bitly_access_token', '' );
		SWP_Utility::update_option( 'bitly_access_login', '' );
		echo 'success';
		wp_die();
	}
}
