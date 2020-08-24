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
	 * The Post ID. Use 0 to get sitewide totals.
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
	 * @var string
	 *
	 */
	private $interval = 'total';


	/**
	 * The CSS classes. The css classes will be appended to the parent container
	 * and can therefore be used to control the layout, spacing, etc.
	 *
	 * @var string
	 *
	 */
	private $classes  = '';
	private $range    = 30;
	private $height   = 400;
	private $step_size = 7;

	private $networks = array();
	private $html = '';

	public function __construct( $args = array() ) {

		$this->chart_key = 'chart_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5 );

		foreach( $args as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	public function __call( $name, $value ) {

		if( false === strpos( $name, 'set_' ) ) {
			return;
		}

		$name = str_replace( 'set_', '', $name );

		if( isset( $this->{$name} ) ) {
			$this->{$name} = $value[0];
		}
		return $this;
	}

	public function render_html() {
		$this->generate_chart_title();
		$this->generate_canvas();
		$this->filter_networks();
		$this->generate_chart_datasets();
		$this->generate_chart_js();
		return $this->html;
	}

	private function generate_chart_title() {
		global $swp_social_networks;

		$prefix = $start = $middle = $end = '';

		switch( $this->post_id ) {
			case 0:
				$start = 'Sitewide ';
				break;
		}

		switch( $this->scope ) {
			case 'all':
				$middle = 'Network Shares ';
				break;
			case 'total_shares':
				$middle = 'Total Shares ';
				break;
			default:
				$middle = $swp_social_networks[$this->scope]->name . ' ';
				break;
		}

		switch( $this->interval ) {
			case 'daily':
				$prefix = 'Daily ';
				break;
			case 'total':
				$end = 'Over Time ';
				break;
		}

		$this->chart_title = $prefix . $start . $middle . $end;
	}

	private function generate_canvas() {

		switch($this->interval) {
			case 'daily':
				$chart_type = 'bar';
				break;
			default:
				$chart_type = 'line';
				break;
		}

		$this->html .= '<div class="sw-grid '.$this->classes.'"><h2 class="'.$chart_type.'_chart">'.$this->chart_title.'</h2><div><canvas class="swp_analytics_chart" data-key="'.$this->chart_key.'" data-type="'.$chart_type.'" style="width:100%; height:'.$this->height.'px"></canvas></div></div>';

	}

	private function filter_networks() {
		switch( $this->scope ) {
			case 'total_shares':
				$networks = array('total_shares');
				break;
			case 'all':
				global $swp_social_networks;
				foreach( $swp_social_networks as $network ) {
					if( in_array($network->key, array('more') ) ) {
						continue;
					}

					if( $network->is_active() ) {
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
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}swp_analytics WHERE post_id = $this->post_id AND date > CURDATE() - INTERVAL $this->range DAY ORDER BY date ASC", OBJECT );
		return $results;
	}

	private function generate_chart_js() {
		$this->html .= '<script>var chart_data = chart_data || {}; chart_data.'.$this->chart_key.' = {datasets:' . json_encode($this->datasets) .',stepSize:'.$this->step_size.'}</script>';
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
