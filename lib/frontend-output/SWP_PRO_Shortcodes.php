<?php

/**
 * A class to house and register all of the Social Warfare - Pro advanced
 * shortcodes for use in posts and widgets (if a widget shortcode plugin is
 * active).
 *
 * @package   Social Warfare Pro\Frontend-Output
 * @copyright Copyright (c) 2019, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     4.0.0 | 09 JUL 2019 | Created
 *
 */
class SWP_Pro_Shortcodes {


	/**
	 * The Magic Constructor. This will set up and register all of the
	 * shortcodes that can be used with the plugin. It will add shortcodes for
	 * each of the networks that the plugin (and any addons) supports.
	 *
	 * @since  4.0.0 | 10 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {


		/**
		 * The $swp_social_networks global is an array of social network objects
		 * that correspond to all the available networks available to be used
		 * as share buttons in a buttons panel.
		 *
		 * We'll loop through this so that user's can access share counts via
		 * these shortcodes for all available networks.
		 *
		 */
		global $swp_social_networks;
		foreach($swp_social_networks as $network_key => $network_object ) {
			$network = $network_object->key;


			/**
			 * Use the magic __call method, we'll intercept these dynamically
			 * named method calls and parse the name to come up with the correct
			 * response in order to provide these share counts.
			 *
			 */
			add_shortcode( "${network}_shares", array( $this, "display_${network}_share_counts" ) );
			add_shortcode( "sitewide_${network}_shares", array( $this, "display_sitewide_${network}_share_counts" ) );
		}
	}


	/**
	 * The Magic Call Method
	 *
	 * This allows us to intercept calls to methods that don't actually exist.
	 * We can then parse the $name string to find out which of our shortcodes
	 * that we registered above in the constructor and return the proper response.
	 *
	 * @since  4.0.0 | 10 JUL 2019 | Created
	 * @param  string $name      The name of the class method being called.
	 * @param  array  $arguments An array of arguments passed to that method.
	 * @return mixed  String of share count if valid call is made. False if not.
	 *
	 */
	public function __call( $method_name, $arguments ) {


		/**
		 * We'll loop through the available networks in the plugin and see if
		 * the method name being called contains the unique, snake-cased key for
		 * one of the networks. If so, then that is the network for which we
		 * will fetch and display share counts.
		 *
		 */
		global $swp_social_networks;
		foreach($swp_social_networks as $key => $network_object ) {
			if( strpos( $method_name, $key ) !== false ) {
				$network = $key;
			}
		}


		/**
		 * If the method name contains the string "sitewide" then the user is
		 * requesting the aggragate sum of all shares for this network accross
		 * their entire website. Otherwise, they are just requesting share counts
		 * for this specific post.
		 *
		 */
		if ( strpos( $method_name, 'sitewide' ) !== false ) {
			return swp_kilomega( $this->get_total_shares() );
		}
		return $this->fetch_post_shares( $network );

	}

	public function display_sitewide_shares() {
		return swp_kilomega( $this->get_total_shares() );
	}

	protected function fetch_post_shares( $network_key ) {
		global $post;
		$shares = get_post_meta( $post->ID, $network_key . '_shares', true );

		return $shares ? swp_kilomega( $shares ) : 0;
	}


	/**
	 * A method to update the sitewide aggragate share counts for each network.
	 *
	 * This method will check the timestamp and then loop through all of the
	 * social networks and then update the sitewide totals so that they will be
	 * cached and readily available for the next 24 hours.
	 *
	 * @since  4.0.0 | 10 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	protected function update_sitewide_shares() {


		/**
		 * A WordPress option to allow us to store a timestamp and our aggragate
		 * totals. We can check this field to see if they need updated. If not,
		 * we'll just use the cached totals that are already in place.
		 *
		 * If it hasn't been created yet, it will return FALSE and as such, we'll
		 * check if it's an array and declare it as one and declare an expired
		 * timestamp so that it will still trigger the recount below.
		 *
		 */
		$network_shares = get_option( 'social_warfare_sitewide_totals' );
		if( !is_array( $network_shares ) ) {
			$network_shares              = array();
			$network_shares['timestamp'] = 0;
		}


		
		$timestamp    = $network_shares['timestamp'];
		$current_time = time();
		if ( 24 * 60 * 60 < ( $current_time - $timestamp ) ) {
			return;
		}

		global $swp_social_networks;
		foreach($swp_social_networks as $key => $network_object ) {

		if ( !empty( $network_shares[$this->network_key] ) ) {

		}

		//* The total count has not been updated in 24 hours.
		$updated_total = $this->get_total_shares();
		$network_shares[$this->network_key] = [
			'timestamp'     => time(),
			'total_shares'  => $updated_total
		];

		update_option( 'social_warfare_sitewide_totals', $network_shares );

		return $updated_total;
	}


	/**
	 * Fetches total share counts for all currently registred networks.
	 *
	 * If they add networks later, they will not be in here. Instead,
	 * the shortcode will check to see if the network has data. If not
	 * it will be created and saved at that time.
	 *
	 * @return bool True if successfully created, false otherwise.
	 *
	 */
	protected function init_database() {
		global $swp_social_networks;
		$share_data = [];

		foreach ($swp_social_networks as $network_key => $object) {
			$total = $this->get_total_shares( $network );
			$share_data[$this->network_key] = [
				'timestamp'     => time(),
				'total_shares'  => $this->get_total_shares( $network_key )
			];
		}

		return add_option( 'social_warfare_enhanced_shortcode', $share_data );
	}

	/**
	 * Counts the total share data aggregated across posts.
	 *
	 * This reads all post meta for a given network, sums it,
	 * and returns it as an integer.
	 *
	 *
	 * @return integer The total share counts for a given network.
	 *
	 */
	protected function get_total_shares( $network_key = null ) {
		if ( null === $network_key ) {
			$network_key = $this->network_key;
		}

		$query = "SELECT
				SUM(meta_values)
				AS total
				FROM $wpdb->postmeta
				WHERE meta_key = $network_key";

		$sum = $wpdb->get_results( $total );
		return $sum[0]->total;
	}
}
