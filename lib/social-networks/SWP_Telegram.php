<?php
if ( false === class_exists( 'SWP_Social_Network' ) ) {
	return;
}

/**
 * Telegram
 *
 * Class to add a Telegram share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @since     4.1.0 | 17 APR 2020 | CREATED
 *
 */
class SWP_Telegram extends SWP_Social_Network {


	/**
	 * The Magic __construct Method
	 *
	 * This method is used to instantiate the social network object. It does three things.
	 * First it sets the object properties for each network. Then it adds this object to
	 * the globally accessible swp_social_networks array. Finally, it fetches the active
	 * state (does the user have this button turned on?) so that it can be accessed directly
	 * within the object.
	 *
	 * @since  4.1.0 | 17 APR 2020 | Created
	 * @param  void
	 * @return void
	 * @access public
	 *
	 */
	public function __construct() {

		// Update the class properties for this network
		$this->name           = __( 'Telegram','social-warfare' );
		$this->cta            = __( 'Telegram','social-warfare' );
		$this->key            = 'telegram';
		$this->default        = false;
        $this->premium        = 'pro';
		$this->base_share_url = 'https://telegram.me/share/url?url=';

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
	 * @since  4.1.0 | 17 APR 2020 | Created
	 * @param  array $array The array of information passed in from the buttons panel.
	 * @return string The generated link
	 * @access public
	 *
	 */
	public function generate_share_link( $post_data ) {
		return 'https://telegram.me/share/url?url=' . $this->get_shareable_permalink( $post_data ) .'&text=' . urlencode( $post_data['post_title'] );
	}
}
