<?php
if ( class_exists( 'SWP_Social_Network' ) ) :

/**
 * Email
 *
 * Class to add an Email share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | CREATED
 * @since     2.2.4 | 02 MAY 2017 | Refactored functions & updated docblocking
 * @since     3.0.0 | 05 APR 2018 | Rebuilt into a class-based system.
 *
 */
class SWP_Email extends SWP_Social_Network {


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
		$this->name           = __( 'Email','social-warfare' );
		$this->cta            = __( 'Email','social-warfare' );
		$this->key            = 'email';
		$this->default        = true;
		$this->premium        = 'pro';
		$this->base_share_url = 'mailto:?subject=';

		$this->init_social_network();
	}


	/**
	 * Generate Share link
	 *
	 * @since  3.0.5 | 11 MAY 2018 | Created
	 * @param  array $post_data The array of Post Data
	 * @return string           The share link.
	 *
	 */
	public function generate_share_link( $post_data ) {

		// Collect the Title
		$subject = get_post_meta( $post_data['ID'] , 'swp_og_title' , true );
		if ( false == $subject ) :
			$subject = $post_data['post_title'];
		endif;

		// Collect the Description
		$body = get_post_meta( $post_data['ID'] , 'swp_og_description' , true );
		if ( false == $body ) :
			$body = SWP_Utility::get_the_excerpt( $post_data['ID'] );
		endif;

		$permalink = $this->get_shareable_permalink( $post_data );
		$newline = "%0D%0A";

		$share_link = 'mailto:?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($body) . $newline . $newline .  __('Read More Here: ' , 'social-warfare' ) . '%20' . $permalink;
		return $share_link;
	}


	/**
	 * Create the HTML to display the share button
	 *
	 * @since  1.0.0
	 * @access public
	 * @param  array $network_counts Associative array of 'network_key' => 'count_value'
	 * @return array $array The modified array which will now contain the html for this button
	 * @todo   Eliminate the array
	 *
	 */
	public function render_HTML( $panel_context , $echo = false ) {

		$post_data = $panel_context['post_data'];
		$share_counts = $panel_context['shares'];
		$options = $panel_context['options'];

		$share_link = $this->generate_share_link( $post_data );

		// Build the button.
		$icon = '<span class="iconFiller">';
			$icon.= '<span class="spaceManWilly">';
				$icon.= '<i class="sw swp_'.$this->key.'_icon"></i>';
				$icon.= '<span class="swp_share">' . $this->cta . '</span>';
			$icon .= '</span>';
		$icon .= '</span>';

		if ( true === $this->are_shares_shown( $share_counts , $options ) ) :
			$icon .= '<span class="swp_count">' . SWP_Utility::kilomega( $share_counts[$this->key] ) . '</span>';
		else :
			$icon = '<span class="swp_count swp_hide">' . $icon . '</span>';
		endif;

		// Build the wrapper.
		$html = '<div class="nc_tweetContainer swp_share_button swp_'.$this->key.'" data-network="'.$this->key.'">';
			$html .= '<a class="nc_tweet noPop swp_share_link" rel="nofollow noreferrer noopener" href="' . $share_link . '" target="_blank">';
				// Put the button inside.
				$html .= $icon;
			$html.= '</a>';
		$html.= '</div>';

		// Store these buttons so that we don't have to generate them for each set
		$this->html = $html;

		if ( $echo ) :
			echo $html;
		endif;

		return $html;

	}
}
endif;
