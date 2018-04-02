<?php

/**
 * TODO: This file needs to mirror Social_Warfare.php and the Social_Warfare
 * 		 class that is in core.
 */

class Social_Warfare_Pro extends Social_Warfare {

	public function __construct() {
		$this->load_classes();
		$this->instantiate_classes();

		if ( true === is_admin() ) {
			$this->instantiate_admin_classes();
		}

		$this->registration_update_notification();
		$this->mismatch_notification();
		$this->initiate_plugin();
		$this->update_checker();
		// $this->social_warfare_pro_registration_key(1);
	    // add_action( 'admin_notices', array( $this, 'registration_update_notification' ) );
	    // add_action( 'admin_notices', array( $this, 'mismatch_notification' ) );
	    // add_action( 'plugins_loaded' , array( $this, 'initiate_plugin' , 10 ) );
	    // add_action( 'plugins_loaded' , array( $this, 'update_checker' , 20 ) );
	    add_filter( 'swp_registrations' , array( $this, 'social_warfare_pro_registration_key' , 1 ) );
	}

	public function load_classes() {
		require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Options_Page.php';
	}

	public function instantiate_classes() {

		new SWP_Pro_Options_Page();

		// We can sort the object immediately before looping for HTML output.
		// AKA migrate this command to SWP_Options_Page_HTML
		// $SWP_Options_Page->sort_by_priority();
	}

	public function instantiate_admin_classes() {

	}


	/**
	 * Hook into the registration functions in core and add this plugin to the array
	 *
	 * @param  $array Array An array of registrations to be processed and handled
	 * @return $array Array The modified array of registrations to be processed
	 * @since  2.3.3 | 13 SEP 2017 | Created
	 * @access public
	 *
	 */

	public function social_warfare_pro_registration_key($array) {
	    $array['pro'] = array(
	        'plugin_name' => 'Social Warfare - Pro',
	        'key' => 'pro',
	        'product_id' => SWPP_ITEM_ID,
	        'version' => SWPP_VERSION,
	    );

	    return $array;
	}

	/**
	 * A function to defer the loading of the functions.
	 * We don't want these functions to run until after core has loaded.
	 *
	 * @param  none
	 * @return none
	 *
	 */
	public function initiate_plugin() {
	    if(defined('SWP_VERSION') && SWP_VERSION === SWPP_VERSION):
	        /**
	         * Include the necessary files
	         *
	         */
	        require_once SWPP_PLUGIN_DIR . '/functions/meta-box/meta-box.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/utilities/utility.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/admin/post-options.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/SWP_Pro_Header_Output.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/scripts.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/admin/options-array.php';


	        /**
	         * Include the networks files
	         *
	         */
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/tumblr.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/reddit.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/yummly.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/email.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/whatsapp.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/pocket.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/buffer.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/hackernews.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/flipboard.php';

			new SWP_Pro_Header_Output();

			// Admin only classes
			if ( is_admin() ) {
		        require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Settings_Link.php';

				new SWP_Pro_Settings_link();
			}

	    endif;
	}


	/**
	 * A function to notify users that the versions of Social Warfare and Social Warfare Pro are mismatched.
	 *
	 *
	 * @since  2.2.0
	 * @param  none
	 * @return void
	 *
	 */
	public function mismatch_notification() {
		global $swp_user_options;

		if(defined('SWP_VERSION') && SWP_VERSION !== SWPP_VERSION):
			echo '<div class="update-nag notice is-dismissable"><p>' . __( '<b>Important:</b> You are currently running Social Warfare v'.SWP_VERSION.' and Social Warfare - Pro v'.SWPP_VERSION.'. In order to avoid conflicts, these two version need to match in order to activate all of the plugins features. Please update the appropriate plugin so that both Social Warfare and Social Warfare - Pro are on the same version. For more information about this, <a href="https://warfareplugins.com/support/updating-social-warfare-social-warfare-pro/">please read this</a>. ', 'social-warfare' ) . '</p></div>';
		endif;
	}


	/**
	 * The Plugin Update Checker
	 *
	 *
	 * @since 2.0.0 | Created | Update checker added when the plugin was split into core and pro.
	 * @since 2.3.3 | 13 SEP 2017 | Updated to use EDD's update checker built into core.
	 * @access public
	 *
	 */

	public function update_checker() {

	    // Make sure core is on a version that contains our dependancies
	    if (defined('SWP_VERSION') && version_compare(SWP_VERSION , '2.3.3') >= 0){

	        // Check if the plugin is registered
	        if( is_swp_addon_registered( 'pro' ) ) {

	            // retrieve our license key from the DB
	            $license_key = swp_get_license_key('pro');
	            $website_url = swp_get_site_url();

	            // setup the updater
	            $swed_updater = new SWP_Plugin_Updater( SWP_STORE_URL , SWPP_PLUGIN_FILE , array(
	            	'version'   => SWPP_VERSION,      // current version number
	            	'license'   => $license_key,      // license key
	            	'item_id'   => SWPP_ITEM_ID,      // id of this plugin
	            	'author'    => 'Warfare Plugins', // author of this plugin
	            	'url'       => $website_url,      // URL of this website
	                'beta'      => false              // set to true if you wish customers to receive beta updates
	                )
	            );
	        }
	    }
	}

	/**
	 * Registration Update Notification
	 *
	 *
	 * @since 2.3.0
	 * @access public
	 * @return void
	 *
	 */
	public  function registration_update_notification() {
	    $options = get_option( 'socialWarfareOptions', array() );
	    if( !empty($options['premiumCode']) && empty( $options['pro_license_key'] ) ):
	        echo '<div class="notice-error notice is-dismissable"><p>' . __( '<b>Important:</b> We’ve just made some significant upgrades to your <i>Social Warfare - Pro</i> license. You will need to <a href="https://warfareplugins.com/my-account/">grab your license key</a> and re-register the plugin. Read <a href="https://warfareplugins.com/support/how-to-register-your-license-key/">the full details</a> to find out why this change was necessary.', 'social-warfare' ) . '</p></div>';
	    endif;
	}

}
