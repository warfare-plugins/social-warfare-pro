<?php

class SWP_Pro_Options_Page extends SWP_Options_Page {

	public function __construct() {
		global $SWP_Options_Page;

		$_options = $SWP_Options_Page->tabs->display->sections->share_counts->options;
		$_options->minimum_shares = new SWP_Option_Input();
		$_options->minimum_shares->set_name( __( 'Minimum Shares', 'social-warfare' ) )->set_priority(30)->set_size('two-thirds')->set_default('0')->set_premium('pro');

		/**
		 * These all need moved to different tabs and/or sections.
		 *
		 * $_options->full_content = new SWP_Option_Checkbox();
		 * $_options->full_content->set_name( __( 'Full Content?', 'social-warfare' ) )->set_default( false )->set_premium( 'pro' );
		 *
		 * $_options->force_new_shares = new SWP_Option_Checkbox();
		 * $_options->force_new_shares->set_name( __( 'Force New Shares?', 'social-warfare' ))->set_default( false )->set_premium( 'pro' )->set_size( 'two-thirds ');
		*/
	}


}
