<?php

/**
 * SWFW_Flickr
 *
 * This provides an interface for creating a follow button for Flickr.
 *
 * @package   social-follow-widget
 * @copyright Copyright (c) 2019, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Created
 *
 */
class SWP_FW_Flickr extends SWP_Pro_Follow_Network {


	/**
	 * Applies network-specific data to the SWFW_Follow_Network
	 *
	 * @since 1.0.0 | 03 DEC 2018 | Created.
	 * @see SWFW_Follow_Network
	 * @param void
	 * @return void
	 */
	public function __construct() {
		$network = array(
			'key'                 => 'flickr',
			'name'                => 'Flickr',
			'cta'                 => 'Follow',
			'follow_description'  => 'Followers',
			'color_primary'       => '#0063dc',
			'color_accent'        => '#ff0084',
			'url'                 => 'https://www.flickr.com/people/swfw_username/',
			'needs_authorization' => false
		);

		parent::__construct( $network );
	}


	/**
	 * This network does not have a follow API, so it must return false.
	 *
	 * @since 1.0.0 | 12 FEB 2019 | Created.
	 * @param void
	 * @return void;
	 *
	 */
	public function do_api_request() {
		return false;
	}


	/**
	 * This network does not have a follow API, so it must return 0.
	 *
	 * @since 1.0.0 | 12 FEB 2019 | Created.
	 * @param void
	 * @return string 0
	 *
	 */
	public function parse_api_response() {
		return '0';
	}
}
