<?php

class SWP_Pro_Options_Page extends SWP_Options_Page {

	public function __construct() {

		global $SWP_Options_Page;

		$_options = $SWP_Options_Page->tabs->display->sections->share_counts->options;
		$_options->minimum_shares = new SWP_Option_Input();
		$_options->minimum_shares->set_name( __( 'Minimum Shares', 'social-warfare' ) )->set_priority(30)->set_premium('pro');

		// New Section: Meta Tags
		$_sections = $SWP_Options_Page->tabs->display->sections;
		$_sections->meta_tags = new SWP_Options_Page_Section();
		$_sections->meta_tags
			->set_priority( 30 )
			->set_name( 'Meta Tags' )
			->set_link( 'https://warfareplugins.com/support/options-page-display-tab-twitter-cards/' )
			->set_description( __( 'Open Graph meta tags and Twitter Cards are the standard for defining which title, image, and description get posted when a share is made.' , 'social-warfare' ) );
		$_options = $_sections->meta_tags->options;

		$_options->open_graph_tags = new SWP_Option_Checkbox();
		$_options->open_graph_tags->set_name( __( 'Output Open Graph Tags', 'social-warfare' ) )->set_priority(10)->set_premium('pro');

		$_options->twitter_cards = new SWP_Option_Checkbox();
		$_options->twitter_cards->set_name( __( 'Twitter Cards', 'social-warfare' ) )->set_priority(20)->set_premium('pro');
		

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
