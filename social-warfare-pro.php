<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  http://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     3.0.0
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
define( 'SWPP_VERSION', '3.0.0' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );

add_action('plugins_loaded' , 'initialize_social_warfare_pro' , 20 );

function initialize_social_warfare_pro() {
	if( defined('SWP_VERSION') && SWP_VERSION == SWPP_VERSION ):
		require_once SWPP_PLUGIN_DIR . '/functions/Social_Warfare_Pro.php';
		new Social_Warfare_Pro();
    else:
        add_action( 'admin_notices', 'mismatch_notification' );
	endif;
}

/**
 * Notify users that the versions of Social Warfare and Social Warfare Pro are mismatched.
 *
 *
 * @since  2.2.0
 * @param  none
 * @return void
 *
 */
 function mismatch_notification() {
    echo '<div class="update-nag notice is-dismissable"><p>' . __( '<b>Important:</b> You are currently running Social Warfare v'.SWP_VERSION.' and Social Warfare - Pro v'.SWPP_VERSION.'. In order to avoid conflicts, these two version need to match in order to activate all of the plugins features. Please update the appropriate plugin so that both Social Warfare and Social Warfare - Pro are on the same version. For more information about this, <a href="https://warfareplugins.com/support/updating-social-warfare-social-warfare-pro/">please read this</a>. ', 'social-warfare' ) . '</p></div>';
}
