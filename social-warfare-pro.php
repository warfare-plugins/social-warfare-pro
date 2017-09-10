<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  http://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     2.3.2
 * Author:      Warfare Plugins
 * Author URI:  http://warfareplugins.com
 * Text Domain: social-warfare
 */

defined( 'WPINC' ) || die;

/**
 * Define plugin constants for use throughout the plugin (Version and Directories)
 *
 */
define( 'SWPP_VERSION', '2.3.2' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );

// Add a registration key for the plugin to track this registration
add_filter('swp_registrations' , 'social_warfare_pro_registration_key');
function social_warfare_pro_registration_key($array) {
    $array[] = array(
        'plugin_name' => 'Social Warfare - Pro',
        'key' => 'pro',
        'product_id' => 63157
    );

    return $array;
}

add_action( 'plugins_loaded' , 'swpp_initiate_plugin' , 10 );
function swpp_initiate_plugin() {
    if(defined('SWP_VERSION') && SWP_VERSION === SWPP_VERSION):
        /**
         * Include the necessary files
         *
         */
        require_once SWPP_PLUGIN_DIR . '/functions/meta-box/meta-box.php';
        require_once SWPP_PLUGIN_DIR . '/functions/utilities/utility.php';
        require_once SWPP_PLUGIN_DIR . '/functions/admin/post-options.php';
        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/header-meta-tags.php';
        require_once SWPP_PLUGIN_DIR . '/functions/frontend-output/scripts.php';
        require_once SWPP_PLUGIN_DIR . '/functions/admin/options-array.php';

        /**
         * Include the networks files
         *
         */
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/tumblr.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/reddit.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/yummly.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/email.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/whatsapp.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/pocket.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/buffer.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/hackernews.php';
        require_once SWPP_PLUGIN_DIR . '/functions/social-networks/flipboard.php';

    endif;
}

/**
 * A function to notify users that the versions of Social Warfare and Social Warfare Pro are mismatched.
 *
 *
 * @since  2.2.0
 * @return void
 */
function swp_mismatch_notification() {
	global $swp_user_options;

	if(defined('SWP_VERSION') && SWP_VERSION !== SWPP_VERSION):
		echo '<div class="update-nag notice is-dismissable"><p>' . __( '<b>Important:</b> You are currently running Social Warfare v'.SWP_VERSION.' and Social Warfare - Pro v'.SWPP_VERSION.'. In order to avoid conflicts, these two version need to match in order to activate all of the plugins features. Please update the appropriate plugin so that both Social Warfare and Social Warfare - Pro are on the same version. For more information about this, <a href="https://warfareplugins.com/support/updating-social-warfare-social-warfare-pro/">please read this</a>. ', 'social-warfare' ) . '</p></div>';
	endif;
 }
 add_action( 'admin_notices', 'swp_mismatch_notification' );

/**
 * The Plugin Update checker
 *
 * @since 2.0.0
 * @access public
 */
require_once SWPP_PLUGIN_DIR . '/functions/update-checker/plugin-update-checker.php';
$swpp_github_checker = swp_PucFactory::getLatestClassVersion('PucGitHubChecker');
$swpp_update_checker = new $swpp_github_checker(
    'https://github.com/warfare-plugins/social-warfare-pro/',
    __FILE__,
    'master'
);

/**
 * Registration Update Notification
 *
 * @since 2.3.0
 * @access public
 * @return void
 *
 */
 function swp_registration_update_notification() {
    $options = get_option( 'socialWarfareOptions', array() );
    if( !empty($options['premiumCode']) && empty( $options['pro_license_key'] ) ):
        echo '<div class="notice-error notice is-dismissable"><p>' . __( '<b>Important:</b> We’ve just made some significant upgrades to your <i>Social Warfare - Pro</i> license. You will need to <a href="https://warfareplugins.com/my-account/">grab your license key</a> and re-register the plugin. Read <a href="https://warfareplugins.com/support/how-to-register-your-license-key/">the full details</a> to find out why this change was necessary.', 'social-warfare' ) . '</p></div>';
    endif;
  }
  add_action( 'admin_notices', 'swp_registration_update_notification' );
