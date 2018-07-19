<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  https://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     3.1.1
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
define( 'SWPP_VERSION', '3.1.1' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'EDD_SL_STORE_URL', 'https://warfareplugins.com' );

define( 'EDD_SL_PRODUCT_ID', 63157 );
// define( 'EDD_SL_PRODUCT_ID', 189418 );  // Test product id.

add_action('plugins_loaded' , 'initialize_social_warfare_pro' , 20 );

function initialize_social_warfare_pro() {
    //* TODO
    //* If the versions are not up to date, we need to display a message
    //* saying somthing like "To reap all the features of Pro, update to the latest version."

    if ( defined( 'SWP_VERSION' ) && SWP_VERSION != SWPP_VERSION ) {
        add_filter( 'swp_admin_notices', 'update_notification' );
    }

	// if( defined('SWP_VERSION') && SWP_VERSION == SWPP_VERSION ):
        if ( file_exists( SWPP_PLUGIN_DIR . '/functions/Social_Warfare_Pro.php' ) ) :
    		require_once SWPP_PLUGIN_DIR . '/functions/Social_Warfare_Pro.php';
    		new Social_Warfare_Pro();

            // Queue up out footer hook function
            add_filter( 'swp_footer_scripts', 'swp_pinit_controls_output');

        endif;
    //
    // elseif ( !defined('SWP_VERSION')) :
    //     add_action( 'admin_notices', 'needs_core' );
    //     return;
    // else:
    //     add_action( 'admin_notices', 'mismatch_notification' );
    //     return;
	// endif;

    /**
     * Note regarding keys:
     *
     * As I was testing, it seemd that the 'license' field trumps 'item_id'
     * as far as product validation goes. For example:
     *
     * Set item_id to a Test Product id
     * set license to a valid Test Product license
     * Result: Finds updates for Test Product
     *
     * Set item_id to a Test Product id
     * Set license to a valid Socail Warfare Pro license
     * Result: Finds updates for Social Warfare Pro

     * Set item_id to a Test Product id
     * Set license to "x"
     * Result: Finds no updates for anything.
     *
     */

    // retrieve our license key from the DB
    $options = get_option( 'social_warfare_settings' );
    $license_key = trim( $options['pro_license_key'] );

    //* $test_product_key pairs with item_id 189418 for 'Test Product for Updates'
    $test_product_key = 'cf88c0df1bf351d2142ce82edb5a10be';

    //* An expired Pro key.
    // $expired_key = 'ab81a8227a5ee7a180ca2dbf89b5b935';

    if ( !class_exists( 'SWP_EDD_SL_Plugin_Updater' ) && defined( 'SWP_PLUGIN_DIR' ) ) {
        require_once( SWP_PLUGIN_DIR . '/functions/utilities/SWP_EDD_SL_Plugin_Updater.php' );
    }

    $edd_updater = new SWP_EDD_SL_Plugin_Updater( EDD_SL_STORE_URL, __FILE__, array(
    	'version' 	=> SWPP_VERSION,		// current version number
    	'license' 	=> $license_key,	// license key (used get_option above to retrieve from DB)
        'item_id'   => EDD_SL_PRODUCT_ID,
    	'author' 	=> 'Warfare Plugins',	// author of this plugin
    	'url'           => home_url(),
        'beta'          => false // set to true if you wish customers to receive update notifications of beta releases
    ) );
}


/**
 * A function to output the Pin Button option controls
 *
 * @since  2.1.4
 * @access public
 * @param  array $info An array of footer script information.
 * @return array $info A modified array of footer script information.
 */
function swp_pinit_controls_output($info){
	global $swp_user_options;

	$pin_vars = array(
		'enabled' => false,
	);

	if ( $swp_user_options['pinit_toggle'] ) {
		$pin_vars['enabled']   = true;
		$pin_vars['hLocation'] = $swp_user_options['pinit_location_horizontal'];
		$pin_vars['vLocation'] = $swp_user_options['pinit_location_vertical'];
		$pin_vars['minWidth']  = str_replace( 'px', '', $swp_user_options['pinit_min_width'] );
		$pin_vars['minHeight'] = str_replace( 'px', '', $swp_user_options['pinit_min_height'] );
        $pin_vars['disableOnAnchors'] = $swp_user_options['pinit_hide_on_anchors'];

		// Set the image source
		if(isset($swp_user_options['pinit_image_source']) && 'custom' == $swp_user_options['pinit_image_source'] && get_post_meta( get_the_ID() , 'swp_pinterest_image_url' , true ) ):
			$pin_vars['image_source'] = get_post_meta( get_the_ID() , 'swp_pinterest_image_url' , true );
		endif;

		// Set the description Source
		if(isset($swp_user_options['pinit_image_description']) && 'custom' == $swp_user_options['pinit_image_description'] && get_post_meta( get_the_ID() , 'swp_pinterest_description' , true ) ):
			$pin_vars['image_description'] = get_post_meta( get_the_ID() , 'swp_pinterest_description' , true );
		endif;
	}

	$info['footer_output'] .= ' swpPinIt='.json_encode($pin_vars).';';
	return $info;
}

function needs_core() {
    ?>
    <div class="update-nag notice is-dismissable">
        <p><b>Important:</b> You currently have Social Warfare - Pro installed without our Core plugin installed.<br/>Please download the free core version of our plugin from the WordPress repo or from our <a href="https://warfareplugins.com" target="_blank">website</a>.</p>
    </div>
    <?php
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
 function update_notification( $notices = array() ) {
     if (is_string( $notices ) ) {
         $notices = array();
     }

     $notices[] = array(
         'key'   => 'update_notice_pro_' . SWPP_VERSION, // database key unique to this version.
         'message'   => 'Looks like your copy of Social Warfare - Pro isn\t up to date with Core. While you can still use both of these plugins, we highly recommend you keep both Core and Pro up-to-date for the best of what we have to offer.',
         'ctas'  => array(
             array(
                 'action'    => 'Remind me in a week.',
                 'timeframe' => 7 // permadismiss for this version.
             ),
             array(
                 'action'    => 'Thanks for letting me know.',
                 'timeframe' => 0 // permadismiss for this version.
             )
         )
     );

     return $notices;
}
