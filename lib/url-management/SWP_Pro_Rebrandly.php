<?php

/**
 * SWP_Pro_Rebrandly
 *
 * This class will control all of the link shortening functionality for the
 * Rebrandly integration.
 *
 * @since 4.0.0 | 17 JUL 2019 | Created
 *
 */
class SWP_Pro_Rebrandly extends SWP_Link_Shortener {

	use SWP_Debug_Trait;

	public $key                = 'rebrandly';
	public $name               = 'Rebrandly';
	public $deactivation_hook  = '';
	public $authorization_link = '';
	private $shortlinks = array();
	private $domain = 'rebrand.ly';


	/**
	 * The Magic Constructor. Instantiates our Bitly class and sets up any class
	 * properties that weren't set up above.
	 *
	 * @since  4.0.0 | 25 JUL 2019 | Created
	 * @since  4.4.6.3 | 15 APR 2024 | Changed the hook from 'wp_loaded' to 'wp' to fix early function call issues.
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {


		/**
		 * We'll check for an API key and use this to determine whether or not
		 * this link shortening service should be considered active or not.
		 *
		 */
		if( SWP_Utility::get_option('rebrandly_api_key') ) {
			$this->api_key = SWP_Utility::get_option('rebrandly_api_key');
			$this->active  = true;
		}


		/**
		 * This will set up the user's selected link shortening domain while
		 * defaulting it the rebrand.ly short domain.
		 *
		 */
		$domain = SWP_Utility::get_option('rebrandly_domain');
		if( !empty( $domain ) ) {
			$this->domain = $domain;
		}

		parent::__construct();
		add_action( 'wp', array( $this, 'add_options_page_options') , 25 );
	}


	public function generate_new_shortlink( $url, $post_id, $network = false ) {

		if( $this->get_existing_link( $url, $post_id ) ) {
			return $this->get_existing_link( $url, $post_id );
		}

		// Variables we'll pass to the API.
		$domain_data['fullName']  = $this->domain;
		$post_data['domain']      = $domain_data;
		$post_data['destination'] = $url;
		$post_data['title']       = get_the_title( $post_id );

		if( false !== $network && SWP_Utility::get_option('google_analytics') ) {
			$post_data['title'] .= ' (via '. $network .')';
		}

		// Setup and run a cURL request.
		$ch = curl_init("https://api.rebrandly.com/v1/links");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"apikey: {$this->api_key}",
	        "Content-Type: application/json"
		));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
		$result = curl_exec($ch);
		curl_close($ch);

		// Process the response.
		$response = json_decode($result, true);
		$response['shortUrl'] = $this->add_prefix( $response['shortUrl'] );

		if( isset( $response['shortUrl'] ) ) {
			$this->update_link_data( $url, $post_id, $response );
			return $response['shortUrl'];
		}
		return $url;
	}

	public function update_link_data( $url, $post_id, $response ) {
		$rebrandly_data = get_post_meta( $post_id, 'rebrandly_data', true );
		if( false == $rebrandly_data ) {
			$rebrandly_data = array();
		}
		$rebrandly_data[$url] = array(
			'id' => $response['id'],
			'shortlink' => $response['shortUrl']
		);
		delete_post_meta( $post_id, 'rebrandly_data' );
		update_post_meta( $post_id, 'rebrandly_data', $rebrandly_data );
	}

	public function get_existing_link( $url, $post_id ) {
		$rebrandly_data = get_post_meta( $post_id, 'rebrandly_data', true );

		if( !empty( $this->shortlinks[$url] ) ) {
			return $this->shortlinks[$url]['shortlink'];
		}

		if( false == $rebrandly_data ) {
			return false;
		}

		if( empty( $rebrandly_data[$url] ) ) {
			return false;
		}



		// Setup and run a cURL request.
		$post_data['destination'] = $url;
		$ch = curl_init('https://api.rebrandly.com/v1/links/' . $rebrandly_data[$url]['id'] );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"apikey: {$this->api_key}",
			"Content-Type: application/json"
		));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
		$result = curl_exec($ch);
		curl_close($ch);

		// Process the response.
		$response = json_decode($result, true);

		if( !empty( $response['status'] ) && $response['status'] == 'active' ) {
			$this->shortlinks[$url] = $rebrandly_data[$url];
			return $this->add_prefix( $response['shortUrl'] );
		}

		return false;
	}


	private function add_prefix( $shortlink ) {
		if( false === strpos( $shortlink, 'https' )) {
			return 'https://' . $shortlink;
		}
	}

	/**
	 * A method for adding our options to the options page. This adds the API
	 * Key field and the Domain Name field.
	 *
	 * @since  4.0.0 | 24 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function add_options_page_options() {

		// The input for the API key.
		$api_key = new SWP_Option_Text('Rebrandly API Key', 'rebrandly_api_key');
		$api_key->set_size( 'sw-col-300' )
			->set_priority( 30 )
			->set_default( '' )
			->set_dependency('link_shortening_service', $this->key);

		// The input for the domain name.
		$domain = new SWP_Option_Text('Rebrandly Domain', 'rebrandly_domain');
		$domain->set_size( 'sw-col-300' )
			->set_priority( 30 )
			->set_default( '' )
			->set_dependency('link_shortening_service', $this->key);

		// Add our new options to the $SWP_Options_Page object.
		global $SWP_Options_Page;
		$link_shortening = $SWP_Options_Page->tabs->advanced->sections->link_shortening;
		$link_shortening->add_options( array( $api_key, $domain ) );
	}

	public function add_postmeta_options() {

	}
}
