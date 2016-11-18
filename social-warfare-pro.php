<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  http://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     2.1.4
 * Author:      Warfare Plugins
 * Author URI:  http://warfareplugins.com
 * Text Domain: social-warfare
 */

defined( 'WPINC' ) || die;

/**
 * Define plugin constants for use throughout the plugin (Version and Directories)
 */
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );

function swpp_initiate_plugin() {
    if(defined('SWP_VERSION')):
        /**
         * Include the necessary files
         */
        require_once SWPP_PLUGIN_DIR . '/meta-box/meta-box.php';
        require_once SWPP_PLUGIN_DIR . '/functions/utility.php';
        require_once SWPP_PLUGIN_DIR . '/functions/registration.php';
        require_once SWPP_PLUGIN_DIR . '/functions/post-options.php';
        require_once SWPP_PLUGIN_DIR . '/functions/header-meta-tags.php';
        require_once SWPP_PLUGIN_DIR . '/functions/options-array.php';
        /**
         * Include the networks files
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
add_action( 'plugins_loaded' , 'swpp_initiate_plugin' , 10 );
/**
 * Coming soon
 */
// require_once SWP_PLUGIN_DIR . '/functions/media-options.php';
