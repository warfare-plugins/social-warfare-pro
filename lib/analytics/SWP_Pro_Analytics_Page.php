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

		$chart = new SWP_Pro_Analytics_Chart();
		$html .= $chart->set_classes('sw-col-460')
				       ->render_html();

		$chart = new SWP_Pro_Analytics_Chart();
		$html .= $chart->set_classes('sw-col-460 sw-fit')
		               ->set_interval('daily')
					   ->render_html();

		$html .= '<div class="sw-clearfix"></div>';

		$chart = new SWP_Pro_Analytics_Chart();
		$html .= $chart->set_classes('sw-col-460')
		               ->set_scope('all')
					   ->render_html();

		$chart = new SWP_Pro_Analytics_Chart();
		$html .= $chart->set_classes('sw-col-460 sw-fit')
		               ->set_scope('all')
					   ->set_interval('daily')
					   ->render_html();

		$html .= '<div class="sw-clearfix"></div>';
		$html .= '</div>';

		echo $html;
	}

}
