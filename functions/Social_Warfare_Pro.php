<?php

/**
 * TODO: This file needs to mirror Social_Warfare.php and the Social_Warfare
 * 		 class that is in core.
 */

class Social_Warfare_Pro extends SWP_Addon {

	public function __construct( ) {
        parent::__construct();
        $this->name = 'Social Warfare - Pro';
        $this->key = 'pro';
        $this->product_id = 63157;
        $this->version = '3.0.0';
		$this->load_classes();
		$this->instantiate_classes();

		if ( true === is_admin() ) {
			$this->instantiate_admin_classes();
		}

		$this->registration_update_notification();
		$this->initiate_plugin();
		$this->update_checker();
		// $this->social_warfare_pro_registration_key(1);
	    // add_action( 'admin_notices', array( $this, 'registration_update_notification' ) );
	    if ( $this->version !== $this->core_version ) {
            add_action( 'admin_notices', array( $this, 'mismatch_notification' ) );
        }
	    // add_action( 'plugins_loaded' , array( $this, 'initiate_plugin' , 10 ) );
	    // add_action( 'plugins_loaded' , array( $this, 'update_checker' , 20 ) );
        add_filter( 'swp_registrations', [$this, 'add_self'] );

        if( true === $this->is_registered() ):
        	add_filter( 'swpmb_meta_boxes', [$this, 'register_meta_boxes'] );
        endif;
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
     * Output the meta boxes on the posts page if the plugin is registered
     *
     */


    /**
     * Build the options fields.
     *
     * @param  array $meta_boxes The existing meta boxes.
     * @return array $meta_boxes The modified meta boxes.
     */
    public function register_meta_boxes( $meta_boxes ) {
    	global $swp_user_options;

    	$prefix = 'swp_';
    	$options = $swp_user_options;

    	$twitter_id = isset( $options['twitter_id'] ) ? $options['twitter_id'] : false;

    	$twitter_handle = _swp_get_twitter_handle( $twitter_id );

    	// Setup our meta box using an array.
    	$meta_boxes[0] = array(
    		'id'       => 'social_warfare',
    		'title'    => __( 'Social Warfare Custom Options','social-warfare' ),
    		'pages'    => swp_get_post_types(),
    		'context'  => 'normal',
    		'priority' => apply_filters( 'swp_metabox_priority', 'high' ),
    		'fields'   => array(
    			// Setup the social media image.
    			array(
    				'name'  => '<span class="dashicons dashicons-share"></span> ' . __( 'Social Media Image','social-warfare' ),
    				'desc'  => __( 'Add an image that is optimized for maximum exposure on Facebook, Google+ and LinkedIn. We recommend 1,200px by 628px.','social-warfare' ),
    				'id'    => $prefix . 'ogImage',
    				'type'  => 'image_advanced',
    				'clone' => false,
    				'class' => $prefix . 'ogImageWrapper',
    				'max_file_uploads' => 1,
    			),
    			// Setup the social media title.
    			array(
    				'name'  => '<span class="dashicons dashicons-share"></span> ' . __( 'Social Media Title','social-warfare' ),
    				'desc'  => __( 'Add a title that will populate the open graph meta tag which will be used when users share your content onto Facebook, LinkedIn, and Google+. If nothing is provided here, we will use the post title as a backup.','social-warfare' ),
    				'id'    => $prefix . 'ogTitle',
    				'type'  => 'textarea',
    				'class' => $prefix . 'ogTitleWrapper',
    				'clone' => false,
    			),
    			// Setup the social media description.
    			array(
    				'name'  => '<span class="dashicons dashicons-share"></span> ' . __( 'Social Media Description','social-warfare' ),
    				'desc'  => __( 'Add a description that will populate the open graph meta tag which will be used when users share your content onto Facebook, LinkedIn, and Google Plus.','social-warfare' ),
    				'id'    => $prefix . 'ogDescription',
    				'class' => $prefix . 'ogDescriptionWrapper',
    				'type'  => 'textarea',
    				'clone' => false,
    			),
    			// Divider.
    			array(
    				'name' => 'divider',
    				'id'   => 'divider',
    				'type' => 'divider',
    			),
    			// Setup the pinterest optimized image.
    			array(
    				'name'  => '<i class="sw sw-pinterest"></i> ' . __( 'Pinterest Image','social-warfare' ),
    				'desc'  => __( 'Add an image that is optimized for maximum exposure on Pinterest. We recommend using an image that is formatted in a 2:3 aspect ratio like 735x1102.','social-warfare' ),
    				'id'    => $prefix . 'pinterestImage',
    				'class' => $prefix . 'pinterestImageWrapper',
    				'type'  => 'image_advanced',
    				'clone' => false,
    				'max_file_uploads' => 1,
    			),
    			// Setup the Custom Tweet box.
    			array(
    				'name'  => '<i class="sw sw-twitter"></i> ' . __( 'Custom Tweet','social-warfare' ),
    				'desc'  => ( $twitter_id ? sprintf( __( 'If this is left blank your post title will be used. Based on your username (@%1$s), <span class="tweetLinkSection">a link being added,</span> and the current content above, your tweet has %2$s characters remaining.','social-warfare' ),str_replace( '@','',$twitter_handle ),'<span class="counterNumber">140</span>' ) : sprintf( __( 'If this is left blank your post title will be used. <span ="tweetLinkSection">Based on a link being added, and</span> the current content above, your tweet has %s characters remaining.','social-warfare' ),'<span class="counterNumber">140</span>' )),
    				'id'    => $prefix . 'customTweet',
    				'class' => $prefix . 'customTweetWrapper',
    				'type'  => 'textarea',
    				'clone' => false,
    			),
    			// Setup the pinterest description.
    			array(
    				'name'  => '<i class="sw sw-pinterest"></i>' . __( 'Pinterest Description','social-warfare' ),
    				'desc'  => __( 'Craft a customized description that will be used when this post is shared on Pinterest. Leave this blank to use the title of the post.','social-warfare' ),
    				'id'    => $prefix . 'pinterestDescription',
    				'class' => $prefix . 'pinterestDescriptionWrapper',
    				'type'  => 'textarea',
    				'clone' => false,
    			),
    			array(
    				'name'    => '<i class="sw sw-pinterest"></i> ' . __( 'Pin Image for Browser Extensions','social-warfare' ),
    				'id'      => 'swp_pin_browser_extension',
    				'class'   => 'swp_pin_browser_extensionWrapper',
    				'type'    => 'select',
    				'options' => array(
    					'default' => __( 'Default','social-warfare' ),
    					'on'      => __( 'On','social-warfare' ),
    					'off'     => __( 'Off','social-warfare' ),
    				),
    				'clone' => false,
    				'std'   => 'default',
    			),
    			array(
    				'name'    => '<i class="sw sw-pinterest"></i> ' . __( 'Pin Browser Image Location','social-warfare' ),
    				'id'      => 'swp_pin_browser_extension_location',
    				'class'   => 'swp_pin_browser_extension_locationWrapper',
    				'type'    => 'select',
    				'options' => array(
    					'default' => __( 'Default','social-warfare' ),
    					'hidden'  => __( 'Hidden','social-warfare' ),
    					'top'     => __( 'At the Top of the Post','social-warfare' ),
    					'bottom'  => __( 'At the Bottom of the Post','social-warfare' ),
    				),
    				'clone' => false,
    				'std'   => 'default',
    			),
    			// Set up the location on post options.
    			array(
    				'name'    => '<span class="dashicons dashicons-randomize"></span> ' . __( 'Horizontal Buttons Location','social-warfare' ),
    				'id'      => $prefix . 'postLocation',
    				'class'   => $prefix . 'postLocationWrapper',
    				'type'    => 'select',
    				'options' => array(
    					'default' => __( 'Default','social-warfare' ),
    					'above'   => __( 'Above the Content','social-warfare' ),
    					'below'   => __( 'Below the Content','social-warfare' ),
    					'both'    => __( 'Both Above and Below the Content','social-warfare' ),
    					'none'    => __( 'None/Manual Placement','social-warfare' ),
    				),
    				'clone' => false,
    				'std'	=> 'default',
    			),
    			array(
    				'name'    => '<span class="dashicons dashicons-randomize"></span> ' . __( 'Floating Buttons','social-warfare' ),
    				'id'      => $prefix . 'floatLocation',
    				'class'   => $prefix . 'floatLocationWrapper',
    				'type'    => 'select',
    				'options' => array(
    					'default' => __( 'Default','social-warfare' ),
    					'on'      => __( 'On','social-warfare' ),
    					'off'     => __( 'Off','social-warfare' ),
    				),
    				'clone' => false,
    				'std'   => 'default',
    			),
    			array(
    				'name'  => 'divider2',
    				'id'    => 'divider2',
    				'type'  => 'divider',
    			),
    			array(
    				'name'  => $twitter_handle,
    				'id'    => 'twitter_id',
    				'class' => 'twitterIDWrapper',
    				'type'  => 'hidden',
    				'std'   => $twitter_handle,
    			),
    			array(
    				'name'  => (is_swp_addon_registered('pro') ? 'true' : 'false'),
    				'id'    => (is_swp_addon_registered('pro') ? 'true' : 'false'),
    				'class' => 'registrationWrapper',
    				'type'  => 'hidden',
    				'std'   => (is_swp_addon_registered('pro') ? 'true' : 'false'),
    			),
    		),
    	);

    	return $meta_boxes;
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
    		 * The Social Network Classes
    		 *
    		 * This family of classes provides the framework and the model needed for creating
    		 * a unique object for each social network. It also provides for maximum extensibility
    		 * to allow addons even easier access than ever before to create and add more social
    		 * networks to the plugin.
    		 *
    		 */
    		$social_networks = [
    			'Buffer',
    			'Reddit',
    			'Flipboard',
                'Email',
                'Hackernews',
                'Pocket',
                'Tumblr',
                'Whatsapp',
                'Yummly'
    		];
    		$this->load_files( '/functions/social-networks/', $social_networks);


	        /**
	         * Include the necessary files
	         *
	         */
	        require_once SWPP_PLUGIN_DIR . '/functions/meta-box/meta-box.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/utilities/utility.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/admin/post-options.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/SWP_Pro_Header_Output.php';
	        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/scripts.php';


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
		echo '<div class="update-nag notice is-dismissable"><p>' . __( '<b>Important:</b> You are currently running Social Warfare v'.SWP_VERSION.' and Social Warfare - Pro v'.SWPP_VERSION.'. In order to avoid conflicts, these two version need to match in order to activate all of the plugins features. Please update the appropriate plugin so that both Social Warfare and Social Warfare - Pro are on the same version. For more information about this, <a href="https://warfareplugins.com/support/updating-social-warfare-social-warfare-pro/">please read this</a>. ', 'social-warfare' ) . '</p></div>';
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
	        if( $this->is_registered() ) {

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
