<?php

/**
 * A class to house and register all of the Social Warfare - Pro advanced
 * shortcodes for use in posts and widgets (if a widget shortcode plugin is
 * active).
 *
 * This class will loop through all social media networks and register two
 * shortcodes per network for displaying share counts:
 *
 *    A. [network_name_shares] such as [twitter_shares]
 *       This will display the share count, in this case Twitter share counts,
 *       for the current post.
 *
 *    B. [sitewide_network_name_shares] such as [sitewide_twitter_shares]
 *       This will display the share counts, in this case Twitter share counts,
 *       for the entire website. This will add up share counts for all posts,
 *       pages, and custom post types and add them up into one cumalitive total.
 *
 *    C. [total_shares] and [sitewide_total_shares]
 *       These two shortcodes will behave the same as above except that they
 *       will bring out the 'total shares' field instead of an individual
 *       social network.
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
			add_shortcode( "{$network}_shares", array( $this, "display_{$network}_share_counts" ) );
			add_shortcode( "sitewide_{$network}_shares", array( $this, "display_sitewide_{$network}_share_counts" ) );
		}

	   add_shortcode( "total_shares", array( $this, "display_total_share_counts" ) );
	   add_shortcode( "sitewide_total_shares", array( $this, "display_sitewide_total_share_counts" ) );
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
		 * Bail out if this isn't being called via a valid method name that we
		 * created above. This will keep any bizarre requests at bay.
		 *
		 */
		if( strpos( $method_name, 'display' ) === false &&
		    strpos( $method_name, 'share_counts' ) === false ) {
			return;
		}


		/**
		 * We'll loop through the available networks in the plugin and see if
		 * the method name being called contains the unique, snake-cased key for
		 * one of the networks. If so, then that is the network for which we
		 * will fetch and display share counts.
		 *
		 */
		global $swp_social_networks;
		$networks = $swp_social_networks;
		$networks['total'] = true;
		foreach($networks as $key => $network_object ) {
			if( strpos( $method_name, $key ) !== false ) {
				$network = $key;
			}
		}


		/**
		 * If no network was matched to the name of the method being called,
		 * then just bail out and move on.
		 *
		 */
		if( empty( $network ) ) {
			return;
		}


		/**
		 * If the method name contains the string "sitewide" then the user is
		 * requesting the aggragate sum of all shares for this network accross
		 * their entire website. Otherwise, they are just requesting share counts
		 * for this specific post.
		 *
		 */
		if ( strpos( $method_name, 'sitewide' ) === false ) {
			return $this->fetch_post_shares( $network );
		}
		return $this->fetch_sitewide_shares( $network );

	}


	/**
	 * A method to fetch share counts for an individual post for a specific
	 * social media network. These can be accessed via shortcode using the
	 * following naming pattern:
	 *
	 * [network_name_shares]
	 * [twitter_shares]
	 * [facebook_shares]
	 *
	 * @since  4.0.0 | 10 JUL 2019 | Created
	 * @since  4.4.4 | 24 OCT 2023 | Escaped output to prevent potential XSS vulnerabilities
	 * @param  string $network_key The unique, snake_cased key for the network.
	 * @return string The formatted string of the share counts.
	 *
	 */
	protected function fetch_post_shares( $network ) {
		global $post;
		$shares = get_post_meta( $post->ID, '_' . $network . '_shares', true );
		return esc_html( $shares ? SWP_Utility::kilomega( $shares ) : 0 );
	}


	/**
	 * A method to fetch the aggragate, site-wide shares for a particular
	 * social media network. For example, this can fetch ALL Facebook activity
	 * across all pages, posts and the home page across the entire site. It can
	 * be accessed using the following shortcode formates.
	 *
	 * [sitewide_network_name_shares]
	 * [sitewide_twitter_shares]
	 * [sitewide_facebook_shares]
	 *
	 * @since  4.0.0 | 10 JUL 2019 | Created
	 * @since  4.4.4 | 24 OCT 2023 | Escaped output to prevent potential XSS vulnerabilities
	 * @param  string $network The unique, snake_cased key for the network.
	 * @return string The formatted string of the share counts.
	 *
	 */
	protected function fetch_sitewide_shares( $network ) {
		$this->update_sitewide_shares();
		$network_shares = get_option( 'social_warfare_sitewide_totals' );
		return esc_html( SWP_Utility::kilomega( $network_shares[$network] ) );
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
			$network_shares = array();
			$network_shares['timestamp'] = 0;
		}


		/**
		 * Check if the timestamp is older than 24 hours old. If not, bail out
		 * and just keep the current share totals in place for continued use.
		 *
		 */
		if ( 24 * 60 * 60 > ( time() - $network_shares['timestamp'] ) ) {
			return;
		}


		/**
		 * Now that we've decided the cache is expired, we'll want to go ahead
		 * and fetch new share count totals from across all the postmeta fields
		 * on the site. We'll grab the global $swp_social_networks variable
		 * which is an array of social network objects. We'll also add an item
		 * to the array called 'total' to represent the total shares across all
		 * social networks.
		 *
		 */
		global $swp_social_networks, $wpdb;
		$networks = $swp_social_networks;
		$networks['total'] = true;


		/**
		 * If the timestamp is expired (older than 24 hours) then we'll proceed
		 * by looping through each social network and updating their total,
		 * site-wide share counts.
		 *
		 * We're going to use a query directly on the $wpdb object because that
		 * will be much fast and lightweight than attempting to query posts and
		 * do some sort of complex loop. Plus, since we'll only be doing this
		 * once per day and then storing them in an autoloaded option set, this
		 * should remain very high performance and not put any drag on the server.
		 *
		 */
		foreach( $networks as $network_key => $network_object ) {
			$meta_key = '_' . $network_key . '_shares';
			$total = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT
					SUM(meta_value)
					AS total
					FROM $wpdb->postmeta
					WHERE meta_key = %s",
					$meta_key
				)
			);
			$network_shares[$network_key] = $total ? $total : 0;
		}


		/**
		 * Now we update the timestamp to the current time and stuff all of our
		 * totals back into the database for easy retrieval.
		 *
		 */
		$network_shares['timestamp'] = time();
		update_option( 'social_warfare_sitewide_totals', $network_shares, true );
	}
}
