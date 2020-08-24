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
	 * @since  4.2.0 | 24 AUG 2020 | Created
	 * @param  void
	 * @return string The string of rendered html.
	 *
	 */
	public function render_html() {
		$this->establish_chart_type();
		$this->generate_chart_title();
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
	private function generate_chart_title() {
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

	private function generate_canvas() {
		$this->html .= '<div class="sw-grid '.$this->classes.'"><h2 class="'.$this->type.'_chart">'.$this->chart_title.'</h2><div><canvas class="swp_analytics_chart" data-key="'.$this->chart_key.'" data-type="'.$this->type.'" style="width:100%; height:'.$this->height.'px"></canvas></div></div>';

	}

	private function filter_networks() {
		switch( $this->scope ) {
			case 'total_shares':
				$networks = array('total_shares');
				break;
			case 'all':
				global $swp_social_networks;
				foreach( $swp_social_networks as $network ) {
					if( in_array($network->key, array('more','email','print') ) ) {
						continue;
					}

					if( $network->is_active() && 0 !== $network->get_api_link('') ) {
						$networks[] = $network->key;
					}
				}
				break;
			default:
				$networks[] = $this->scope;
				break;
		}
		$this->networks = $networks;
	}

	private function generate_chart_datasets() {
		global $swp_social_networks;

		// Fetch the data from the database.
		$results  = $this->fetch_from_database( $this->post_id );

		// Loop through each network and create a dataset for it.
		foreach( $this->networks as $network ) {

			// If there is no data for this network, skip it.
			if( false === isset($results[0]->{$network} ) ) {
				continue;
			}

			// Get the name of the network from the global Social_Network object.
			$name = 'Total Shares';
			if( isset( $swp_social_networks[$network] ) ) {
				$name = $swp_social_networks[$network]->name;
			}

			$data = array();
			unset($last_count);
			foreach( $results as $row ) {
				$count = $row->{$network};
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

				$data[] = array(
					't' => $row->date,
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

			$datasets[] = array(
				'label'                => $name,
				'data'                 => $data,
				'fill'                 => false,
				'borderWidth'          => 3,
				'borderColor'          => $this->get_color($network),
				'backgroundColor'      => $this->get_color($network, ($this->interval == 'daily' ? 1 : 0.7 )),
				'pointBackgroundColor' => $this->get_color($network),
				// 'lineTension'          => 0.4,
				'pointBorderWidth'     => 0,
				'pointHitRadius'       => 3
			);
		}
		$this->datasets = $datasets;
	}

	private function fetch_from_database() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}swp_analytics WHERE post_id = $this->post_id AND date > CURDATE() - INTERVAL $this->range + 1 DAY ORDER BY date ASC", OBJECT );

		if( count( $results ) < 2 ) {
			$this->warning = 'Please allow some time for the plugin to collect analytics data. We want at least 2 days worth of data to begin displaying charts.';
		}

		if( count( $results ) < 7 && $this->step_size == 7 ) {
			$this->step_size = 1;
		}

		return $results;
	}

	private function generate_chart_js() {
		$this->html .= '<script>var chart_data = chart_data || {}; chart_data.'.$this->chart_key.' = {datasets:' . json_encode($this->datasets) .',stepSize:'.$this->step_size.', offset: '.json_encode($this->offset).'}</script>';
	}

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

}
