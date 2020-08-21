<?php

class SWP_Pro_analytics {

	public function __construct() {

		add_filter( 'plugins_loaded' , array( $this , 'instantiate_analytics' ) , 999 );
		add_action( 'save_post', array( $this, 'record_social_optimizer_score' ), 10, 1 );
		add_action( 'swp_cache_rebuild', array( $this, 'record_social_optimizer_score' ), 10, 1 );
	}

	public function instantiate_analytics() {
		new SWP_Pro_Analytics_Columns();
		new SWP_Pro_Analytics_Page();
		new SWP_Pro_Analytics_Database();
		new SWP_Pro_Analytics_Widget();
	}

	public function record_social_optimizer_score( $post_id ) {
		new SWP_Pro_Social_Optimizer( $post_id );
	}
}
