<?php

/**
 * The SWP_Pro_Analytics_Database class will control all of interactions that
 * our analytics suite will have with the database. It will handle creating the
 * table for new installs, and it will handle inserting and updating records
 * when share counts are fetched to allow us to have historical data for our
 * charts and graphs to present to the user.
 *
 * @since 4.1.0 | 29 JUL 2020 | Created
 *
 */
class SWP_Pro_Analytics_Database {


	/**
	 * The name of the table in the database. This is left blank here and will
	 * be added in the constructor becaus we'll be using the $wpdb->prefix in
	 * order to ensure that we get the right name.
	 *
	 * @var string
	 *
	 */
	public static $table_name = '';


	/**
	 * The constructor for this class will setup the database table name into a
	 * local static property, and then it will queue up an action hook to allow
	 * fetched share counts to be sent to and processed by this class where they
	 * will be inserted or updated in the database to allow for historical
	 * charting of the information.
	 *
	 * @since  4.1.0 | 29 JUL 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {


		/**
		 * The WordPress Database Access Abstraction Object
		 *
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 *
		 */
		global $wpdb;


		/**
		 * This is the name of the table that we'll be storing our analytics
		 * data in. We'll be sure to concatenate the name to the user's table
		 * prefix.
		 *
		 * @var string
		 *
		 */
		self::$table_name = $wpdb->prefix . 'swp_analytics';


		/**
		 * We'll use an action for this because it essentially allows us to call
		 * this function from core without throwing an error if this function
		 * doesn't actually exist. It's a safer, WordPress friendly way of
		 * making that function pluggable.
		 *
		 */
		add_action('swp_analytics_record_shares', array($this, 'record_share_counts' ), 10, 2 );
	}


	/**
	 * The record_share_counts() method will be attached to an action hook so
	 * that it can be called in a fully pluggable manner.
	 *
	 * The idea here is for the SWP_Post_Cache class in core to simply call this
	 * action hook while passing in the post_id and an array of share counts,
	 * and this method will then take care of organizing them into our analytics
	 * table accordingly.
	 *
	 * This will generate a date timestamp in the following format: YYYY-MM-DD.
	 * Since many posts are checked repeatedly throughout the day, this method
	 * will first check if an entry for this post on this day already exists. If
	 * it does exist, we'll update the existing entry. This means that there will
	 * only be one entry per post per day. The last update for any given day will
	 * be the one that remains in the table and will be used for the historical
	 * record.
	 *
	 * @since  4.1.0 | 29 JUL 2020 | Created
	 * @param  integer $post_id The integer of the post being updated.
	 * @param  array   $counts  A key/value set of share counts for each network.
	 *                          Example: array('facebook' => 55)
	 * @return void
	 *
	 */
	public function record_share_counts( $post_id, $counts ) {
		global $wpdb;
		$table_name = self::$table_name;


		/**
		 * This will create the table in the database if the table has not
		 * already been created. If it has been created, it won't do anything.
		 *
		 */
		$this->setup_database();


		if( 0 !== $post_id ) {
			$this->update_sitewide_shares();
		}


		/**
		 * A column/value associative array is used by many of the $wpdb methods
		 * for updating and inserting data into a table. As such, we'll use this
		 * variable to neatly compile the values that we'll be sending to the db.
		 *
		 * @var array
		 *
		 */
		$data = array();
		$format = array();


		/**
		 * The post_id and the date, when combined, form the unqique key that
		 * we'll use to access each entry in the analytics table. We only want
		 * one entry for each post per day so we'll simply check if these already
		 * exist when determining if we need to insert versus update new data.
		 *
		 */
		$data['post_id'] = $post_id;
		$data['date']    = $date = date('Y-m-d');

		$format['post_id'] = '%d';
		$format['date'] = '%s';

		/**
		 * We need to clean up and process the $shares array. We do this to
		 * ensure that we are only adding networks that offer share counts. We
		 * don't need historical data for networks like 'email', 'more', etc.
		 *
		 */
		foreach( $this->get_valid_networks() as $network ){

			/**
			 * There are aren't any counts for this network, just skip it and
			 * do not add it to our $data array. We don't need it.
			 *
			 */
			if( empty( $counts[$network] ) ) {
				continue;
			}

			// Add this share count to our $data array to be sent to the database.
			$data[$network] = (int) $counts[$network];
		}

		// Here we'll manually tack on the 'total_shares' to our $data array.
		$data['total_shares'] = (int) $counts['total_shares'];


		/**
		 * This is the query that will check to see if any existing entry for
		 * this post on this day already exists.
		 *
		 */
		$query = "SELECT * FROM $table_name WHERE post_id = $post_id && date = '$date'";
		$previous_entry = $wpdb->get_results( $query );


		/**
		 * If there is already an existing entry for this post on this day, then
		 * we'll use the $wpdb->update method and simply update the counts to
		 * reflect the latest data.
		 *
		 */
		if( !empty( $previous_entry ) ) {
			$columns_to_match = array( 'post_id' => $post_id, 'date' => $date );
			$wpdb->update( $table_name, $data, $columns_to_match );
			return;
		}

		// Otherwise we'll insert a new set of share count data.
		$wpdb->insert( $table_name, $data );
	}


	/**
	 * The update_sitewide_shares() method will, well, update the sitewide totals
	 * for each network including the total shares. This will allow us to use
	 * the data for charts and graphs.
	 *
	 * @since  4.1.0 | 03 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function update_sitewide_shares() {

		// Since we'll be querying the database for sums.
		global $wpdb;

		// Get a list of the networks that we'll be providing analytics for.
		$networks = $this->get_valid_networks();
		$networks[] = 'total';


		/**
		 * In this section we'll loop through each of the valid networks, and
		 * we'll reach into the postmeta database and get the sum of all of the
		 * share count data for each network.
		 *
		 */
		foreach( $networks as $network_key ) {
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

		$network_shares['total_shares'] = $network_shares['total'];
		unset($network_shares['total']);
		$this->record_share_counts( 0, $network_shares );
	}


	/**
	 * The create_database() method will be used to handle the initial creation
	 * and setup of a table that we'll use to store all of the analytics data.
	 *
	 * Some notes on swp_analytics_database_version:
	 *
	 * We'll use an autoloaded option in the database so that we don't have to
	 * build out a 'on activation' class to handle database tasks when the plugin
	 * is updated and installed. Instead, we'll simply check to see if our option
	 * is current, and then bail out if it is. If it doesn't exist or isn't
	 * current, then we'll create the database or update it.
	 *
	 * @since  4.1.0 | 29 JUL 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function setup_database() {
		global $wpdb;

		// Setup the table name and fetch the existing charset.
		$table_name      = self::$table_name;
		$charset_collate = $wpdb->get_charset_collate();

		// If the table already exists, bail out.
		if(get_option('swp_analytics_database_version') === SWPP_VERSION ) {
			return;
		}

		// Make the version dependant on the current version of the pro plugin.
		update_option('swp_analytics_database_version', SWPP_VERSION );

		// This will return an array of networks that support share counts.
		$networks = $this->get_valid_networks();

		/**
		 * This will loop through the array of valid networks and generate a
		 * string that can be inserted into the sql query to ensure that we get
		 * a column for each of the eligible networks.
		 *
		 */
		$networks_string = '';
		foreach( $networks as $network ) {
			$networks_string .= "$network bigint(32) DEFAULT 0," . PHP_EOL;
		}


		/**
		 * This is the actual sql query that will be used to create the table in
		 * the database.
		 *
		 */
		$sql = "CREATE TABLE $table_name (
		  id int(15) NOT NULL AUTO_INCREMENT,
		  date date DEFAULT '0000-00-00' NOT NULL,
		  post_id int(15) NOT NULL,
		  total_shares bigint(32) DEFAULT 0,
		  $networks_string
		  PRIMARY KEY  (id)
		) $charset_collate;";

		// This file must be included to use dbDelta or it will throw errors.
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
	}


	/**
	 * The get_valid_networks() method will generate an array of network keys
	 * based on which networks support share counts. Those that do not support
	 * share counts will not be a part of our analytics suite since it is showing
	 * data based on historical share counts.
	 *
	 * This also makes a manual exception ensuring that Twitter gets added even
	 * if the user has not set up the Twitter share counts yet.
	 *
	 * @since  4.1.0 | 29 JUL 2020 | Created
	 * @param  void
	 * @return array An array of valid network keys.
	 *
	 */
	private function get_valid_networks() {

		// Pull in the global variable containing all of the network objects.
		global $swp_social_networks;

		// Loop through all of the registered social networks in the system.
		foreach( $swp_social_networks as $network ) {

			// Don't add networks that don't support share counts from the API's.
			if( 0 === $network->get_api_link('') && $network->key !== 'twitter' ) {
				continue;
			}

			// Add each compatible network to the sql query string.
			$networks[] = $network->key;
		}
		return $networks;
	}
}
