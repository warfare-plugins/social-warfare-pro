<?php

if( false == class_exists( 'SWP_Link_Shortener' ) ) {
	return;
}


/**
 * This class manages and processes the shortened URLs for shared links
 * if the user has shortlinks enabled and if they have Bitly selected as
 * their link shortening integration of choice. The link modifications
 * made by this class are added via filter and will be accessed by
 * applying the swp_link_shortening filter.
 *
 * @since 4.0.0 | 17 JUL 2019 | Created
 *
 */
class SWP_Pro_Bitly extends SWP_Link_Shortener {


	/**
     * The Bitly API access token for authenticating API requests.
     *
     * @var string
     */
    public $access_token = '';

    /**
     * The authorization link for Bitly OAuth process.
     *
     * @var string
     */
    public $authorization_link = '';

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
	 * @since  4.4.6.3 | 15 APR 2024 | Changed the hook from 'wp_loaded' to 'wp' to fix early function call issues.
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {

		// Check if we have an access token which means this has been authenticated.
		if( SWP_Utility::get_option( 'bitly_access_token' ) ) {
			$this->access_token = SWP_Utility::get_option( 'bitly_access_token' );
			$this->active = true;
		}

		// The link for the authentication button.
		$this->authorization_link = "https://bitly.com/oauth/authorize?client_id=96c9b292c5503211b68cf4ab53f6e2f4b6d0defb&state=" . admin_url( 'admin-ajax.php') . "&redirect_uri=https://warfareplugins.com/bitly_oauth.php";

		parent::__construct();

		add_action( 'wp', array( $this, 'add_options') , 25 );
	}


	/**
	 * Create a new Bitly short URL
	 *
	 * This is the method used to interface with the Bitly API with regard to creating
	 * new shortened URL's via their service.
	 *
	 * @since  3.0.0 | 04 APR 2018 | Created
	 * @since  4.0.0 | 17 JUL 2019 | Migrated into this standalone Bitly class.
	 * @since  4.0.0 | 26 FEB 2020 | Migrated from Bitly v3 to v4.
	 * @param  string $url          The URL to be shortened
	 * @param  string $post_id      The ID of the post this link belongs to.
	 * @param  string $network      The social network this link will be used on.
	 * @return string               The shortened URL.
	 *
	 */
	public function generate_new_shortlink( $url, $post_id = false, $network = false) {


		/**
		 * First we need to compile the link that we'll use to contact the
		 * Bitly API.
		 *
		 */
		$api_request_url = 'https://api-ssl.bitly.com/v4/shorten';


		/**
		 * The fields added to the array here will be passed through our
		 * CURL::post_json() method. This method will convert the array into
		 * one single JSON encoded field to be sent to the Bitly API.
		 *
		 */
		$api_request_fields = array(
			'long_url' => $url
		);


		/**
		 * These headers will be sent through the CURL::post_json() method.
		 * These headers are needed by Bitly to ensure proper formatting of our
		 * request.
		 *
		 */
		$api_request_header = array(
			'Authorization: Bearer ' . $this->access_token,
			'Content-Type: application/json',
			'Accept: application/json',
		);


		/**
		 * Fetch a response from the Bitly API and then parse it from JSON into
		 * an array that we can use.
		 *
		 */
		$response = SWP_CURL::post_json( $api_request_url, $api_request_fields, $api_request_header );
		$result   = json_decode( $response , true );

		//* The user no longer uses Bitly for link shortening.
		if ( isset( $result['status_txt'] ) && 'INVALID_ARG_ACCESS_TOKEN' == $result['status_txt'] )   {
			SWP_Utility::delete_option( 'bitly_access_token' );
			SWP_Utility::delete_option( 'bitly_access_login' );

			//* Turn link shortening off for the user.
			SWP_Utility::update_option( 'link_shortening_toggle', false );
		}


		/**
		 * If we have a valid link, we'll use that. If not, we'll return false.
		 *
		 */
		if ( isset( $result['link'] ) ) {
			return $result['link'];
		}

		return false;
	}


	/**
	 * A method used to add the options that are specific to this link
	 * shortening API. In this case, it adds the Bitly Authentication button.
	 *
	 * @since  4.0.0 | 24 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function add_options() {

		// Create the authentication button option.
		$authentication_button = new SWP_Option_Button(
			$this->button_properties['text'],
			'authenticate_' . $this->key,
			$this->button_properties['classes'],
			$this->button_properties['link'],
			$this->button_properties['new_tab'],
			$this->button_properties['deactivation_hook']
		);

		// Add the size, priority, and dependency to the option.
		$authentication_button
			->set_size( 'sw-col-300' )
			->set_priority( 30 )
			->set_dependency('link_shortening_service', $this->key);

		// Add our new options to the $SWP_Options_Page object.
		global $SWP_Options_Page;
		$link_shortening = $SWP_Options_Page->tabs->advanced->sections->link_shortening;
		$link_shortening->add_option( $authentication_button );
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
