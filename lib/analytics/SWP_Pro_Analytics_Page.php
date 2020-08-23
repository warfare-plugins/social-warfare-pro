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


	private $tabs = array(
		array(
			'key' => 'trends',
			'name' => 'Sitewide Trends'
		),
		array(
			'key' => 'posts',
			'name' => 'Posts Analysis'
		),
	);


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
		add_action( 'admin_print_styles-' . $swp_analytics_menu, array( $this, 'enqueue_assets' ) );
	}


	/**
	* Enqueue the Settings Page CSS & Javascript
	*
	* @since  4.2.0 | 22 AUG 2020 | Created
	* @param  void
	* @return void
	*
	*/
	public function enqueue_assets() {

		// The .min.css or .css suffix.
		$suffix     = SWP_Script::get_suffix();

		// Enqueue the admin-options-page.css file so we can just reuse that file.
		wp_enqueue_style(
			'swp_admin_options_css',
			SWP_PLUGIN_URL . "/assets/css/admin-options-page{$suffix}.css",
			array(),
			SWP_VERSION
		);

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-effects-core' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-position' );
		wp_enqueue_media();
		wp_enqueue_script(
			'swp_admin_options_js',
			SWP_PLUGIN_URL . "/assets/js/admin-options-page{$suffix}.js",
			array( 'jquery', 'social_warfare_script' ),
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
		$this->html = '<div class="sw-admin-wrapper">';

		$this->html .= $this->generate_header_menu();

		foreach( $this->tabs as $tab ) {
			$this->html .= '<div id="swp_'.$tab['key'].'" class="sw-admin-tab sw-grid sw-col-940">';
			$method_name = 'generate_' . $tab['key'] . '_tab';
			$this->$method_name();
			$this->html .= '</div>';
		}


		echo $this->html;
	}

	private function generate_posts_tab() {
		$this->html .= 'Hello World';
	}

	private function generate_trends_tab() {
		$chart = new SWP_Pro_Analytics_Chart();
		$this->html .= $chart->set_classes('sw-col-460')
					   ->render_html();

		$chart = new SWP_Pro_Analytics_Chart();
		$this->html .= $chart->set_classes('sw-col-460 sw-fit')
					   ->set_interval('daily')
					   ->render_html();

		$this->html .= '<div class="sw-clearfix"></div>';

		$chart = new SWP_Pro_Analytics_Chart();
		$this->html .= $chart->set_classes('sw-col-460')
					   ->set_scope('all')
					   ->render_html();

		$chart = new SWP_Pro_Analytics_Chart();
		$this->html .= $chart->set_classes('sw-col-460 sw-fit')
					   ->set_scope('all')
					   ->set_interval('daily')
					   ->render_html();

		$this->html .= '<div class="sw-clearfix"></div>';
	}

	private function generate_header_menu() {

		$this->html .= '<div class="sw-header-wrapper"><div class="sw-grid sw-col-940 sw-top-menu"><div class="sw-grid sw-col-700"><img class="sw-header-logo-pro" src="/wp-content/plugins/social-warfare/assets/images/admin-options-page/social-warfare-pro-light.png"><ul class="sw-header-menu">';

		$i = 0;
		foreach( $this->tabs as $tab ) {
			$this->html .= '<li class="'.($i++ === 0 ? 'sw-active-tab' : '' ).'"><a class="sw-tab-selector" href="#" data-link="swp_'.$tab['key'].'"><span>'.$tab['name'].'</span></a></li>';
		}

		$this->html.= '</ul></div><div class="sw-grid sw-col-220 sw-fit"></div><div class="sw-clearfix"></div></div></div>';
	}

}
