<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  http://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     2.3.5
 * Author:      Warfare Plugins
 * Author URI:  http://warfareplugins.com
 * Text Domain: social-warfare
 */

defined( 'WPINC' ) || die;

/**
 * Define plugin constants for use throughout the plugin (Version and Directories)
 * @since 2.3.5 | 18 DEC 2017 | Added a constant to activate the registration tab built into core
 *
 */
define( 'SWPP_VERSION', '2.3.5' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'SWPP_ITEM_ID', 63157 );

// Activate the registration tab constant so that core loads the registration tab
if( !defined( 'SWP_ACTIVATE_REGISTRATION_TAB' ) ) {
    define( 'SWP_ACTIVATE_REGISTRATION_TAB' , true );
}



add_action('plugins_loaded' , 'initialize_social_warfare_pro' , 10 );
function initialize_social_warfare_pro() {
	require_once SWPP_PLUGIN_DIR . '/functions/Social_Warfare_Pro.php';
	new Social_Warfare_Pro();
}
