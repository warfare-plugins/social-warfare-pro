<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  https://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     3.2.2
 * Author:      Warfare Plugins
 * Author URI:  https://warfareplugins.com
 * Text Domain: social-warfare
 */

defined( 'WPINC' ) || die;

/**
 * Define plugin constants for use throughout the plugin (Version and Directories)
 * @since 2.3.5 | 18 DEC 2017 | Added a constant to activate the registration tab built into core
 *
 */
define( 'SWPP_VERSION', '3.2.2' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'SWPP_SL_PRODUCT_ID', 189418 );  // Pro Utility Product id

add_action('plugins_loaded' , 'initialize_social_warfare_pro' , 20 );

function initialize_social_warfare_pro() {

    if ( !defined( 'SWP_VERSION' ) ) :
        //* We can not do any Pro without Core. Let them know and bail.
        add_action( 'admin_notices', 'swp_needs_core' );
        return;
    endif;

    if ( !class_exists( 'Social_Warfare_Addon' ) ) :
        require_once( SWP_PLUGIN_DIR . '/lib/Social_Warfare_Addon.php' );
    endif;


	if( defined( 'SWP_VERSION' ) && SWP_VERSION == SWPP_VERSION ):
        if ( file_exists( SWPP_PLUGIN_DIR . '/lib/Social_Warfare_Pro.php' ) ) :
    		require_once SWPP_PLUGIN_DIR . '/lib/Social_Warfare_Pro.php';
    		new Social_Warfare_Pro();
        endif;
    else:
        //* Do not instantiate Pro. Instead make them update.
        add_filter( 'swp_admin_notices', 'swp_pro_update_notification' );
	endif;

    if ( class_exists( 'Puc_v4_Factory') ) :
        $update_checker = Puc_v4_Factory::buildUpdateChecker(
        	'https://github.com/warfare-plugins/social-warfare-pro/',
        	__FILE__,
        	'social-warfare-pro'
        );
        $update_checker->getVcsApi()->enableReleaseAssets();
    endif;

}


if ( !function_exists( 'swp_needs_core' ) ) :
    function swp_needs_core() {
        ?>
        <div class="update-nag notice is-dismissable">
            <p><b>Important:</b> You currently have Social Warfare - Pro installed without our Core plugin installed.<br/>Please download the free core version of our plugin from the WordPress repo or from our <a href="https://warfareplugins.com" target="_blank">website</a>.</p>
        </div>
        <?php
    }
endif;


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
    echo '<div class="update-nag notice is-dismissable"><p><b>Important:</b> You are currently running Social Warfare v' . SWP_VERSION . ' and Social Warfare - Pro v' . SWPP_VERSION . '. In order to avoid conflicts, these two version need to match in order to activate all of the plugins features. Please update the appropriate plugin so that both Social Warfare and Social Warfare - Pro are on the same version. For more information about this, <a href="https://warfareplugins.com/support/updating-social-warfare-social-warfare-pro/">please read this</a></p></div>';
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
