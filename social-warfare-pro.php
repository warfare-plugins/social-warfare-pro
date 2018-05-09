<?php
/**
 * Plugin Name: Social Warfare - Pro
 * Plugin URI:  http://warfareplugins.com
 * Description: A plugin to maximize social shares and drive more traffic using the fastest and most intelligent share buttons on the market, calls to action via in-post click-to-tweets, popular posts widgets based on share popularity, link-shortening, Google Analytics and much, much more!
 * Version:     3.0.3
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
define( 'SWPP_VERSION', '3.0.3' );
define( 'SWPP_PLUGIN_FILE', __FILE__ );
define( 'SWPP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'SWPP_PLUGIN_DIR', dirname( __FILE__ ) );

add_action('plugins_loaded' , 'initialize_social_warfare_pro' , 20 );

function initialize_social_warfare_pro() {
	if( defined('SWP_VERSION') && SWP_VERSION == SWPP_VERSION ):
		require_once SWPP_PLUGIN_DIR . '/functions/Social_Warfare_Pro.php';
		new Social_Warfare_Pro();

        // Queue up out footer hook function
        add_filter( 'swp_footer_scripts', 'swp_pinit_controls_output');

    elseif ( !defined('SWP_VERSION')) :
        add_action( 'admin_notices', 'needs_core' );
    else:
        add_action( 'admin_notices', 'mismatch_notification' );
	endif;
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
