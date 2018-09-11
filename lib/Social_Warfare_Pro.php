<?php

if ( !class_exists( 'Social_Warfare_Addon' ) ) :
    require_once SWP_PLUGIN_DIR . '/lib/Social_Warfare_Addon';
endif;

class Social_Warfare_Pro extends Social_Warfare_Addon {

	public function __construct() {
        //* Define the Addon data.
        $this->name = 'Social Warfare - Pro';
        $this->key = 'pro';
        $this->product_id = 63157;
        $this->version = SWPP_VERSION;
        $this->core_required = '3.3.0';

        parent::__construct();

        $this->filepath = SWPP_PLUGIN_FILE;
		$this->load_classes();

        if ( $this->is_registered && ! Social_Warfare::has_plugin_conflict() ) {
            $this->load_networks();
            add_action( 'wp_loaded', array( $this, 'instantiate_addon') );
            // add_action( 'plugins_loaded', array( $this, 'instantiate_addon') );
            $this->initiate_plugin();
        }
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
        require_once SWPP_PLUGIN_DIR . '/lib/meta-box/meta-box.php';
        require_once SWPP_PLUGIN_DIR . '/lib/frontend-output/SWP_Pro_Header_Output.php';

        if ( class_exists( 'SWP_Pro_Header_Output' ) ) :
    		new SWP_Pro_Header_Output();
        endif;

		new SWP_Meta_Box_Loader();

        //* Load admin-specific classes.
		if ( is_admin() ) {
	        require_once SWPP_PLUGIN_DIR . '/lib/admin/SWP_Pro_Settings_Link.php';
    		new SWP_Pro_Settings_link();
		}
	}


    /**
     * Instantiates the addon's functionality.
     *
     * @return void
     *
     */
	public function instantiate_addon() {
        new SWP_Pro_Options_Page();
        new SWP_Pro_Pinterest();
	}


	public function load_classes() {


		/**
		 * The Utilities Classes
		 *
		 */
		$utilities = array(
			'Meta_Box_Loader'
		);

		$this->load_files( '/lib/utilities/', $utilities );

		require_once SWPP_PLUGIN_DIR . '/lib/admin/SWP_Pro_Options_Page.php';
	}


    public function load_networks() {
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
        $this->load_files( '/lib/social-networks/', $social_networks);
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
