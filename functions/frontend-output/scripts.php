<?php
/**
 * Register and enqueue plugin scripts and styles.
 *
 * @package   Social Warfare - Pro\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 */

defined( 'WPINC' ) || die;

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
	if ( is_swp_registered() ) {

		if ( $swp_user_options['pinit_toggle'] ) {
			$pin_vars['enabled']   = true;
			$pin_vars['hLocation'] = $swp_user_options['pinit_location_horizontal'];
			$pin_vars['vLocation'] = $swp_user_options['pinit_location_vertical'];
			$pin_vars['minWidth']  = str_replace( 'px', '', $swp_user_options['pinit_min_width'] );
			$pin_vars['minHeight'] = str_replace( 'px', '', $swp_user_options['pinit_min_height'] );
		}
	}
	$info['footer_output'] .= ' swpPinIt='.json_encode($pin_vars).';';
	return $info;
}

// Queue up out footer hook function
add_filter( 'swp_footer_scripts', 'swp_pinit_controls_output');
