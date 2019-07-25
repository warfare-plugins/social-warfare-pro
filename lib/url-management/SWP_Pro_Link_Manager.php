<?php

class SWP_Pro_Link_Manager {

	/**
	 * The magic __construct method.
	 *
	 * This method instantiates the SWP_Pro_Link_Manager object. It's primary function
	 * is to add the various methods to their approprate hooks for use later on
	 * when the core plugin calls for them.
	 *
	 * @since  4.0.0 | 17 JUL 2019 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {


		/**
		 * This class will manage and add the URL parameters for shared links
		 * using the Google Analytics UTM format. The link modifications made by
		 * this class are added via filter and will be accessed by applying the
		 * swp_analytics filter.
		 *
		 */
		new SWP_Pro_UTM_Tracking();


		/**
		 * This class will manage and process the shortened URLs for shared links
		 * if the user has shortlinks enabled and if they have Bitly selected as
		 * their link shortening integration of choice. The link modifications
		 * made by this class are added via filter and will be accessed by
		 * applying the swp_link_shortening filter.
		 *
		 */
		if( class_exists( 'SWP_Pro_Bitly' ) ) {
			new SWP_Pro_Bitly();
		}

		//if( class_exists( 'SWP_Pro_Rebrandly' ) ) {
			new SWP_Pro_Rebrandly();
		//}

	}
}
