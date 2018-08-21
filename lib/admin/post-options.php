<?php
/**
 * Add the custom meta boxes to all supported post types.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 */

defined( 'WPINC' ) || die;

/**
 * Get the Twitter handle for tweet counts.
 *
 * @param  string $fallback A default value to fall back to.
 * @return string $twitter_handle The twitter handle.
 */
function _swp_get_twitter_handle( $fallback = false ) {
	// Fetch the Twitter handle for the Post Author if it exists.
	if ( isset( $_GET['post'] ) ) {
		$user_id = SWP_User_Profile::get_author( absint( $_GET['post'] ) );
	} else {
		$user_id = get_current_user_id();
	}

	$twitter_handle = get_the_author_meta( 'swp_twitter', $user_id );

	if ( ! $twitter_handle ) {
		$twitter_handle = $fallback;
	}

	return $twitter_handle;
}
