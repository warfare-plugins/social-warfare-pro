<?php
if ( class_exists( 'SWP_Social_Network' ) ) :

/**
 * WhatsApp
 *
 * Class to add a WhatsApp share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | CREATED
 * @since     2.2.4 | 02 MAY 2017 | Refactored functions & updated docblocking
 * @since     3.0.0 | 05 APR 2018 | Rebuilt into a class-based system.
 *
 */
class SWP_WhatsApp extends SWP_Social_Network {


	/**
	 * The Magic __construct Method
	 *
	 * This method is used to instantiate the social network object. It does three things.
	 * First it sets the object properties for each network. Then it adds this object to
	 * the globally accessible swp_social_networks array. Finally, it fetches the active
	 * state (does the user have this button turned on?) so that it can be accessed directly
	 * within the object.
	 *
	 * @since  3.0.0 | 06 APR 2018 | Created
	 * @param  none
	 * @return none
	 * @access public
	 *
	 */
	public function __construct() {

		// Update the class properties for this network
		$this->name           = __( 'WhatsApp','social-warfare' );
		$this->cta            = __( 'WhatsApp','social-warfare' );
		$this->key            = 'whatsapp';
		$this->default        = false;
        $this->premium        = 'pro';

		$this->establish_base_share_url();
		$this->init_social_network();
	}

	public function contains_user_agent( $agent ) {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) && false !== strpos( $_SERVER['HTTP_USER_AGENT'], $agent );
	}

    /**
     * Adapted from
     * https://medium.com/@jeanlivino/how-to-fix-whatsapp-api-in-desktop-browsers-fc661b513dc
     *
     * @return [type] [description]
     */
	public function establish_base_share_url() {
		$mobiles = ['iPhone', 'Android', 'webOS', 'BlackBerry', 'iPod'];
		$is_mobile = count( array_filter( $mobiles, array( $this, 'contains_user_agent') ) );
		$this->base_share_url = $is_mobile ? "whatsapp://send?text=" : "https://api.whatsapp.com/send?text=";

	}
}

endif;
