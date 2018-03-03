<?php

/**
 * TODO: This file needs to mirror Social_Warfare.php and the Social_Warfare
 * 		 class that is in core.
 */
class Social_Warfare_Pro extends Social_Warfare {

	public function __construct() {
		$this->load_classes();
		$this->instantiate_classes();

		if( true === is_admin() ){
			$this->instantiate_admin_classes();
		}
	}

	public function load_classes() {
		require_once SWPP_PLUGIN_DIR . '/functions/admin/SWP_Pro_Options_Page.php';
	}

	public function instantiate_classes() {

		new SWP_Pro_Options_page();

		// Output for testing:
		global $SWP_Options_Page;

		// We can sort the object immediately before looping for HTML output.
		// $SWP_Options_Page->sort_by_priority();
		// var_dump($SWP_Options_Page);
	}

	public function instantiate_admin_classes() {

	}

}
