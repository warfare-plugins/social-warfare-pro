<?php

/**
 * A class to load up all of this plugin's social networks.
 *
 * The purpose of this class is to create a global social networks array and
 * then to load up and instantiate each of the social networks as objects into
 * that array.
 *
 * @since 4.0.0 | 20 JUL 2019 | Created
 *
 */
class SWP_Pro_Networks_Loader {


	/**
	 * The Magic __construct method.
	 *
	 * This method creates the global $swp_social_networks array and then queues
	 * up the instantiation of all the networks to be run after all the plugin
	 * including all addons have been loaded.
	 *
	 * @since  4.0.0 | 20 JUL 2019 | Created
	 * @param  void
	 * @return void
	 * @access public
	 *
	 */
	public function __construct() {

		// Create a global array to contain our social network objects.
		global $swp_social_networks;
		$swp_social_networks = array();

		add_filter( 'plugins_loaded' , array( $this , 'instantiate_networks' ) , 999 );
	}


	/**
	 * Instantiate all the networks.
	 *
	 * This class loops through every single declared child class of the
	 * primary SWP_Social_Network class and fires it up.
	 *
	 * @since  3.0.0 | Created | 06 APR 2018
	 * @param  none
	 * @return none
	 * @access public
	 *
	 */
	public function instantiate_networks() {
			new SWP_Blogger();
			new SWP_Buffer();
			new SWP_Email();
			new SWP_Evernote();
			new SWP_Print();
			new SWP_More();
			new SWP_Flipboard();
			new SWP_Hackernews();
			new SWP_Parler();
			new SWP_Pocket();
			new SWP_Reddit();
			new SWP_Telegram();
			new SWP_Tumblr();
			new SWP_Viber();
			new SWP_VKontakte();
			new SWP_Whatsapp();
			new SWP_Xing();
			new SWP_Yummly();
			new SWP_Mastodon();
	}

}
