<?php
if ( class_exists( 'SWP_Social_Network' ) ) :

/**
 * Yummly
 *
 * Class to add a Yummmly share button to the available buttons
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | CREATED
 * @since     2.2.4 | 02 MAY 2017 | Refactored functions & updated docblocking
 * @since     3.0.0 | 05 APR 2018 | Rebuilt into a class-based system.
 * @since     3.0.8 | 21 MAY 2018 | Added render_thml() and check_taxonomy_conditionals()
 *
 */
class SWP_Yummly extends SWP_Social_Network {


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
		$this->name           = __( 'Yummly','social-warfare' );
		$this->cta            = __( 'Yum','social-warfare' );
		$this->key            = 'yummly';
		$this->default        = false;
        $this->premium        = 'pro';
		$this->base_share_url = 'https://www.yummly.com/urb/verify?url=';

		$this->init_social_network();
	}


	/**
	 * Generate the API Share Count Request URL
	 *
	 * @since  1.0.0 | 06 APR 2018 | Created
	 * @access public
	 * @param  string $url The permalink of the page or post for which to fetch share counts
	 * @return string $request_url The complete URL to be used to access share counts via the API
	 *
	 */
	public function get_api_link( $url ) {
        return 'https://www.yummly.com/services/yum-count?url=' . $url;
	}


	/**
	 * Parse the response to get the share count
	 *
	 * @since  1.0.0 | 06 APR 2018 | Created
	 * @access public
	 * @param  string $response The raw response returned from the API request
	 * @return int $total_activity The number of shares reported from the API
	 *
	 */
	public function parse_api_response( $response ) {
        $response = json_decode( $response, true );
    	return isset( $response['count'] )?intval( $response['count'] ):0;
	}


	/**
	 * Render the HTML
	 *
	 * This method is being added to this child class, instead of just using
	 * the parent render_html() method, because we need to check if the Yummly
	 * terms or categories have been set on the options page and then if so,
	 * we'll call the parent render_html method to actually do the work.
	 *
	 * @since  3.0.8 | 21 MAY 2018 | Created
	 * @param  array  $panel_context The necessary data from the buttons panel.
	 * @param  boolean $echo         Return of echo the html.
	 * @return string                The html for the button.
	 *
	 */
    public function render_HTML( $panel_context, $echo = false ) {
        if ( true === $this->check_taxonomy_conditionals( $panel_context ) ) :
            return parent::render_HTML( $panel_context, $echo );
        else:
            return '';
        endif;
    }


	/**
	 * Check the Yummly Taxonomy Conditionals
	 *
	 * This method is designed to check if the user has set a tag or category
	 * on the options page to limit the display of the Yummly button to only
	 * show up on posts that have that tag or are in that category.
	 *
	 * @since  3.0.8 | 21 MAY 2018 | Created
	 * @param  array $panel_context The necessary data from the buttons panel.
	 * @return bool                 true: conditions met, display the button
	 *                              false: conditions not met, don't display it
	 *
	 */
	private function check_taxonomy_conditionals( $panel_context ) {

		// Create local variables to keep the logic below cleaner.
		$id = $panel_context['post_data']['ID'];
		$cat = $panel_context['options']['yummly_categories'];
		$tag = $panel_context['options']['yummly_tags'];

		// If a category is set and this post is in that category.
		if( !empty( $cat ) && in_category( $cat , $id ) ):
			return true;
		endif;

		// If a tag is set and this post is in that tag.
		if ( !empty( $tag ) && has_tag( $tag , $id ) ):
			return true;
		endif;

		// If no tags or categories have been set
		if ( empty( $tag ) && empty( $cat ) ):
			return true;
		endif;

		return false;

	}

}
endif;

// private function check_taxonomy_conditionals() {
//     global $post;
//     $post_tags = get_the_tags( $post->ID );
//
//     if ( $post_tags !== false ) :
//         //* Trim whitespace and return an array.
//         $user_tags = preg_split ('/[\s*,\s*]*,+[\s*,\s*]*/', $swp_user_options['yummly_tags']);
//
//         foreach ( $post_tags as $tag ) {
//             if ( in_array( $tag, $user_tags ) ) :
//                 return true;
//             endif;
//         }
//
//     endif;
//
//     $post_categories = wp_get_post_categories();
//
//     //* wp_get_post_categories can return a WP error. Make sure we don't process it.
//     if ( !is_wp_error( $post_categories) && count( $post_categories ) > 0 ) :
//         //* Trim whitespace and return an array.
//         $user_categories = preg_split ('/[\s*,\s*]*,+[\s*,\s*]*/', $swp_user_options['yummly_categories']);
//
//         foreach( $post_categories as $cat ) {
//             $category = get_category( $cat );
//
//             if ( in_array( $category->name, $user_categories ) || in_array( $category->slug, $user_categories ) ) :
//                 return true;
//             endif;
//         }
//     endif;
//
//     return false;
// }
