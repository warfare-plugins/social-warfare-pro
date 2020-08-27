<?php

/**
 * The SWP_Pro_Analytics_Widget class will interface with WordPress to register
 * our widget. It will then control the look and feel of the widget by generating
 * the necessary html for it.
 *
 * @since  4.2.0 | 25 AUG 2020 | Created
 *
 */
class SWP_Pro_Analytics_Widget {


	/**
	 * The constructor's only job is to hook into the wp_dashboard_setup hook
	 * so that we can slip in there and generate our widget.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    The 'wp_dashboard_setup' Documentation:
	 *         https://developer.wordpress.org/reference/functions/wp_dashboard_setup/
	 *         https://developer.wordpress.org/reference/hooks/wp_dashboard_setup/
	 *         https://codex.wordpress.org/Plugin_API/Action_Reference/wp_dashboard_setup
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {
		add_action('wp_dashboard_setup', array( $this, 'setup_dashboard' ) );
	}


	/**
	 * The setup_dashboard() method is being fired during the wp_dashboard_setup
	 * hook. This will in turn call the wp_add_dashbaord_widget() function which
	 * will add our widget to the dashboard.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @see    The 'wp_add_dashbaord_widget' Documentation:
	 *         https://developer.wordpress.org/reference/functions/wp_add_dashboard_widget/
	 * @param  void
	 * @return void
	 *
	 */
	public function setup_dashboard() {


		/**
		 * Since the charts re-use any data they get from the database, it won't
		 * use any extra calls to the database to instantiate the chart now,
		 * check if we have enough data to render a widget. After we know that
		 * we can bail out and refuse to create a widget at all, or proceed and
		 * create the chart object all over again...all without hitting the
		 * database again.
		 *
		 * The final method, get_status, will return a true or false letting us
		 * know if the chart has enough data to be rendered. If it has not yet
		 * collected at least 2 days worth of data, it will not be rendered.
		 *
		 */
		$chart = new SWP_Pro_Analytics_Chart();
 		$status = $chart->set_classes('sw-col-940 sw-fit')
 					   ->set_range(7)
 					   ->set_step_size(1)
 					   ->set_height(200)
 					   ->set_scope('all')
 					   ->set_interval('daily')
 					   ->set_show_timeframes(false)
 					   ->get_status();

		// Bail out if we don't have enough data.
		if( false === $status ) {
			return;
		}


		/**
		 * The wp_add_dashbaord_widget() function will add our widget to the page.
		 *
		 * It requires 3 parameters:
		 * 1. widget_id   (string) (Required) Widget ID (used in the 'id' attribute for the widget).
		 * 2. widget_name (string) (Required) Title of the widget.
		 * 3. callback    (callable) (Required) Function that fills the widget
		 *                with the desired content. The function should echo
		 *                its output.
		 *
		 */
		wp_add_dashboard_widget('swp_analytics_widget', 'Analytics by Social Warfare', array( $this, 'render_html' ) );
	}


	/**
	 * The render_html() method will generate the html for this widget. It is
	 * registered above by using the wp_add_dashbaord_widget() function. This
	 * method will echo the html directly to the screen when finished.
	 *
	 * @since  4.2.0 | 25 AUG 2020 | Created
	 * @param  void
	 * @return void Generated html is echoed directly to the screen.
	 *
	 */
	public function render_html() {
		$html = '';

		$chart = new SWP_Pro_Analytics_Chart();
		$html .= $chart->set_classes('sw-col-940 sw-fit')
					   ->set_range(7)
					   ->set_step_size(1)
					   ->set_height(200)
					   ->set_scope('all')
					   ->set_interval('daily')
					   ->set_show_timeframes(false)
					   ->render_html();

  		$chart = new SWP_Pro_Analytics_Chart();
  		$html .= $chart->set_classes('sw-col-940 sw-fit')
  					   ->set_range(7)
					   ->set_step_size(1)
  					   ->set_height(200)
  					   ->set_scope('all')
					   ->set_show_timeframes(false)
  					   ->render_html();

		echo $html;
	}
}
