<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  https://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     4.0.0
 * Author:      Warfare Plugins
 * Author URI:  https://warfareplugins.com
 * Text Domain: social-warfare
 *
 */
defined( 'WPINC' ) || die;


/**
 * Define plugin constants for use throughout the plugin (Version and Directories)
 *
 * @since 2.3.5 | 18 DEC 2017 | Added a constant to activate the registration tab built into core
 *
 */
define( 'SWPP_VERSION', '4.0.0' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'SWPP_SL_PRODUCT_ID', 189418 );  // Pro Utility Product id


/**
 * Ensure that pro loads after core has already been fully loaded.
 *
 */
add_action('plugins_loaded' , 'initialize_social_warfare_pro' , 20 );


/**
 * Initialize the Social Warfare Pro plugin.
 *
 * This function will run on the plugins_loaded hook after Social Warfare Core
 * has already been loading making it's classes available to us here.
 *
 * We will also check for what version core is on, as well as doing checks for
 * existence for essential classes that we need to extend.
 *
 * If pro is missing or outdated, we will make the plugin gracefully fail by
 * simply not loading up any pro classes and providing a notice to the user that
 * they need to install core or update one of the plugins.
 *
 * @since  3.0.0 | 01 MAR 2018 | Created
 * @param  void
 * @return void
 *
 */
function initialize_social_warfare_pro() {


	/**
	 * Social Warfare (Core) is missing.
	 *
	 * If core is not loaded, we leave the plugin active, but we do not proceed
	 * to load up, activate, or instantiate any of pro's features. Instead we
	 * simply activate a dashboard notification to let the user know that they
	 * need to activate core.
	 *
	 */
	if ( !defined( 'SWP_VERSION' ) ) :
		add_action( 'admin_notices', 'swp_needs_core' );
		return;
	endif;


	/**
	 * The Social_Warfare_Addon class does not exist.
	 *
	 * If for some reason core failed to load the Addon class that this plugin
	 * will be extending, we will attempt to load that class here. This is the
	 * class in core that Social_Warfare_Pro will be extending.
	 *
	 */
	$addon_path = SWP_PLUGIN_DIR . '/lib/Social_Warfare_Addon.php';
	if ( !class_exists( 'Social_Warfare_Addon' ) && file_exists( $addon_path ) ) :
		require_once( $addon_path );
	endif;


	/**
	 * Version Compatibility
	 *
	 * As of 3.3.0 (3.2.90 is the beta), we are making the plugin backwards
	 * compatible back to this version. Addons prior to 3.3.0 will still need
	 * to be an exact match, everything on or after this version will simply
	 * run using existence checks. This will allow that the bulk of the plugin
	 * continues to run smoothly. Only features that are missing their
	 * dependencies will gracefully deactivate until the other plugin is updated.
	 *
	 * If core is available, and it's on 3.3.0 or higher, we'll go ahead and
	 * load and instantiate the Social Warfare Pro class to fire up the plugin.
	 *
	 * Each subsequent version will be made to be compatable with all versions
	 * within the same major version subset. For example:
	 *
	 * 3.3.0 with 3.4.1 = Compatible
	 * 4.1.0 with 4.0.0 = Compatible
	 * 4.1.0 with 3.4.1 = Not Compatible
	 *
	 * Failure to find a compatible version of the core plugin will cause the
	 * pro plugin to gracefully remain unloaded and will alert the user to the
	 * mismatch via a WordPress admin notification.
	 *
	 */
	if( class_exists( 'Social_Warfare_Addon' ) && version_compare( SWP_VERSION , '4.0.0' ) >= 0 ) {
		$pro_class_path = SWPP_PLUGIN_DIR . '/lib/Social_Warfare_Pro.php';
		if( file_exists( $pro_class_path ) ) {
			require_once $pro_class_path;
			new Social_Warfare_Pro();
		}


	/**
	 * If core is simply too far out of date, we will create a dashboard notice
	 * to inform the user that they need to update core to the appropriate
	 * version in order to get access to pro.
	 *
	 */
	} else {
		add_filter( 'swp_admin_notices', 'swp_pro_update_notification' );
	}


	/**
	 * The plugin update checker
	 *
	 * This is the class for the plugin update checker. It is not dependent on
	 * a certain version of core existing. Instead, it simply checks if the class
	 * exists, and if so, it uses it to check for updates from GitHub.
	 *
	 */
	if ( class_exists( 'Puc_v4_Factory') ) :
		$update_checker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/warfare-plugins/social-warfare-pro/',
			__FILE__,
			'social-warfare-pro'
		);
		$update_checker->getVcsApi()->enableReleaseAssets();
	endif;

}


/**
 * Notificiation that Social Warfare (core) is needed.
 *
 * This is the dashboard notification that will alert users that in order to
 * use the features of this plugin, they will need to have the core plugin
 * installed and activated.
 *
 * @since  2.2.0 | Unknown | Created
 * @param  void
 * @return void
 *
 */
if ( !function_exists( 'swp_needs_core' ) ) :
	function swp_needs_core() {
		echo '<div class="update-nag notice is-dismissable"><p><b>Important:</b> You currently have Social Warfare - Pro installed without our Core plugin installed.<br/>Please download the free core version of our plugin from the <a href="https://wordpress.org/plugins/social-warfare/" target="_blank">WordPress plugins repository</a>.</p></div>';
	}
endif;


/**
 * Notify users that the versions of Social Warfare and Social Warfare Pro are
 * are currently on incompatible versions with each other.
 *
 * @since  2.2.0 | Unknown | Created
 * @param  array $notices An array of notices to which we add our notice.
 * @return void
 *
 */
 function swp_pro_update_notification( $notices = array() ) {
	 if (is_string( $notices ) ) {
		 $notices = array();
	 }

	 $notices[] = array(
		 'key'   => 'update_notice_pro_' . SWPP_VERSION, // database key unique to this version.
		 'message'   => 'Looks like your copy of Social Warfare - Pro isn\'t up to date with Core. While you can still use both of these plugins, we highly recommend you keep both Core and Pro up-to-date for the best of what we have to offer.',
		 'ctas'  => array(
			 array(
				 'action'    => 'Remind me in a week.',
				 'timeframe' => 7 // dismiss for one week.
			 ),
			 array(
				 'action'    => 'Thanks for letting me know.',
				 'timeframe' => 0 // permadismiss for this version.
			 )
		 )
	 );

	 return $notices;
}
