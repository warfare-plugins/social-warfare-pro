<?php
if ( class_exists( 'SWP_Settings_Link' ) ):

/**
 * Adding the settings link to the plugins page
 *
 * This class and its methods add a link to the plugins page which links directly
 * to the Social Warfare settings page.
 *
 * @package   SocialWarfare\Admin\Functions
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     3.0.0 | 21 FEB 2018 | Created
 *
 */
class SWP_Pro_Settings_Link extends SWP_Settings_Link {


	/**
    * The magic method for instatiating this class
    *
    * This method called in the settings link by attaching it to the appropriate
    * WordPress hooks and filtering the passed array of $links.
    *
    * @since  3.0.0
    * @param  None
    * @return None
    *
    */
	public function __construct() {
		add_filter( 'plugin_action_links_' . plugin_basename( SWPP_PLUGIN_FILE ), array( $this , 'add_settings_links' ) );
	}
}

endif;
