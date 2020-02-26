<?php

/**
 * Evernote
 *
 * Class to add a Evernote share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2020, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     4.0.0 | 26 FEB 2020 | CREATED
 *
 */
class SWP_Evernote extends SWP_Social_Network {


	/**
	 * The Magic __construct Method
	 *
	 * This method is used to instantiate the social network object. It does three things.
	 * First it sets the object properties for each network. Then it adds this object to
	 * the globally accessible swp_social_networks array. Finally, it fetches the active
	 * state (does the user have this button turned on?) so that it can be accessed directly
	 * within the object.
	 *
	 * @since  4.0.0 | 26 APR 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {

		// Update the class properties for this network
		$this->name           = __( 'Evernote','social-warfare' );
		$this->cta            = __( 'Clip','social-warfare' );
		$this->key            = 'evernote';
		$this->default        = 'false';

		// This is the link that is clicked on to share an article to their network.
		$this->base_share_url = 'http://www.evernote.com/clip.action?url=';

		$this->init_social_network();
	}
}
