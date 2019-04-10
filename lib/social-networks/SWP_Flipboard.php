<?php
if ( class_exists( 'SWP_Social_Network' ) ) :

/**
 * Flipboard
 *
 * Class to add a Flipboard share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | CREATED
 * @since     2.2.4 | 02 MAY 2017 | Refactored functions & updated docblocking
 * @since     3.0.0 | 05 APR 2018 | Rebuilt into a class-based system.
 *
 */
class SWP_Flipboard extends SWP_Social_Network {


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
		$this->name           = __( 'Flipboard','social-warfare' );
		$this->cta            = __( 'Flip','social-warfare' );
		$this->key            = 'flipboard';
		$this->default        = false;
        $this->premium        = 'pro';
		$this->base_share_url = 'https://share.flipboard.com/bookmarklet/popout?v=2';

		$this->init_social_network();
	}

	/**
     * Generate the share link
     *
     * This is the link that is being clicked on which will open up the share
     * dialogue. Thie method is only used for networks that use this exact same pattern.
     * For anything that accepts more than just the post permalink as a URL parameter,
     * those networks will have to overwrite this method with their own custom method
     * in their respective child classes.
     *
     * @since  3.4.0 | 30 OCT 2018 | Created
     * @param  array $post_data The array of information passed in from the buttons panel.
     * @return string The generated link
     * @access public
     *
     */
	public function generate_share_link( $post_data ) {

		$title = get_post_meta( $post_data['ID'] , 'swp_og_title' , true );

		if ( !$title ) :
			$title = isset( $post_data['post_title'] ) ? $post_data['post_title'] : get_the_title();
		endif;

		$share_link = $this->base_share_url . "&title=" . urlencode( $title ) . "&url=" . $this->get_shareable_permalink( $post_data );

		return $share_link;
	}

}
endif;
