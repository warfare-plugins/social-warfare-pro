<?php

class SWP_Pro_Link_Manager {

	/**
	 * The magic __construct method.
	 *
	 * This method instantiates the SWP_Pro_Link_Manager object. It's primary function
	 * is to add the various methods to their approprate hooks for use later on
	 * when the core plugin calls for them.
	 *
	 * @since  4.0.0 | 17 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {


		/**
		 * This class will manage and add the URL parameters for shared links
		 * using the Google Analytics UTM format. The link modifications made by
		 * this class are added via filter and will be accessed by applying the
		 * swp_analytics filter.
		 *
		 */
		new SWP_Pro_UTM_Tracking();


		/**
		 * This class will manage and process the shortened URLs for shared links
		 * if the user has shortlinks enabled and if they have Bitly selected as
		 * their link shortening integration of choice. The link modifications
		 * made by this class are added via filter and will be accessed by
		 * applying the swp_link_shortening filter.
		 *
		 */
		new SWP_Pro_Bitly();


		/**
		 * This will enqueue our options to be added on the wp_loaded hook. We
 	 	 * defer the call to this method as such to ensure that the $SWP_Options_Page
 	 	 * global object has already been created and is available for us to
 	 	 * manipulate.
		 *
		 */
		add_action( 'wp_loaded', array( $this, 'add_settings_page_options') , 20 );

	}


	/**
	 * add_settings_page_options()
	 *
	 * This method will add the options to the settings page that will allow the
	 * WordPress admin to configure the link shortening options as they see fit.
	 *
	 * We'll add the following options for the user:
	 * 1. A link shortening on/off toggle.
	 * 2. A link shortening service select dropdown (Bitly, Rebrandly, Etc.)
	 * 3. A minimum post publication date input box.
	 * 4. Options to turn shortening on/off for each post type.
	 *
	 * @since  4.0.0 | 18 JUL 2019 | Created
	 * @param  void
	 * @return void
	 * 
	 */
	public function add_settings_page_options() {

		$link_shortening = new SWP_Options_Page_Section( __( 'Link Shortening', 'social-warfare'), 'bitly' );
		$link_shortening->set_description( __( 'If you\'d like to have all of your links automatically shortened, turn this on.', 'social-warfare') )
			->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-bitly-link-shortening/' )
			->set_priority( 20 );

			//* linkShortening => bitly_authentication
			$link_shortening_toggle = new SWP_Option_Toggle( __('Link Shortening', 'social-warfare' ), 'link_shortening_toggle' );
			$link_shortening_toggle->set_size( 'sw-col-300' )
				->set_priority( 10 )
				->set_default( false )
				->set_premium( 'pro' );



				$services = array();
				$authentications = array();
				$services = apply_filters('swp_register_link_shorteners', $services );
				foreach( $services as $service ) {
					$available_services[$service['key']] = $service['name'];

					$authentications[$service['key']] = new SWP_Option_Button( 'Authenticate', 'bitly', 'button sw-navy-button swp-revoke-button', 'http://google.com' );
					$authentications[$service['key']]->set_size( 'sw-col-300' )
						->set_priority( 20 )
						->set_dependency('link_shortening_service', $service['key']);
				}



				//* advanced_pinterest_image_location => pinterest_image_location
				$link_shortening_service = new SWP_Option_Select( __( 'Link Shortening Service', 'social-warfare' ), 'link_shortening_service' );
				$link_shortening_service->set_choices( $available_services )
					->set_default( 'hidden ')
					->set_size( 'sw-col-300' )
					->set_dependency( 'link_shortening_toggle', true )
					->set_premium( 'pro' )
					->set_priority( 15 );

			$link_shortening_start_date = new SWP_Option_Text( __( 'Minimum Publish Date', 'social-warfare' ), 'link_shortening_start_date' );
			$link_shortening_start_date->set_default( date('Y-m-d') )
				->set_priority( 30 )
				->set_size( 'sw-col-300' )
				->set_dependency( 'link_shortening_toggle', true )
				->set_premium( 'pro');

			$link_shortening->add_options( array( $link_shortening_toggle, $link_shortening_service, $link_shortening_start_date ) );
			$link_shortening->add_options( $authentications );

			global $SWP_Options_Page;
			$advanced_tab = $SWP_Options_Page->tabs->advanced;
			$advanced_tab->add_sections( array( $link_shortening ) );

	}
}
