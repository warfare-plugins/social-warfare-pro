<?php

/**
 * SWP_Pro_Rebrandly
 *
 * This class will control all of the link shortening functionality for the
 * Rebrandly integration.
 *
 * @since 4.0.0 | 17 JUL 2019 | Created
 *
 */
class SWP_Pro_Rebrandly extends SWP_Link_Shortener {

	use SWP_Debug_Trait;

	public $key                = 'rebrandly';
	public $name               = 'Rebrandly';
	public $deactivation_hook  = '';
	public $authorization_link = '';

	public function __construct() {
		if( SWP_Utility::get_option('rebrandly_api_key') ) {
			$this->api_key = SWP_Utility::get_option('rebrandly_api_key');
			$this->active  = true;
		}
		parent::__construct();
	}

}
