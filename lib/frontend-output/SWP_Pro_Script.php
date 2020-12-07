<?php

/**
 * Register and enqueue plugin scripts and styles.
 *
 * @package   SocialWarfarePro\frontend-output
 * @copyright Copyright (c) 2020, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     4.1.0
 *
 */
class SWP_Pro_Script extends SWP_Script {


	/**
	 * The contructor will add the hooks needs to enqueue the Pro-only scripts.
	 *
	 * @since  4.1.0 | 05 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {
		$this->add_hooks();
	}

	public function add_hooks() {

		// Queue up the Social Warfare scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}


	public function enqueue_admin_scripts( $screen ) {
		$suffix = SWP_Script::get_suffix();

		wp_enqueue_style(
			'social_warfare_admin_pro',
			SWPP_PLUGIN_URL . "/assets/css/admin_pro{$suffix}.css",
			array(),
			SWPP_VERSION
		);

		if( in_array( $screen, array('index.php', 'social-warfare_page_social-warfare-analytics' ) ) ) {
			wp_enqueue_script(
				'chartjs',
				SWPP_PLUGIN_URL . "/assets/js/chart{$suffix}.js",
				array( 'jquery', 'social_warfare_script' ),
				SWPP_VERSION
			);

			wp_enqueue_script(
				'swp_analytics',
				SWPP_PLUGIN_URL . "/assets/js/SocialAnalytics{$suffix}.js",
				array( 'jquery', 'chartjs', 'social_warfare_script' ),
				SWPP_VERSION
			);
		}

		if( $screen == 'post-new.php' || $screen == 'post.php' ) {
			if( function_exists('has_blocks') && has_blocks() === true ) {
				wp_enqueue_script(
					'swp_post_meta',
					SWPP_PLUGIN_URL . "/assets/js/SocialOptimizer{$suffix}.js",
					array( 'jquery', 'social_warfare_script' ),
					SWPP_VERSION
				);
			}
		}
	}
}
