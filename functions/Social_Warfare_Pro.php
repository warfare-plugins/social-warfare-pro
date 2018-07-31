<?php

if (class_exists( 'SWP_Addon' ) ) :

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

		$this->initiate_plugin();

        add_filter( 'swp_registrations', array( $this, 'add_self' ) );
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

        if ( class_exists( 'SWP_Header_Output' ) ) :
    		new SWP_Pro_Header_Output();
        endif;

		new SWP_Meta_Box_Loader();

		if ( is_admin() ) {
	        require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Settings_Link.php';

            if ( class_exits( 'SWP_Pro_Settings_Link' ) ) :
    			new SWP_Pro_Settings_link();
            endif;
		}
	}


    /**
     * Instantiates the addon's functionality.
     *
     * @return void
     *
     */
	public function instantiate_addon() {
        if ( $this->is_registered()) :
            if ( class_exists( 'SWP_Pro_Options_Page' ) ) :
                new SWP_Pro_Options_Page();
            endif;

            new SWP_Pro_Pinterest();
        endif;
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

		require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Options_Page.php';
	}


    /**
     * Loads an array of sibling files.
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

endif;
