<?php

class SWP_Pro_analytics {

	public function __construct() {

		add_filter( 'plugins_loaded' , array( $this , 'instantiate_analytics' ) , 999 );

	}

	public function instantiate_analytics() {
		new SWP_Pro_Analytics_Page();
		new SWP_Pro_Analytics_Database();
		new SWP_Pro_Analytics_Widget();
	}

}
