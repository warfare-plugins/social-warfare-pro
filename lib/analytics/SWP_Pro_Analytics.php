<?php

class SWP_Pro_analytics {

	public function __construct() {

		add_filter( 'plugins_loaded' , array( $this , 'instantiate_analytics' ) , 999 );
		add_filter( 'the_content', array( $this, 'load_social_optimizer' ) );
	}

	public function instantiate_analytics() {
		new SWP_Pro_Analytics_Page();
		new SWP_Pro_Analytics_Database();
		new SWP_Pro_Analytics_Widget();
	}

	public function load_social_optimizer( $the_content ) {
		$Social_Optimizer = new SWP_Pro_Social_Optimizer( get_the_id() );
		return $the_content;
	}

}
