<?php

/**
 * A class of functions used to load the plugin files and functions
 *
 * This is the class that brings the entire plugin to life. It is used to
 * instatiate all other classes throughout the plugin.
 *
 * This class also serves as a table of contents for all of the plugin's
 * functionality. By browsing below, you will see a brief description of each
 * class that is being instantiated.
 *
 * LOAD vs. INSTANTIATE
 * Definitional Note: The term "load" or "loaded" in this file always refers to
 * simply including a class's file. The term "instantiate" refers to actually
 * calling and activating a class.
 *
 * @package   Social_Warfare
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     3.0.0 | 19 FEB 2018 | Created
 *
 */
class Social_Warfare_Pro extends Social_Warfare_Addon {


	/**
	 * The magic method used to instantiate this class.
	 *
	 * This method will load all of the classes using the "require_once" command.
	 * It will then instantiate them all one by one. This will also establish
	 * the properties needed to load up this addon class, as well as keep the
	 * plugin from loading if dependancies are missing or known conflicts are
	 * found.
	 *
	 * @since  3.0.0  | 19 FEB 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {


		/**
		 * These properties are used by the parent constructor to estrablish
		 * the addon object. They will be used to add the registration fields
		 * to the options page, to control and respond to the the current
		 * registration status, etc.
		 *
		 */
        $this->name          = 'Social Warfare - Pro';
        $this->key           = 'pro';
        $this->core_required = '3.3.0';
        $this->product_id    = 63157;
        $this->version       = SWPP_VERSION;
        $this->filepath      = SWPP_PLUGIN_FILE;

        parent::__construct();


		/**
		 * This load up, but does not instantiate, all of the classes in this
		 * pro addon. Also note, it does not load the social network classes as
		 * all loaded social network classes will be auto-instantiated. As such,
		 * we don't want to load those up unless the plugin is registered and we
		 * don't have any conflicts that would make us want to keep from loading.
		 *
		 */
		$this->load_classes();


		/**
		 * If the plugin is registered and we don't have any conflicts then we
		 * will proceed to instantiate the classes that were loaded above.
		 *
		 */
        if ( $this->is_registered && false == Social_Warfare::has_plugin_conflict() ) {

            $this->instantiate_classes();


			/**
			 * We are deferring certain classes to the "wp_loaded" hook because
			 * they need to make use of certin conditional like is_singular()
			 * which are not available until then.
			 *
			 */
            add_action( 'wp_loaded', array( $this, 'instantiate_deferred_classes') , 20 );

        }
	}


	/**
	 * This is the method that will load the classes. This method includes the
	 * necessary files, but it does not instantiate the classes.
	 *
	 * @since  3.0.0 | 01 MAR 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function load_classes() {


		/**
		 * Load up the social networks. The social networks are auto-instantiated
		 * so while they are loaded here, core itself will see them and fire
		 * them up. This is always why we are making sure this addon is registered
		 * before loading them.
		 *
		 */
 		if ( true === $this->is_registered ) {
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
		 * The Options Classes
		 *
		 * While this is an options class, we need to be able to access it via
		 * the frontend as well so we need to load it up everywhere, not just
		 * in the admin area.
		 *
		 */
		$options = array(
			'Pro_Options_Page'
		);
		$this->load_files( '/lib/admin/', $options );


		/**
		 * The Admin Classes
		 *
		 * These files load up all the classes that we use to populate options,
		 * links, and whatnot inside that WordPress admin area.
		 *
		 */
		if ( true === is_admin() ) {
			$admin = array(
				'Meta_Box_Loader',
				'Pro_Settings_Link'
			);
			$this->load_files( '/lib/admin/', $admin );
		}


		/**
		 * The Frontend-Output Classes
		 *
		 * The classes in this section are used in the processing and filtering
		 * of the frontend content like header meta tags, content filters, etc.
		 *
		 */
		$frontend_output = array(
			'Pro_Header_Output'
		);
		$this->load_files( '/lib/frontend-output/', $frontend_output );


		/**
		 * This is manually required because it is a third party class and as
		 * such doesn't fit the naming structure needed to work with the
		 * load_files() method being used above.
		 *
		 * We use this class to populate the meta fields on the post editor.
		 *
		 */
		require_once SWPP_PLUGIN_DIR . '/lib/meta-box/meta-box.php';

	}


    /**
	 * A Method to instantiate all the classes that were loaded above.
	 *
	 * @since  3.0.0 | 01 MAR 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function instantiate_classes() {


		/**
		 * Header Output
		 *
		 * This is the class that extends the Header_Output class in the
		 * core plugin. It controls the output of things like the Open
		 * Graph tags, Twitter Card tags, and the dynamically generated
		 * custom color CSS.
		 *
		 * Since this class extends a core class, it is wrapped in an
		 * existence check before it is defined. So we also wrap the actual
		 * call to instantiate in a check for existence.
		 *
		 */
        if ( class_exists( 'SWP_Pro_Header_Output' ) ) {
    		new SWP_Pro_Header_Output();
        }

		if( true == is_admin() ) {


			/**
			 * Meta Box Loader
			 *
			 * This is the class that loads up all of the "Social Warfare
			 * Custom Options" fields on the post editor so that users have
			 * a place to input their custom tweets, Pinterest images, etc.
			 *
			 */
			new SWP_Meta_Box_Loader();


			/**
			 * This is the class that adds the the "settings" link to the
			 * plugins listing page.
			 *
			 */
			new SWP_Pro_Settings_link();
		}

	}


    /**
     * Instantiates the addon's deferred classes.
     *
     * @since  3.0.0 | 01 MAR 2018 | Created
     * @param  void
     * @return void
     *
     */
	public function instantiate_deferred_classes() {
        new SWP_Pro_Options_Page();
		new SWP_Pro_Pinterest();
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
