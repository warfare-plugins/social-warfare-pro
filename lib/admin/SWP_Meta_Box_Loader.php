<?php

/**
 * Meta Box Loader Class
 *
 * A class to load up all of our custom meta boxes for things like the custom
 * Pinterest image, the Twitter description, etc.
 *
 * @package   SocialWarfare\Functions\Utilities
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since  3.1.0 | 02 JUL 2018 | Created
 *
 */
class SWP_Meta_Box_Loader {

	public function __construct() {
		if ( true === is_admin() ) {
			add_filter( 'swpmb_meta_boxes', array( $this, 'load_meta_boxes') );
			add_action( 'swpmb_before_social_warfare', array( $this, 'before_meta_boxes') );
			add_action( 'swpmb_after_social_warfare', array( $this, 'after_meta_boxes' ) );
		}
	}

	/**
	 * Load Meta Boxes
	 *
	 * @since  3.1.0 | 02 JUL 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function load_meta_boxes( $meta_boxes ) {
		$post_id = isset($_GET['post']) ? $_GET['post'] : 0;
		$prefix = 'swp_';
		$twitter_id = SWP_Utility::get_option( 'twitter_id' );

		$twitter_handle = $this->get_twitter_handle( $twitter_id );

		//* Set a default value if the user has never toggled the switch.
		if ( metadata_exists( 'post', $post_id, 'swp_force_pin_image' ) ) {
			$pin_force_image_value = get_post_meta($post_id, 'swp_force_pin_image', true);
		} else {
			$pin_force_image_value = true;
		}

		// $field = array(
		//  'name'	=> string // Display Name for the component',
		// 	'id'	=> string // Unique key for storing post_meta keys',
		// 	'type'	=> string // One of the metabox types in meta-box/inc/fields
		// 	'class'	=> string // The CSS class name to give the element.
		// 	             - @see $this::before_meta_boxes()
		// 				 - use swpmb-full-width for 100% width
		// 				 - use swpmb-left for a 50% left-aligned column
		// 				 - use swpmb-right for a 50% right-aligned column
		// 	'desc'	=> string // Text to display with the field.
		// )
		//


		$heading = array(
			'name'  => 'Optimize for Social',
			'id'    => 'swp_meta_box_heading',
			'type'  => 'heading',
			'class' => 'heading  swpmb-full-width',
			'desc'  => $this->generate_score_html() . '<p class="social_optimize_description">Make sure your content is shared exactly the way you want it to be shared by customizing the fields below. Let\'s face it. Nobody else is going to take the time to carefully craft titles and descriptions for your content when they share it to their timelines on social media. With Social Warfare, that doesn\'t matter. If you take a moment to carefully craft your post\'s images, titles and descriptions here, then these will be pre-filled for your visitors when they share your posts online.</p> ',
		);


		// Setup the Open Graph image.
		$open_graph_image = array(
			'name'  => __( 'Open Graph Image','social-warfare' ),
			'desc'  => __( 'Add an image that is optimized for maximum exposure on Facebook and LinkedIn. We recommend 1,200px by 628px.','social-warfare' ),
			'id'    => $prefix . 'og_image',
			'type'  => 'image_advanced',
			'class' => 'open-graph swpmb-left',
			'max_file_uploads' => 1,
			'image_size' => 'full'
		);

		// Setup the Open Graph title.
		$open_graph_title = array(
			'name'  => __( 'Open Graph Title','social-warfare' ),
			'desc'  => __( 'Add a title that will populate the open graph meta tag which will be used when users share your content onto Facebook, LinkedIn, and others. If nothing is provided here, we will use the post title as a backup.','social-warfare' ),
			'placeholder' => 'The Greatest Blog Post in the History of the World',
			'id'    => $prefix . 'og_title',
			'type'  => 'text',
			'class' => 'open-graph swpmb-right',
			'rows'	=> 1,
		);

		// Setup the Open Graph description.
		$open_graph_description = array(
			'name'  => __( 'Open Graph Description','social-warfare' ),
			'desc'  => __( 'Add a description that will populate the open graph meta tag which will be used when users share your content onto Facebook, LinkedIn, and others.','social-warfare' ),
			'placeholder' => __( 'Foursquare and seven years ago, a new blogger set forth...','social-warfare' ),
			'id'    => $prefix . 'og_description',
			'class' => 'open-graph swpmb-right',
			'type'  => 'textarea',
		);

		// Setup the Open Graph image.
		$twitter_image = array(
			'name'  => __( 'Twitter Card Image','social-warfare' ),
			'desc'  => __( 'Add an image that is optimized for maximum exposure on your Twitter card. We recommend 1,200px by 628px.','social-warfare' ),
			'id'    => $prefix . 'twitter_card_image',
			'type'  => 'image_advanced',
			'class' => 'twitter swpmb-left',
			'max_file_uploads' => 1,
			'image_size' => 'full'
		);

		// Setup the Twitter Card title.
		$twitter_title = array(
			'name'  => __( 'Twitter Card Title','social-warfare' ),
			'desc'  => __( 'Add a title that will populate the Twitter Card meta tag which will be used when users share your content onto Twitter. If nothing is provided here, we will use the post title as a backup.','social-warfare' ),
			'id'    => $prefix . 'twitter_card_title',
			'type'  => 'text',
			'class' => $prefix . 'twitter_card_title twitter swpmb-right',
			'rows'	=> 1,
		);

		// Setup the Twitter Card Description description.
		$twitter_description = array(
			'name'  => __( 'Twitter Card Description','social-warfare' ),
			'desc'  => __( 'Add a description that will populate the Twitter Card description meta tag which will be used when users share your content onto Twitter.','social-warfare' ),
			'id'    => $prefix . 'twitter_card_description',
			'class' => $prefix . 'twitter_card_description twitter swpmb-right',
			'type'  => 'textarea',
		);

		// Setup the Custom Tweet box.
		$custom_tweet = array(
			'name'  => __( 'Custom Tweet','social-warfare' ),
			'placeholder' => 'Write your awesome custom tweet here...',
			'desc'  => ( $twitter_id
							 ? sprintf( __( 'If this is left blank your post title will be used. Based on your username (<span id="swp-twitter-handle">@%1$s</span>), a link being added, and the current content above, your tweet has %2$s characters remaining.', 'social-warfare' ), str_replace( '@', '' ,$twitter_handle ), '<span class="counterNumber">280</span>' )
							 : sprintf( __( 'If this is left blank your post title will be used. Based on a link being added, and the current content above, your tweet has %s characters remaining.','social-warfare' ), '<span class="counterNumber">280</span>' ) ),
			'id'    => $prefix . 'custom_tweet',
			'class' => $prefix . 'custom_tweetWrapper custom_tweet  swpmb-full-width',
			'type'  => 'textarea',
		);

		$open_graph_toggle = array(
			'id'    => 'swp_twitter_use_open_graph',
			'type'  => 'switch',
			'name'  => __( 'Use Open Graph for Twitter Card?', 'social-warfare'),
			'desc'	=> '',
			'std'=> 1,
			'class' => 'twitter_og_toggle swpmb-left',
			'on_label' => 'On',
			'off_label' => 'Off',
			'style' => 'square'
		);

		// Setup the pinterest optimized image.
		$pinterest_image = array(
			'name'  => __( 'Pinterest Image','social-warfare' ),
			'desc'  => __( 'Add one or more images that are optimized for maximum exposure on Pinterest. We recommend using an image that is formatted in a 2:3 aspect ratio like 1000 x 1500. <br /><br /> <b>Pro Tip:</b> You can now upload as many Pinterest images as you\'d like. These images will be presented to the user when they click on the Pinterest button. They will also be added to the post content (top, bottom or hidden) so they appear for people using browser extensions, but only if you have this option turned on in the Social Warfare options page.','social-warfare' ),
			'id'    => $prefix . 'pinterest_image',
			'class' => $prefix . 'large_image pinterest swpmb-left',
			'type'  => 'image_advanced',
			'max_file_uploads' => 99,
			'image_size' => 'full'
		);

		$pinterest_description = array(
			'name'  => __( 'Pinterest Description','social-warfare' ),
			'desc'  => __( 'Craft a customized description that will be used when this post is shared on Pinterest. Leave this blank to use the title of the post.','social-warfare' ),
			'placeholder' => __( 'Rockin\' out on the Pinterest description...', 'social-warfare' ),
			'id'    => $prefix . 'pinterest_description',
			'class' => $prefix . 'pinterest_descriptionWrapper pinterest swpmb-right',
			'type'  => 'textarea',
		);

		// Setup the pinterest description.
		$pin_browser_extension = array(
			'name'    => __( 'Pin Image for Browser Extensions','social-warfare' ),
			'id'      => 'swp_pin_browser_extension',
			'type'    => 'select',
			'options' => array(
				'default' => __( 'Default','social-warfare' ),
				'on'      => __( 'On','social-warfare' ),
				'off'     => __( 'Off','social-warfare' ),
			),
			'class' => 'pinterest swpmb-right',
			'std'   => 'default',
		);

		$pin_browser_extension_location = array(
			'name'    => __( 'Pin Browser Image Location','social-warfare' ),
			'id'      => 'swp_pin_browser_extension_location',
			'type'    => 'select',
			'options' => array(
				'default' => __( 'Default','social-warfare' ),
				'hidden'  => __( 'Hidden','social-warfare' ),
				'top'     => __( 'At the Top of the Post','social-warfare' ),
				'bottom'  => __( 'At the Bottom of the Post','social-warfare' ),
			),
			'class' => 'pinterest swpmb-right',
			'std'   => 'default',
		);

		$recover_shares_box = array(
			'name'  => __( 'Share Recovery','social-warfare' ),
			'desc'  => __( 'If you have changed the permalink for just this post, paste in the previous full URL for this post so we can recover shares for that link.','social-warfare' ),
			'id'    => 'swp_recovery_url',
			'class' => $prefix . 'share_recoveryWrapper other',
			'type'  => 'text',
		);

		$other_post_options = array(
			'name'  => 'Other Post Options',
			'id'    => 'swp_other_heading',
			'type'  => 'heading',
			'class' => 'other swpmb-full-width',
			'desc'	=> ''
		);

		// Set up the location on post options.
		$post_location = array(
			'name'    =>  __( 'Static Buttons Location','social-warfare' ),
			'id'      => $prefix . 'post_location',
			'type'    => 'select',
			'options' => array(
				'default' => __( 'Default','social-warfare' ),
				'above'   => __( 'Above the Content','social-warfare' ),
				'below'   => __( 'Below the Content','social-warfare' ),
				'both'    => __( 'Both Above and Below the Content','social-warfare' ),
				'none'    => __( 'None/Manual Placement','social-warfare' ),
			),
			'class' => 'other swpmb-left inline-select',
			'std'	=> 'default',
		);

		$float_location = array(
			'name'    =>  __( 'Floating Buttons Location','social-warfare' ),
			'id'      => $prefix . 'float_location',
			'type'    => 'select',
			'options' => array(
				'default' => __( 'Default','social-warfare' ),
				'on'      => __( 'On','social-warfare' ),
				'off'     => __( 'Off','social-warfare' ),
			),
			'class' => 'other swpmb-left inline-select',
			'std'   => 'default',
		);

		$reset_button = array(
			'name'    =>  __( 'Reset Post Meta','social-warfare' ),
			'id'    => 'swp_reset_button',
			'desc'  => __( 'If the buttons on this post are misbehaving, this resets all Social Warfare data for this post.','social-warfare' ),
			'class' => 'swpmb-left other',
			'type'  => 'display-text',
		);

		// Setup our meta box using an array.
		$meta_boxes[0] = array(
			'id'       => 'social_warfare',
			'title'    => __( 'Social Warfare Custom Options','social-warfare' ),
			'pages'    => SWP_Utility::get_post_types(),
			'context'  => 'normal',
			'priority' => apply_filters( 'swp_metabox_priority', 'high' ),
			'fields'   => array()
		);

		$meta_boxes[0]['fields'][] = $heading;
		$meta_boxes[0]['fields'][] = $open_graph_image;
		$meta_boxes[0]['fields'][] = $open_graph_title;
		$meta_boxes[0]['fields'][] = $open_graph_description;
		$meta_boxes[0]['fields'][] = $pinterest_image;
		// $meta_boxes[0]['fields'][] = $pin_force_image;
		$meta_boxes[0]['fields'][] = $open_graph_toggle;
		$meta_boxes[0]['fields'][] = $twitter_image;
		$meta_boxes[0]['fields'][] = $twitter_title;
		$meta_boxes[0]['fields'][] = $twitter_description;

		$meta_boxes[0]['fields'][] = $custom_tweet;

		if ( SWP_Utility::get_option( 'recover_shares' ) ) {
			$meta_boxes[0]['fields'][] = $recover_shares_box;
		}

		$meta_boxes[0]['fields'][] = $pinterest_description;
		$meta_boxes[0]['fields'][] = $pin_browser_extension;
		$meta_boxes[0]['fields'][] = $pin_browser_extension_location;
		$meta_boxes[0]['fields'][] = $other_post_options;
		$meta_boxes[0]['fields'][] = $post_location;
		$meta_boxes[0]['fields'][] = $float_location;
		$meta_boxes[0]['fields'][] = $reset_button;
		// $meta_boxes[0]['fields'][] = $twitter_handle_box;

		return $meta_boxes;
	}


	public function get_twitter_handle( $fallback = '' ) {
		// Fetch the Twitter handle for the Post Author if it exists.
		if ( isset( $_GET['post'] ) ) {
			$user_id = SWP_User_Profile::get_author( absint( $_GET['post'] ) );
		} else {
			$user_id = get_current_user_id();
		}

		$twitter_handle = get_the_author_meta( 'swp_twitter', $user_id );

		if ( ! $twitter_handle ) {
			$twitter_handle = $fallback;
		}

		esc_attr( $twitter_handle );

		return $twitter_handle;
	}


	/**
	 * Echoes content before any meta box fields are printed.
	 * Note: You must echo your content immediately, and return the $meta_box.
	 *
	 * Social Warfare uses this to create sections to organize each of the
	 * fields created in load_meta_boxes().
	 *
	 * The field-to-section relationship is as follows:
	 *
	 * - A unique key goe sin the $boxes array below.
	 * - This key is correlated to a CSS class selector.
	 * - All items of class X are put in $container data-type=[X].
	 *
	 * Therefore, to put a 'New Pinterest Thing' in the 'pinterest' section,
	 * verify that the $new_pinterest_thing array created in load_meta_boxes()
	 * has an index for 'class' which includes the string 'pinterest'.
	 *
	 *
	 * @param  object $meta_box The Rylwis meta_box object.
	 * @return object $meta_box The (optionally) filtered meta box.
	 * @since  3.3.0 | September 2018 (estimate) | Created
	 * @see    | social-warfare/assets/js/admin.js | putFieldsInContainers()
	 *
	 */
	public function before_meta_boxes( $meta_box  ) {
		$default_boxes = array( 'heading', 'open-graph', 'custom_tweet', 'twitter_og_toggle', 'twitter', 'pinterest', 'other' );
		$boxes = apply_filters( 'swp_meta_boxes', $default_boxes);

		foreach ($boxes as $box) {
			$container = '<div class="swpmb-meta-container" data-type="' . $box . '">';
				$container .= '<div class="swpmb-full-width-wrap swpmb-flex"></div>';
				$container .= '<div class="swpmb-left-wrap swpmb-flex"></div>';
				$container .= '<div class="swpmb-right-wrap swpmb-flex"></div>';
			$container .= '</div>';

			echo $container;
		}

		return $meta_box;
	}

	/**
	 * Echoes content after the meta box fields are printed.
	 *
	 * You must echo your content immediately, and return the $meta_box.
	 *
	 * @param  object $meta_box The Rylwis meta_box object.
	 * @return object $meta_box The (optionally) filtered meta box.
	 *
	 */
	public function after_meta_boxes( $meta_box ) {
		return $meta_box;
	}


	/**
	 * The generate_score_html() method will create the html for the heading that
	 * will feature the circular score on the right hand side. Clicking on this
	 * header
	 *
	 * @since  4.1.0 | 15 AUG 2020 | Created
	 * @param  void
	 * @return string The string of rendered html.
	 *
	 */
	public function generate_score_html() {
		$html = '<div class="social_score_wrapper"><div class="score_title">Optimize for Social</div><div class="score_rating"><div class="score_rating_top">0</div><div class="score_rating_bottom">100</div></div><div class="swp_clearfix"></div></div>';
		return $html;
	}
}
