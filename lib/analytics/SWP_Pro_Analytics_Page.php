<?php

/**
 * The SWP_Pro_Analytics_Page class will control the entire layout and build of
 * the analytics page in the admin area. It will generate charts, lists, and
 * practical guides to help the user get the absolute most out of the plugin as
 * possible.
 *
 * @since 4.2.0 | 22 AUG 2020 | Created
 *
 */
class SWP_Pro_Analytics_Page {


	/**
	 * The constrcutor will fire everything up by simply adding our
	 * generate_admin_page() to the admin_menu() hook.
	 *
	 * @since  4.2.0 | 22 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'generate_admin_page') );
	}


	/**
	 * The generate_admin_page() will make use of the add_submenu_page() function
	 * to add this underneath of the main Social Warfare settings in the admin
	 * sidebar menu. It will also register our render_html() to run in order to
	 * render out the actual html of the page. We'll also register in some CSS
	 * styles for this page.
	 *
	 * @since  4.2.0 | 22 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function generate_admin_page() {

		// Declare the menu link
		$swp_analytics_menu = add_submenu_page(
			'social-warfare',
			'Social Analytics by Social Warfare',
			'Social Analytics',
			'manage_options',
			'social-warfare-analytics',
			array( $this, 'render_html'),
			5
		);

		// Queue up our method for registering CSS stylesheets.
		add_action( 'admin_print_styles-' . $swp_analytics_menu, array( $this, 'admin_css' ) );
	}


	/**
	* Enqueue the Settings Page CSS & Javascript
	*
	* @since  4.2.0 | 22 AUG 2020 | Created
	* @param  void
	* @return void
	*
	*/
	public function admin_css() {

		// The .min.css or .css suffix.
		$suffix     = SWP_Script::get_suffix();

		// Enqueue the admin-options-page.css file so we can just reuse that file.
		wp_enqueue_style(
			'swp_admin_options_css',
			SWP_PLUGIN_URL . "/assets/css/admin-options-page{$suffix}.css",
			array(),
			SWP_VERSION
		);
	}


	/**
	 * The render_html() method will generate and echo out all of the html that
	 * will appear on this page.
	 *
	 * @since  4.2.0 | 22 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function render_html() {
		$html = '<div class="sw-admin-wrapper">';
		$html .= $this->generate_chart( 'total_shares', 'sw-col-460');
		$html .= $this->generate_chart( 'total_shares', 'sw-col-460 sw-fit', 0, 'daily');
		$html .= '<div class="sw-clearfix"></div>';
		$html .= $this->generate_chart( 'all', 'sw-col-460');
		$html .= $this->generate_chart( 'all', 'sw-col-460 sw-fit', 0, 'daily');
		$html .= '<div class="sw-clearfix"></div>';
		$html .= '</div>';

		echo $html;
	}


	/**
	 * The generate_chart() method is the one-stop shop for all of our charts.
	 * Given just a few parameters, this method can easily generate any and all
	 * charts that we'll need for a our analytics suite.
	 *
	 * @since  4.2.0 | 22 AUG 2020 | Created
	 * @param  string  $networks   'all','total_shares', or an individual network
	 *                             key such as 'facebook'.
	 * @param  string  $classes    CSS grid classes for the parent container.
	 * @param  integer $post_id    The id of the post. 0 for sitewide stats.
	 * @param  string  $count_type 'total' or 'daily' for daily change.
	 * @return string  The generated html.
	 *
	 */
	public function generate_chart( $networks, $classes, $post_id = 0, $count_type = 'total' ) {

		// Create a unique key to tie the canvas to the JS variable.
		$chart_key = 'chart_' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 5 );

		$this->generate_chart_title( $post_id, $networks, $count_type );

		// Generate the html wrapper and canvas element.
		$html      = $this->generate_canvas( $chart_key, $classes, $count_type );

		// Create an array of networks to display on this chart.
		$networks  = $this->filter_networks( $networks );

		// Generate the datasets that will be used for the chart's javascript.
		$datasets  = $this->generate_chart_datasets( $post_id, $networks, $count_type );

		// Generate the charts javascript.
		$html     .= $this->generate_chart_js($chart_key, $datasets);

		return $html;
	}


	private function generate_chart_title( $post_id, $networks, $count_type ) {
		global $swp_social_networks;
		unset($this->chart_title);

		$prefix = $start = $middle = $end = '';

		switch( $post_id ) {
			case 0:
				$start = 'Sitewide ';
				break;
		}

		switch( $networks ) {
			case 'all':
				$middle = 'Network Shares ';
				break;
			case 'total_shares':
				$middle = 'Total Shares ';
				break;
			default:
				$middle = $swp_social_networks[$networks]->name . ' ';
				break;
		}

		switch( $count_type ) {
			case 'daily':
				$prefix = 'Daily ';
				break;
			case 'total':
				$end = 'Over Time ';
				break;
		}

		$this->chart_title = $prefix . $start . $middle . $end;
	}

	/**
	 * The generate_chart_datasets() method will generate an array of data that
	 * corresponds to the 'datasets' property of the chart.js object that will
	 * be created to render the chart. This will contain everything needed to
	 * tell the chart exactly how to render.
	 *
	 * @since  4.2.0 | 22 AUG 2020 | Created
	 * @param  integer $post_id           The post id.
	 * @param  array   $filtered_networks An array of social networks.
	 * @param  string  $count_type        'total' or 'daily'
	 * @return array   $datasets          The datasets array.
	 *
	 */
	private function generate_chart_datasets( $post_id, $filtered_networks, $count_type ) {
		global $swp_social_networks;

		// Fetch the data from the database.
		$results  = $this->fetch_from_database( $post_id );

		// Loop through each network and create a dataset for it.
		foreach( $filtered_networks as $network ) {

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
				if( $count_type === 'daily' ) {
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
				'label'       => $name,
				'data'        => $data,
				'fill'        => false,
				'borderColor' => $this->get_color($network),
				'backgroundColor' => $this->get_color($network),
				'lineTension' => 0.3
			);
		}
		return $datasets;
	}

	private function filter_networks( $networks ) {
		switch( $networks ) {
			case 'total_shares':
				$new_networks = array('total_shares');
				break;
			case 'all':
				global $swp_social_networks;
				foreach( $swp_social_networks as $network ) {
					if( in_array($network->key, array('more') ) ) {
						continue;
					}

					if( $network->is_active() ) {
						$new_networks[] = $network->key;
					}
				}
				break;
			default:
				$new_networks[] = $networks;
				break;
		}
		return $new_networks;
	}

	private function generate_canvas( $chart_key, $classes, $count_type ) {

		switch($count_type) {
			case 'daily':
				$chart_type = 'bar';
				break;
			default:
				$chart_type = 'line';
				break;
		}

		return '<div class="sw-grid '.$classes.'"><h2>'.$this->chart_title.'</h2><div><canvas class="swp_analytics_chart" data-key="'.$chart_key.'" data-type="'.$chart_type.'" style="width:100%; height:400px"></canvas></div></div>';
	}

	private function fetch_from_database( $post_id ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}swp_analytics WHERE post_id = $post_id AND date > CURDATE() - INTERVAL 30 DAY ORDER BY date ASC", OBJECT );
	}

	private function generate_chart_js( $chart_key, $data ) {
		return '<script>var chart_data = chart_data || {}; chart_data.'.$chart_key.' = ' . json_encode($data) .'</script>';
	}

	private function get_color( $name ) {
		$colors = array(
			'buffer'       => '#323b43',
			'facebook'     => '#1877f2',
			'hacker_news'  => '#d85623',
			'pinterest'    => '#e60023',
			'reddit'       => '#f04b23',
			'tumblr'       => '#39475d',
			'twitter'      => '#1da1f2',
			'vk'           => '#4a76a8',
			'yummly'       => '#e26426',
			'total_shares' => '#ee464f'
		);
		return $colors[$name];
	}

}
