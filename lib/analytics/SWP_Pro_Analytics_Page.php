<?php

class SWP_Pro_Analytics_Page {


	public function __construct() {
		add_action( 'admin_menu', array( $this, 'generate_admin_page') );
	}

	public function generate_admin_page() {

		// Declare the menu link
		$swp_menu = add_submenu_page(
			'social-warfare',
			'Social Analytics by Social Warfare',
			'Social Analytics',
			'manage_options',
			'social-warfare-analytics',
			array( $this, 'render_page_html'),
			5
		);

		// Declare the menu link
		$swp_menu = add_submenu_page(
			'social-warfare',
			'Scheduled Posts by Social Warfare',
			'Scheduled Posts',
			'manage_options',
			'social-warfare-analytics',
			array( $this, 'render_page_html'),
			5
		);
	}

	public function render_page_html() {
		echo 'hello world';
		echo '<div class="analytics_container" style="width:47.5%;height:400px;margin-right:5%;"><canvas id="analytics_chart" style="width:50%; height:400px"></canvas></div>';
	}
}
