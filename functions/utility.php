<?php
/**
 * General utility helper functions.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2016, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     2.1.0
 */

/**
 * Get the current site's URL.
 *
 * @since  2.1.0
 * @return string The current site's URL.
 */
function swp_get_site_url() {

	$domain = get_option( 'siteurl' );

	if ( is_multisite() ) {
		$domain = network_site_url();
	}

	return $domain;
}
