<?php

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
	}

	public function instantiate_admin_classes() {

	}

}
