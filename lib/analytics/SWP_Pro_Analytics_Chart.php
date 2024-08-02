<?php

/**
 * The SWP_Pro_Analytics_Chart class is used to create charts throughout the
 * admin area that display analytics data to the user. These will instantiate
 * a chart object, allow for the use of setters and then render out the html and
 * javascript needed to render an html5 canvas chart using chart.js.
 *
 * @since 4.2.0 | 23 AUG 2020 | Created
 *
 */
class SWP_Pro_Analytics_Chart {


	/**
	 * The unique chart key. This will be used to tie the canvas element to an
	 * indice in our data array in the script tag. This way the chart renderer
	 * can grab the right data with which to populate the chart.
	 *
	 * While this property is automatically generated within the constructor, it
	 * can be overriden using the $Chart->set_chart_key('some_key') method.
	 *
	 * @var string
	 *
	 */
	private $chart_key = '';


	/**
	 * The Post ID. Use 0 to get sitewide totals.
	 *
	 * Can be set in the following manner: $Chart->set_post_id(10);
	 *
	 * @var integer
	 *
	 */
	private $post_id  = 0;


	/**
	 * The Scope. This can be set to 'total_shares', 'all', or the key of any
	 * network (e.g. 'facebook'). This will determine which networks are displayed
	 * on the chart.
	 *
	 * Valid values include: 'total_shares', 'all', 'facebook', 'twitter',
	 *                       'pinterest' (as well as any other network that
	 *                       supports share counts).
	 *
	 * Can be set in the following manner: $Chart->set_scope('all');
	 *
	 * @var string
	 *
	 */
	private $scope    = 'total_shares';


	/**
	 * The interval. This determines what is displayed on each point along the
	 * X axis. It can be set to total shares (how many shares does the post have)
	 * by using 'total', or it can be set to daily (how many shares has the post
	 * gained each day).
	 *
	 * Valid values include:
	 * total: This will result in a line chart being rendered.
	 * daily: This will result in a bar chart being rendered.
	 *
	 * Can be set in the following manner: $Chart->set_interval('daily');
	 *
	 * @var string
	 *
	 */
	private $interval = 'total';


	/**
	 * The CSS classes. The css classes will be appended to the parent container
	 * and can therefore be used to control the layout, spacing, etc.
	 *
	 * Can be set in the following manner: $Chart->set_classes('a_class another_class');
	 *
	 * @var string
	 *
	 */
	private $classes  = '';


	/**
	 * The date range. This will determine how many days past (starting from today)
	 * will be included in the query. 7 days will result in the past week. 30
	 * would be the past month.
	 *
	 * Can be set in the following manner: $Chart->set_range(7);
	 *
	 * @var integer
	 *
	 */
	private $range    = 30;


	/**
	 * The Height of the Container. The charts need to have a height explicitly
	 * provided via the parent container that wraps around the canvas element.
	 * We'll use this attribute to determine the height. We'll add 'px' when we
	 * apply it to the html so don't add them to this value. Just use an integer.
	 *
	 * Can be set in the following manner: $Chart->set_height(200);
	 *
	 * @var integer
	 *
	 */
	private $height   = 400;


	/**
	 * The Step Size. This will determine how often we show the date labels
	 * across the X Axis (across the bottom) of the chart. On a 30 day chart,
	 * that would be 30 dates crammed in along the bottom. It would look better
	 * to just display the date once a week and they can hover and read the
	 * tooltip for anything in between.
	 *
	 * Can be set in the following manner: $Chart->set_step_size(3);
	 *
	 * @var integer
	 *
	 */
	private $step_size = 7;


	/**
	 * The $offset property is used to create extra spacing around the left and
	 * right edges of the chart. This must be switched to 'true' for bar charts
	 * or else the left-most and right-most bars (or series of bars) will be
	 * horizontally centered on the edge effectively hiding half of the bar off
	 * of the edge of the chart.
	 *
	 * @var boolean
	 *
	 */
	private $offset = false;


	/**
	 * The $type property refers to the type of chart that will be rendered.
	 * This is determined using the establish_chart_type() method which will look
	 * at the $interval property and determine whether this should be a line
	 * chart or a bar chart.
	 *
	 * In essence, if we are displaying the trend of total shares over time,
	 * we'll use a line chart. If we are displaying the daily change in shares,
	 * we'll use a bar chart.
	 *
	 * @var string
	 *
	 */
	private $type = 'line';


	/**
	 * The $show_timeframes toggles whether or not the timeframe buttons at the
	 * top of the chart should be displayed to the user. If true, they are shown.
	 * If false, they are no rendered.
	 *
	 * @var boolean
	 *
	 */
	private $show_timeframes = true;


	/**
	 * The Networks Array. This will contain the unique key associated with each
	 * social network (including 'total_shares') for which we have share data.
	 * This is populated internally based on the value of $this->scope.
	 *
	 * @see $this->scope
	 * @var array
	 *
	 */
	private $networks = array();


	/**
	 * The HTML. This will contain the html for the chart. This will also include
	 * the <script> that accompanies the chart that will contain the chart data.
	 * This will all be compiled, then at the end we can just "retun $this->html".
	 *
	 * @var string
	 *
	 */
	private $html = '';


	/**
	 * Insuffient Data
	 *
	 * If there is not enough data yet populated into the database to properly
	 * display this chart, then this property will be toggled to true. This will
	 * allow us to display something else presumably with a message to the user
	 * letting them know why there is no chart there.
	 *
	 * @var boolean
	 *
	 */
	private $insufficient_data = false;
    private $insufficient_networks = false;


	/**
	 * The query results property will store the results of each query in an
	 * associative array. This way if we end up making any queries a second time,
	 * we can just reuse the results from the first time we made the query.
	 *
	 * This is a static property which means that it will persist across all
	 * charts that are generated on any given page load. We'll simply use a base64
	 * encoding of the query itself to create a unique, but repeatable indice for
	 * the array.
	 *
	 * @var array
	 *
	 */
	private static $cached_queries = array();

    /**
     * @var array The query results stored after fetching from the database.
     */
    private $results;

    /**
     * @var string The title of the chart, based on its parameters.
     */
    private $chart_title;

    /**
     * @var array Datasets generated for rendering the chart.
     */
    private $datasets;


	/**
	 * The constructor. The SWP_Pro_Analytics_Chart object can have it's
	 * properties set in one of two ways:
	 *
	 * 1. You can pass in arguments as an associative array. Each argument passed
	 *    will become a local property of the Chart object.
	 * 2. You can use set_property(value) to set any of the properties. Simply
	 *    change the name of the method after the underscore to include the
	 *    property name and pass in the desired value (e.g. set_interval('daily'));
	 *
	 * @since  4.2.0 | 23 AUG 2020 | Created
	 * @param  array $args An associate array of arguments.
	 * @return void
	 *
	 */
	public function __construct( $args = array() ) {

		// Loop through the args and assign them to local properties.
		foreach( $args as $key => $value ) {
			$this->{$key} = $value;
		}


		/**
		 * Generate a unique chart key. This will be used to tie the canvas
		 * element to an indice in our data array in the script tag. This way
		 * the chart renderer can grab the right data with which to populate the
		 * chart.
		 *
		 * @var string
		 *
		 */
		$this->chart_key = 'chart_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5 );
	}


	/**
	 * The _call method will allow us to create dynamic setters for all of our
	 * class properties. We'll also return $this so that we can chain them
	 * together to make setting up our class really swift and easy before
	 * rendering out the html.
	 *
	 * @since  4.2.0 | 24 AUG 2020 | Created
	 * @param  string $name  The name of the method being called (set_scope());
	 * @param  mixed  $value The $value passed into the method.
	 * @return object $this  Allows for method chaining.
	 *
	 */
	public function __call( $name, $value ) {

		// If it's not a setter being called, bail out early.
		if( false === strpos( $name, 'set_' ) ) {
			return;
		}

		// Remove the 'set_' prefix to get the property name.
		$name = str_replace( 'set_', '', $name );

		// If the property exists, update it with the new value.
		if( isset( $this->{$name} ) ) {
			$this->{$name} = $value[0];
		}

		// Return the Chart object to allow for method chaining.
		return $this;
	}


	/**
	 * The render_html() class will use all of the values from the class
	 * properties and then render out the html and JS needed to display the
	 * chart on the page for the user.
	 *
	 * Many of these methods are saved to run after the instantiation to allow
	 * for the caller to use setters in order to setup the chart as they wish
	 * prior to this render process kicking in.
	 *
	 * @since  4.2.0 | 24 AUG 2020 | Created
	 * @param  void
	 * @return string The string of rendered html.
	 *
	 */
	public function render_html() {
		$this->establish_chart_type();
		$this->fetch_from_database( $this->post_id );
		$this->establish_chart_title();
		$this->generate_canvas();
		$this->filter_networks();
		$this->generate_chart_datasets();
		$this->generate_chart_js();
		return $this->html;
	}


	/**
	 * The establish_chart_type() method will determine whether or not this is a
	 * line chart (default) or a bar chart. If the interval for the data is set
	 * to 'daily' then we will render out a bar chart. Otherwise, we'll leave it
	 * set to line chart.
	 *
	 * @since  4.2.0 | 24 AUG 2020 | Created
	 * @see    $this->interval
	 * @param  void
	 * @return void
	 */
	private function establish_chart_type() {
		switch($this->interval) {
			case 'daily':
				$this->type = 'bar';
				$this->offset = true;
				break;
			default:
				$this->type = 'line';
				break;
		}
	}


	/**
	 * The generate_chart_title() method will look at what the parameters are
	 * for the chart and generate a title that explains what the chart is.
	 *
	 * @since  4.2.0 | 24 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function establish_chart_title() {
		global $swp_social_networks;
		$prefix = $start = $middle = $end = '';

		// Examine the post_id
		switch( $this->post_id ) {
			case 0:
				$start = 'Sitewide ';
				break;
		}

		// Examine the scope.
		switch( $this->scope ) {
			case 'all':
				$middle = 'Network Shares ';
				break;
			case 'total_shares':
				$middle = 'Shares ';
				break;
			default:
				$middle = $swp_social_networks[$this->scope]->name . ' ';
				break;
		}

		// Examine the interval
		switch( $this->interval ) {
			case 'daily':
				$prefix = 'Daily ';
				break;
			case 'total':
				$prefix = 'Total ';
				break;
		}

		// Compile the title
		$this->chart_title = $prefix . $start . $middle . $end;
	}


	/**
	 * The generate_timeframe_buttons() method is designed to create the html
	 * for the buttons at the top of the chart that allow the user to change the
	 * timeframe that is being displayed inside of the chart's viewport.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function generate_timeframe_buttons() {


		/**
		 * Timeframe buttons can be turned on or off by setting the local
		 * $show_timeframes to true or false. It is on by default. This checks
		 * that variable and just bails out if they are turned off for this chart.
		 *
		 */
		if( $this->show_timeframes == false ) {
			return;
		}


		/**
		 * The $timeframes variable is an array of timeframes that will displayed
		 * as buttons to the user. We will loop through this array to create
		 * each of the buttons. The key => value sets will provide the information
		 * needed to render the html for each button.
		 *
		 * The following key => value pairs may be included:
		 * name:      The text that will be displayed on the button.
		 * range:     The number of days (starting with today) to display on the chart.
		 * min_range: The minimum number of days available in the data in order
		 *            for this button to displayed as an option.
		 * banned:    An array of chart types on which we will not display this button.
		 *
		 * @var array
		 *
		 */
		$timeframes = array(
			array(
				'name' => 'Week',
				'range' => 7,
				'min_range' => 0
			),
			array(
				'name' => 'Month',
				'range' => 30,
				'min_range' => 7
			),
			array(
				'name' => '3 Months',
				'range' => 91,
				'min_range' => 30,
				'banned' => array('bar')
			),
			array(
				'name' => 'Year-to-Date',
				'range' => date('z') + 1,
				'min_range' => 0,
				'banned' => array('bar')
			),
			array(
				'name' => 'Year',
				'range' => 365,
				'min_range' => 91,
				'banned' => array('bar')
			)
		);

		// Setup the wrapper element. This allows us to center the buttons using CSS.
		$html = '<div class="sw-timeframes">';

		// Loop through and render each timeframe button.
		foreach( $timeframes as $timeframe ) {

			// If this button is banned from this chart, skip it.
			if( isset( $timeframe['banned'] ) && in_array( $this->type, $timeframe['banned'] ) ) {
				continue;
			}

			// Put together the html for this button.
			$html .= '<div class="sw-chart-timeframe '.($this->range === $timeframe['range'] ? 'active' : '').'" data-range="'.$timeframe['range'].'" data-chart="'.$this->chart_key.'">'.$timeframe['name'].'</div>';
		}

		// Close the wrapper element.
		$html .= '</div>';

		// Return the rendered html.
		return $html;
	}


	/**
	 * The filter_networks() method will generate an array of networks that will
	 * be displayed on this chart. This will remove all networks that do not
	 * support share counts as well as buttons that are listed in the networks
	 * array that aren't actually networks (e.g. print or more buttons).
	 *
	 * The generated array will contain the unique key that corresponds to each
	 * network (e.g. array('total_shares', 'facebook', 'twitter') ).
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function filter_networks() {

		// Use the scope to determine which networks are being requested.
		switch( $this->scope ) {

			// If the scope is total shares, only show the total shares.
			case 'total_shares':
				$networks = array('total_shares');
				break;

			// If the scope is all, we show all networks except total shares.
			case 'all':
				global $swp_social_networks;

				// Loop through all networks, eliminate those that don't support
				// counts or are inactive.
				foreach( $swp_social_networks as $network ) {

					// If the networks is active and supports share counts.
					if( $network->is_active() && 0 !== $network->get_api_link('') ) {
						$networks[] = $network->key;
					}
				}
				break;

			// If it's anything else, we assume they passed in a specific network.
			default:
				$networks[] = $this->scope;
				break;
		}

		if( empty( $networks ) ) {
			$networks = 'none';
			$this->insufficient_networks = true;
		}

		// Store the results in the $networks property.
		$this->networks = $networks;
	}


	/**
	 * The generate_chart_datasets() method will take the results from the
	 * database and convert it into a format that can be used by the Javascript
	 * chart.js functions/classes.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    $this->datasets
	 * @param  void
	 * @return void Generated data is stored in $this->datasets
	 *
	 */
	private function generate_chart_datasets() {
		global $swp_social_networks;

		if( $this->get_status() === false ) {
			return;
		}

		// Loop through each network and create a dataset for it.
		foreach( $this->networks as $network ) {

			// If there is no data for this network, skip it.
			if( false === isset($this->results[0]->{$network} ) ) {
				continue;
			}

			// Get the name of the network from the global Social_Network object.
			$name = 'Total Shares';
			if( isset( $swp_social_networks[$network] ) ) {
				$name = $swp_social_networks[$network]->name;
			}

			$data = array();
			$last_count = 0;

			// Loop through the db results and put them into the $data array.
			foreach( $this->results as $row ) {

				// Use the previous count if one is not available for this date.
				$count = $last_count;
				if( isset( $row->{$network} ) ) {
					$count = $last_count = $row->{$network};
				}


				// If this is a daily/bar chart, we'll need to calculate the daily change.
				if( $this->interval === 'daily' ) {
					if( false === isset( $last_count ) ) {
						$count = 0;
					} else {
						$count = $row->{$network} - $last_count;
						if( $count < 0 ) {
							$count = 0;
						}
					}
					$last_count = $row->{$network};
				}

				// This is the data array that will be passed to the JS.
				$data[] = array(
					't' => date( 'd F Y',  strtotime( $row->date ) ),
					'y' => $count
				);
			}

			/**
			 * We fetched an extra date from the database so that we could use it
			 * to determine the daily increase. Here we'll remove it. On bar charts,
			 * the first day will always be zeroes without doing this.
			 *
			 */
			if( $this->interval === 'daily' || count($data) > $this->range ) {
				array_shift($data);
			}


			/**
			 * The $datasets variable is used by chart JS. This will be set and
			 * then JSON encoded so that it can be easily handed over to the JS.
			 *
			 * @var array
			 *
			 */
			$datasets[] = array(
				'label'                => $name,
				'data'                 => $data,
				'fill'                 => true,
				'borderWidth'          => 3,
				'borderColor'          => $this->get_color($network),
				'backgroundColor'      => $this->get_color($network, ($this->interval == 'daily' ? 1 : 0.05 )),
				'pointBackgroundColor' => $this->get_color($network),
				'pointBorderColor'     => '#ffffff',
				'pointBorderWidth'     => 2,
				'pointRadius'          => 6,
				'pointHitRadius'       => 20
			);
		}

		// Store it in a local property for easy access.
		$this->datasets = $datasets;
	}


	/**
	 * The fetch_from_database() method will fetch our historical data from
	 * the database so that we can populate the charts with that data. This will
	 * also cache the results of database queries in the memory to reduce the
	 * number of calls into the database.
	 *
	 * Process Notes:
	 * 1. It will store the results of each query in the self::$cached_queries
	 *    array. This is a private static property that allows us to reuse data
	 *    without having to keep going back into the database.
	 *
	 * 2. It will aslo store the results of the query in the $this->results
	 *    property. This property is unique to each chart and will be used to
	 *    generate the chart data.
	 *
	 * 3. It will set $this->insufficient_data to false if it does not find at
	 *    least 2 days worth of data. This will result in the chart not being
	 *    rendered.
	 *
	 * 4. It will automatically change the $this->step_size if we have very small
	 *    amounts of data. This means that the X Axis labels (dates across the
	 *    bottom) will appear on every tick instead of only once per week.
	 *
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    global $wpdb documentation:
	 *         https://developer.wordpress.org/reference/classes/wpdb/
	 * @param  void
	 * @return void
	 *
	 */
	private function fetch_from_database() {
		global $wpdb;


		/**
		 * If the chart is showing timeframe buttons, then we'll want to fetch
		 * all of the historical record. Then the data will be delivered to the
		 * frontend and the JS functions can filter it on the fly to create 7-day,
		 * 30-day, etc., views for the user.
		 *
		 * If the chart has those buttons turned off, then only fetch as much
		 * data as is needed to fill the chart to the requested timeframe. Instead
		 * of fetching all record, we'll just fetch 7, 30, etc. There will be no
		 * options for the user to change timeframes on this chart.
		 *
		 * @var string
		 *
		 */
		$time_clause = '';
		if( false === $this->show_timeframes ) {
			$time_clause = " AND date > DATE_SUB(NOW(), INTERVAL $this->range + 1 DAY) ";
		}

		/**
		 * Since we'll be reusing the queried results (see "Understanding the
		 * Query Cache" below), we won't filter out any columns. This will allow
		 * us to reuse the same results for multiple charts if the results
		 * contain the data for those multiple charts.
		 *
		 */
		$query = "SELECT * FROM {$wpdb->prefix}swp_analytics WHERE post_id = $this->post_id $time_clause ORDER BY date ASC";


		/**
		 * Understanding the Query Cache
		 *
		 * Whenever we query the database, we'll store the results in a static
		 * local property of this class. This will allow it to persist across
		 * all instances essentially acting as a cache. As such, we'll first
		 * check to see if another chart has already used this set of data and
		 * pull it from that local property. If it doesn't exist, then we'll get
		 * it from the database and stuff it into that local property.
		 *
		 * @see self::$cached_queries
		 * @see $this->fetch_cached_query()
		 * @see $this->store_cached_query()
		 *
		 */
		if(false === ( $this->results = $this->fetch_cached_query($query) ) ) {
			$this->results = $wpdb->get_results( $query, OBJECT );
			$this->store_cached_query( $query, $this->results );
		}


		/**
		 * We want to make sure that we have at least two points of data before
		 * proceeding. For bar charts, we'll need at least two because we use a
		 * simple math formular based on yesterday's and today's shares to
		 * determine the daily increase. Therefore if we don't have 2 days worth
		 * of data, we don't have enough to even make a single point on the chart.
		 *
		 * @see $this->insufficient_data
		 *
		 */
		if( count( $this->results ) <= 1 ) {
			$this->insufficient_data = true;
		}

		$this->establish_step_size();
	}


	/**
	 * The fetch_cached_query() method will check to see if this query to the
	 * database has already been made. If so, the results of that query will be
	 * stored in our $cached_queries static property so we'll simply pull it out
	 * and reuse it on this chart.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    self::$cached_queries
	 * @param  string $query The sql query to be executed.
	 * @return mixed  The stored results of the query on success; false on failure.
	 *
	 */
	private function fetch_cached_query( $query ) {

		// Generate a unique and repeatable key for this query.
		$query_key = base64_encode($query);

		// If the query has not already been executed and stored, return false.
		if( empty(self::$cached_queries[$query_key]) ) {
			return false;
		}

		// If the query is already run, return the stored results.
		return self::$cached_queries[$query_key];
	}


	/**
	 * The store_cached_query() method will take the results of an executed
	 * query and store those results in our static property so that if any other
	 * charts use the same data, we won't need to query the database a second time.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    self::$cached_queries
	 * @param  string $query   The sql query being executed
	 * @param  array  $results The array of results from the query.
	 * @return void
	 *
	 */
	private function store_cached_query( $query, $results ) {

		// Generate a unique and repeatable key for this query.
		$query_key = base64_encode($query);

		// Store the results in our static property for later use.
		self::$cached_queries[$query_key] = $this->results;
	}


	/**
	 * The generate_chart_js() method will create the script tag that is populated
	 * with some variables that the chart.js functions/classes can use in order
	 * to render out the charts. This is how we pass our data to the chart.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function generate_chart_js() {

		// If we don't have enough data, we won't be rendering a chart.
		if( true === $this->insufficient_data ) {
			return;
		}

		// Compile the html to be output for this chart.
		$this->html .= '<script>var chart_data = chart_data || {}; chart_data.'.$this->chart_key.' = {datasets:' . json_encode($this->datasets) .',stepSize:'.$this->step_size.', offset: '.json_encode($this->offset).', range: '.$this->range.', type: '.json_encode($this->type).'}</script>';
	}



	/**
	 * The generate_canvas() method creates the actual html for the canvas
	 * element. This is what will be sent to the browser for the JS to work with.
	 *
	 * @since  4.2.0 | 24 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function generate_canvas() {

		// If we don't have enough data, we won't be rendering a chart.
		// Instead, we'll generate a warning to the user and then bail out.
		if( false === $this->get_status() ) {
			$this->html .= $this->generate_insufficient_data_warning();
			return;
		}

		// If we do have enough data, then render a canvas element.
		$this->html .= '<div class="sw-grid '.$this->classes.'"><h2 class="'.$this->type.'_chart">'.$this->chart_title.'</h2>'.$this->generate_timeframe_buttons().'<div><canvas class="swp_analytics_chart" data-key="'.$this->chart_key.'" data-type="'.$this->type.'" style="width:100%; height:'.$this->height.'px"></canvas></div></div>';
	}


	/**
	 * The generate_insufficient_data_warning() method will generate a div, a
	 * header and some text that informs the user that the current chart does
	 * not have enough data to be drawn for them. Generally they will see this
	 * for the first 48 hours after we begin collecting analytics data. We
	 * basically need at least 2 days worth of data in order to display these
	 * charts so during those first two days, they will see this notice instead.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function generate_insufficient_data_warning() {
		$this->html .= '<div class="sw-grid '.$this->classes.'"><h2 class="'.$this->type.'_chart">'.$this->chart_title.'</h2><div class="insufficient_data" data-key="'.$this->chart_key.'" data-type="'.$this->type.'" style="width:100%; height:'.$this->height.'px"><h3>Insuffient Data</h3><p>There is currently not enough data to display this chart. Generally speaking, we need at least 2 days worth of data before we begin displaying charts.</p></div></div>';
	}


	/**
	 * The get_color() method contains all of the color codes for the networks.
	 * These will be used to populate the fields that are used by the chart.js
	 * system. In turn, this will color the lines and bars according to the
	 * official color of each network.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  string  $name    The snake_cased name of the network.
	 * @param  float   $opacity A number between 0 and 1 representing the desired opacity.
	 * @return string  The rgba() color code for the desired network.
	 *
	 */
	private function get_color( $name, $opacity = 1 ) {
		$colors = array(
			'buffer'       => 'rgba(50, 59, 67, '.$opacity.')',
			'facebook'     => 'rgba(24, 119, 242, '.$opacity.')',
			'hacker_news'  => 'rgba(216, 86, 35, '.$opacity.')',
			'pinterest'    => 'rgba(230, 0, 35, '.$opacity.')',
			'reddit'       => 'rgba(240, 75, 35, '.$opacity.')',
			'tumblr'       => 'rgba(57, 71, 93, '.$opacity.')',
			'twitter'      => 'rgba(29, 161, 242, '.$opacity.')',
			'vk'           => 'rgba(74, 118, 168, '.$opacity.')',
			'yummly'       => 'rgba(226, 100, 38, '.$opacity.')',
			'total_shares' => 'rgba(238, 70, 79, '.$opacity.')'
		);
		return $colors[$name];
	}


	private function establish_step_size() {

		if( $this->step_size !== 7 ) {
			return;
		}

		if( $this->type === 'bar' ) {
			$this->step_size = 1;
			return;
		}

		// Decrease the step size if there are only a handful of results to show.
		if( count( $this->results ) < 7 ) {
			$this->step_size = 1;
		} elseif ( count( $this->results ) < 10 ) {
			$this->step_size = 2;
		} elseif ( count( $this->results ) < 15 ) {
			$this->step_size = 3;
		} elseif ( count( $this->results ) < 21 ) {
			$this->step_size = 4;
		}
	}


	/**
	 * The get_status() method will check to see if we have enough data in the
	 * database to populate this chart. If we have not yet gathered at least 2
	 * days worth of data, we will not be rendering a chart. This is a public
	 * method that we can use to get a true/false so that we can take action as
	 * needed if a chart is not going to be getting rendered.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    $this->fetch_from_database()
	 * @see    $this->insufficient_data
	 * @param  void
	 * @return boolean True if the chart can be rendered; false if not.
	 *
	 */
	public function get_status() {


		$this->filter_networks();

		/**
		 * This will run the query against the database to see how much data is
		 * there. Since we save all of the queries in a static property, it's
		 * better to call this method and make the actual query. This way the
		 * results of this query will be reused when it goes to compile the
		 * actual chart rather than fetching a new dataset from the database.
		 *
		 * This method will populate the $this->results and, if necessary, it
		 * will modify the $this->insufficient_data and set it to true.
		 *
		 */
		$this->fetch_from_database( $this->post_id );

		// If we have insufficient data...
		if( $this->insufficient_data === true ) {
			return false;
		}

		// If we have insufficient networks...
		if( $this->insufficient_networks === true ) {
			return false;
		}

		// If we have enough data...
		return true;
	}
}
