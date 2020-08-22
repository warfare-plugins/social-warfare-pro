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
		$wp_scripts = wp_scripts();

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
		$html .= $this->generate_total_shares_chart('sitewide_total_shares', 'total_shares', 0, 'sw-col-460');
		$html .= $this->generate_total_shares_chart('sitewide_network_shares', 'all', 0, 'sw-col-460 sw-fit');
		$html .= '<div class="sw-clearfix"></div>';
		$html .= '</div>';

		echo $html;
	}

	private function generate_total_shares_chart( $chart_key, $networks, $post_id = 0, $classes ) {

		$html     = $this->generate_canvas( $chart_key, $classes );
		$networks = $this->filter_networks( $networks );
		$datasets = $this->generate_chart_datasets( $post_id, $networks );
		$html    .= $this->generate_chart_js($chart_key, $datasets);

		return $html;
	}

	private function generate_chart_datasets( $post_id, $filtered_networks ) {
		global $swp_social_networks;

		$results  = $this->fetch_from_database( $post_id );
		foreach( $filtered_networks as $network ) {
			if( false === isset($results[0]->{$network} ) ) {
				continue;
			}

			$name = 'Total Shares';
			if( isset( $swp_social_networks[$network] ) ) {
				$name = $swp_social_networks[$network]->name;
			}

			$data = array();
			foreach( $results as $row ) {
				$data[] = array(
					't' => $row->date,
					'y' => $row->{$network}
				);
			}

			$datasets[] = array(
				'label'       => $name,
				'data'        => $data,
				'fill'        => false,
				'borderColor' => $this->get_color($network),
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
		}
		return $new_networks;
	}

	private function generate_canvas( $chart_key, $classes ) {

		return '<div class="sw-grid '.$classes.'"><canvas class="swp_analytics_chart" data-key="'.$chart_key.'" style="width:100%; height:400px"></canvas></div>';
	}

	private function fetch_from_database( $post_id ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}swp_analytics WHERE post_id = $post_id ORDER BY date ASC", OBJECT );
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
