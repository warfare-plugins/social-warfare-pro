<?php

class SWP_Pro_Analytics_Widget {

	public function __construct() {
		add_action('wp_dashboard_setup', array( $this, 'setup_dashboard' ) );




	}

	public function setup_dashboard() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget('swp_analytics_widget', 'Analytics by Social Warfare', array( $this, 'setup_widget' ) );
	}

	public function setup_widget() {
		$html = '';

		$chart = new SWP_Pro_Analytics_Chart();
		$html .= $chart->set_classes('sw-col-940 sw-fit')
					  ->set_range(7)
					  ->set_step_size(1)
					  ->set_height(200)
					  ->set_scope('all')
					  ->set_interval('daily')
					  ->render_html();

  		$chart = new SWP_Pro_Analytics_Chart();
  		$html .= $chart->set_classes('sw-col-940 sw-fit')
  					  ->set_range(7)
					  ->set_step_size(1)
  					  ->set_height(200)
  					  ->set_scope('all')
  					  ->render_html();


		echo $html;
	}
}
