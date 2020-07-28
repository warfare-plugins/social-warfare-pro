<?php

// Bail out if we don't have access to the class we need to extend.
if( false === class_exists( 'SWP_Social_Network' ) ) {
   return;
}

/**
 * More Button
 *
 * Class to add a More button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2020, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     4.0.0 | 24 FEB 2020 | CREATED
 *
 */
class SWP_More extends SWP_Social_Network {


	/**
	 * The Magic __construct Method
	 *
	 * This method is used to instantiate the social network object. It does three things.
	 * First it sets the object properties for each network. Then it adds this object to
	 * the globally accessible swp_social_networks array. Finally, it fetches the active
	 * state (does the user have this button turned on?) so that it can be accessed directly
	 * within the object.
	 *
	 * @since  4.0.0 | 24 APR 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {

		// Update the class properties for this network
		$this->name           = __( 'More','social-warfare' );
		$this->cta            = __( 'More','social-warfare' );
		$this->key            = 'more';
		$this->default        = 'false';
        $this->premium        = 'pro';
		$this->visible_on_amp = false;

		$this->init_social_network();
	}


    /**
     * Generate the share link
     *
     * Since this button will not use a share link, we'll populate it with the
     * pound sign, and then hijack the click via a click event listener in the
     * javascript file. This will, in turn, trigger the more share options
     * lightbox popup options.
     *
     * @since  4.0.0 | 24 FEB 2020 | Created
     * @param  array $array The array of information passed in from the buttons panel.
     * @return string The generated link (in this case a pound sign)
     *
     */
    public function generate_share_link( $post_data ) {
		return '#';
    }

}
