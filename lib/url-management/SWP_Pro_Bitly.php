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

		parent::__construct();

		$this->establish_button_properties();

		add_filter( 'swp_link_shortening', array( $this, 'provide_shortlink' ) );
		add_action( 'wp_ajax_nopriv_swp_bitly_oauth', array( $this , 'bitly_oauth_callback' ) );
		add_action( 'wp_ajax_swp_' .$this->deactivation_hook, array( $this, $this->deactivation_hook ) );
	}


	/**
	 * generate_authentication_button_data()
	 *
	 * A method to generate an array of information that can be used to generate
	 * the authentication button for this network on the options page.
	 *
	 * @since  4.0.0 | 18 JUL 2019 | Created
	 * @param  void
	 * @return array The array of button data including the text, color_css,
	 *               target, and link.
	 *
	 */
	public function establish_button_properties() {


		/**
		 * If the integration has already been authenticated, then we'll need to
		 * populate a button that says, "Connected" so it's easy for the user to
		 * see.
		 *
		 */
		if ( SWP_Utility::get_option('bitly_access_token') ) {

			//* Display a confirmation button. On click takes them to bitly settings page.
			$text         = __( 'Connected', 'social-warfare' );
			$text        .= " for:<br/>" . SWP_Utility::get_option( 'bitly_access_login' );
			$classes      = 'button sw-green-button';
			$link         = 'https://app.bitly.com/bitlinks/?actions=accountMain&actions=settings&actions=security';
			$deactivation = $this->deactivation_hook;
			$new_tab      = true;


		/**
		 * If the integration has not been authenticated, then it needs to
		 * contain the text and link that will allow the user to do so.
		 *
		 */
		} else {

			//* Display the button, which takes them to a Bitly auth page.
			$text         = __( 'Authenticate', 'social-warfare' );
			$classes      = 'button sw-navy-button';
			$new_tab      = false;
			$deactivation = '';

			//* The base URL for authorizing SW to work on a user's Bitly account.
			$link = "https://bitly.com/oauth/authorize";

			//* client_id: The SWP application id, assigned by Bitly.
			$link .= "?client_id=96c9b292c5503211b68cf4ab53f6e2f4b6d0defb";

			//* state: Optional state to include in the redirect URI.
			$link .= "&state=" . admin_url( 'admin-ajax.php' );

			//* redirect_uri: The page to which a user is redirected upon successfully authenticating.
			$link .= "&redirect_uri=https://warfareplugins.com/bitly_oauth.php";
		}

		$this->button_properties['text']              = $text;
		$this->button_properties['classes']           = $classes;
		$this->button_properties['new_tab']           = $new_tab;
		$this->button_properties['link']              = $link;
		$this->button_properties['deactivation_hook'] = $deactivation;
	}


	/**
	 * The Bitly Link Shortener Method
	 *
	 * This is the function used to manage shortened links via the Bitly link
	 * shortening service.
	 *
	 * @since  3.0.0 | 04 APR 2018 | Created
	 * @since  3.4.0 | 16 OCT 2018 | Modified order of conditionals, docblocked.
	 * @since  4.0.0 | 17 JUL 2019 | Migrated into this standalone Bitly class.
	 * @param  array $array An array of arguments and information.
	 * @return array $array The modified array.
	 *
	 */
	public function provide_shortlink( $array ) {


		/**
		 * Pull together the information that we'll need to generate bitly links.
		 *
		 */
		global $post;
		$post_id           = $array['post_id'];
		$google_analytics  = SWP_Utility::get_option('google_analytics');
		$access_token      = SWP_Utility::get_option( 'bitly_access_token' );
		$cached_bitly_link = $this->fetch_cached_shortlink( $post_id, $array['network'] );


		/**
		 * Bail if link shortening is turned off.
		 *
		 */
		if( false == SWP_Utility::get_option( 'link_shortening_toggle' ) ) {
			$this->record_exit_status( 'link_shortening_toggle' );
			return $array;
		}


		/**
		 * Bail if Bitly is not the selected Link shortener.
		 *
		 */
		if( $this->key !== SWP_Utility::get_option( 'link_shortening_service' ) ) {
			$this->record_exit_status( 'link_shortening_service' );
			return $array;
		}


		/**
		 * Bail if we don't have a valid Bitly token.
		 *
		 */
		if ( false == $access_token ) {
			$this->record_exit_status( 'access_token' );
			return $array;
		}


		/**
		 * Bitly links can now be turned on or off at the post_type level on the
		 * options page. So if the bitly links are turned off for our current
		 * post type, let's bail and return the unmodified array.
		 * @todo Update this option in the DB to be more generic. Ensure current
		 *       setting migrates into the new one.
		 *
		 */
		$post_type_toggle = SWP_Utility::get_option( 'short_link_toggle_' . $post->post_type );
		if ( false === $post_type_toggle ) {
			$this->record_exit_status( 'short_link_toggle_' . $post->post_type );
			return $array;
		}


		/**
		 * If the chache is fresh and we have a valid bitly link stored in the
		 * database, then let's use our cached link.
		 *
		 * If the cache is fresh and we don't have a valid bitly link, we just
		 * return the unmodified array.
		 *
		 */
		if ( true == $array['fresh_cache'] ) {
			$this->record_exit_status( 'fresh_cache' );
			if( false !== $cached_bitly_link ) {
				$array['url'] = $cached_bitly_link;
			}
			return $array;
		}


		/**
		 * We don't want bitly links generated for the total shares buttons
		 * (since they don't have any links at all), and Pinterest doesn't allow
		 * shortlinks on their network.
		 *
		 */
		if ( $array['network'] == 'total_shares' || $array['network'] == 'pinterest' ) {
			return $array;
		}


		// The post is older than the minimum publication date.
		if ( false == $this->check_publication_date() ) {
			$this->record_exit_status( 'publication_date' );
			return $array;
		}


		/**
		 * If all checks have passed, let's generate a new bitly URL. If an
		 * existing link exists for the link passed to the API, it won't generate
		 * a new one, but will instead return the existing one.
		 *
		 */
		$network       = $array['network'];
		$url           = urldecode( $array['url'] );
		$new_bitly_url = $this->make_bitly_url( $url, $access_token );


		/**
		 * If a link was successfully created, let's store it in the database,
		 * let's store it in the url indice of the array, and then let's wrap up.
		 *
		 */
		if ( $new_bitly_url ) {
			$meta_key = 'bitly_link';

			if ( $google_analytics ) {
				$meta_key .= "_$network";
			}

			delete_post_meta( $post_id, $meta_key );
			update_post_meta( $post_id, $meta_key, $new_bitly_url );
			$array['url'] = $new_bitly_url;
		}

		return $array;
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
	public static function make_bitly_url( $url, $access_token ) {


		/**
		 * First we need to compile the link that we'll use to contact the
		 * Bitly API.
		 *
		 */
		$api_request_url = 'https://api-ssl.bitly.com/v3/shorten';
		$api_request_url .= "?access_token=$access_token";
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
	public function bitly_oauth_callback() {


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

	public function remove_bitly_authorization() {
		SWP_Utility::update_option( 'bitly_access_token', '' );
		SWP_Utility::update_option( 'bitly_access_login', '' );
		echo 'success';
		wp_die();
	}
}
