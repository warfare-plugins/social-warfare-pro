<?php

/**
 * A class to house and register all of the Social Warfare - Pro advanced
 * shortcodes for use in posts and widgets (if a widget shortcode plugin is
 * active).
 *
 * @package   SocialWarfare\Frontend-Output
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
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
	 * @since  4.0.0 | 10 JUL 2019
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


	public function __call( $name, $arguments ) {

		global $swp_social_networks;
		foreach($swp_social_networks as $network_key => $network_object ) {
			if (strpos($name, $network_key) !== false) {
				$key = $network_key;
			}
		}

		if ( strpos( $name, 'sitewide' ) !== false ) {
			return swp_kilomega( $this->get_total_shares() );
		}

		return $this->display_post_shares ( $key );

	}

	public function display_sitewide_shares() {
		return swp_kilomega( $this->get_total_shares() );
	}

	protected function display_post_shares( $network_key ) {
		global $post;
		$shares = get_post_meta( $post->ID, $network_key . '_shares', true );

		return $shares ? swp_kilomega( $shares ) : 0;
	}

	/**
	 * Gets the total shares updated within the past 24 hours.
	 *
	 * If the total shares exist for the requested network and are less than
	 * a day old, the total is returned.
	 * Otherwise, a new total count is created, then returned.
	 *
	 * @return integer The total share counts for network.
	 */
	protected function fetch_sitewide_shares() {
		$network_shares = get_option( 'social_warfare_sitewide_totals' );

		if ( !empty( $network_shares[$this->network_key] ) ) {
			$then = $network_shares[$this->network_key]['timestamp'];
			$now = time();

			if ( 24 * 60 * 60 < ($now - $then) ) {
				return $network_shares[$this->network]['total_shares'];
			}
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
