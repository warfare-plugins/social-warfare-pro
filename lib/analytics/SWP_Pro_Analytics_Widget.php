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
		echo '<canvas id="swp_analytics_chart"></canvas>';
	}
}
