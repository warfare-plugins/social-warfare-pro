<?php

class Social_Warfare_Pro extends SWP_Addon {

	public function __construct( ) {
        parent::__construct();
        $this->name = 'Social Warfare - Pro';
        $this->key = 'pro';
        $this->product_id = 63157;
        $this->version = SWPP_VERSION;
        $this->filepath = SWPP_PLUGIN_FILE;
		$this->load_classes();

        add_action( 'wp_loaded', array( $this, 'instantiate_addon') );

		$this->registration_update_notification();
		$this->initiate_plugin();

        add_filter( 'swp_registrations', array( $this, 'add_self' ) );


	}

	public function load_classes() {
            /**
             * The Social Network Classes
             *
             * This family of classes provides the framework and the model needed for creating
             * a unique object for each social network. It also provides for maximum extensibility
             * to allow addons even easier access than ever before to create and add more social
             * networks to the plugin.
             *
             */
            $social_networks = array(
                'Buffer',
                'Reddit',
                'Flipboard',
                'Email',
                'Hackernews',
                'Pocket',
                'Tumblr',
                'Whatsapp',
                'Yummly',
                'Pro_Pinterest'
            );
            $this->load_files( '/functions/social-networks/', $social_networks);


			/**
			 * The Utilities Classes
			 *
			 */
			$utilities = array(
				'Meta_Box_Loader',
				'Pro_Pinterest_Shortcode'
			);

			$this->load_files( '/functions/utilities/', $utilities );
            // $this->add_pinterest_description_field();


    		require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Options_Page.php';

	}


    /**
     * Instantiates the addon's functionality.
     *
     * @return void
     *
     */
	public function instantiate_addon() {
        if ( $this->is_registered()) :
            new SWP_Pro_Options_Page();
            new SWP_Pro_Pinterest();
        else:
            // die("not registerd");
        endif;
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
	 * Defer the loading of functions.
	 * We don't want these functions to run until after core has loaded.
	 *
	 * @param  none
	 * @return none
	 *
	 */
	public function initiate_plugin() {

        require_once SWPP_PLUGIN_DIR . '/functions/meta-box/meta-box.php';
        require_once SWPP_PLUGIN_DIR . '/functions/utilities/utility.php';
        require_once SWPP_PLUGIN_DIR . '/functions/admin/post-options.php';
        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/SWP_Pro_Header_Output.php';

		new SWP_Pro_Header_Output();
		new SWP_Meta_Box_Loader();

		if ( is_admin() ) {
	        require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Settings_Link.php';
			new SWP_Pro_Settings_link();
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
	    $options = get_option( 'social_warfare_settings', array() );
	    if( !empty($options['premiumCode']) && empty( $options['pro_license_key'] ) ):
	        echo '<div class="notice-error notice is-dismissable"><p>' . __( '<b>Important:</b> We’ve just made some significant upgrades to your <i>Social Warfare - Pro</i> license. You will need to <a href="https://warfareplugins.com/my-account/">grab your license key</a> and re-register the plugin. Read <a href="https://warfareplugins.com/support/how-to-register-your-license-key/">the full details</a> to find out why this change was necessary.', 'social-warfare' ) . '</p></div>';
	    endif;
	}

    /**
     * Loads an array of related files.
     *
     * @param  string   $path  The relative path to the files home.
     * @param  array    $files The name of the files (classes), no vendor prefix.
     * @return none     The files are loaded into memory.
     *
     */
    private function load_files( $path, $files ) {
        foreach( $files as $file ) {

            //* Add our vendor prefix to the file name.
            $file = "SWP_" . $file;
            require_once SWPP_PLUGIN_DIR . $path . $file . '.php';
        }
    }

    
}
