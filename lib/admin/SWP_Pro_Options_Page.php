<?php
if ( class_exists( 'SWP_Options_Page' ) ) :

class SWP_Pro_Options_Page extends SWP_Options_Page {
	/**
	* Reference to the global SWP_Options_Page object.
	* SWP_Options_Page $core
	*/

	public function __construct() {
		$this->update_display_tab();
		$this->update_styles_tab();
		$this->update_social_tab();
		$this->update_advanced_tab();
	}

	/**
	* Adds many more options to the Advanced tab.
	*
	* @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
	*/
	public function update_advanced_tab() {
		global $SWP_Options_Page;

		$advanced = $SWP_Options_Page->tabs->advanced;


		$analytics_tracking = new SWP_Options_Page_Section( __('Analytics Tracking', 'social-warfare' ), 'analytics_tracking' );
		$analytics_tracking->set_description( __( 'If you want to activate UTM tracking for shared URL, turn this on.', 'social-warfare') )
			->set_priority( 30 );

			//* swp_click_tracking => click_tracking
			$click_tracking = new SWP_Option_Toggle( __('Button Click Tracking', 'social-warfare' ), 'click_tracking' );
			$click_tracking->set_priority( 10 )
				->set_size( 'sw-col-300' )
				->set_default( false )
				->set_premium( 'pro' );

			//* googleAnalytics => google_analytics
			$google_analytics = clone $click_tracking;
			$google_analytics->set_name( __( 'UTM Link Tracking', 'social-warfare' ) )
				->set_default(false)
				->set_key( 'google_analytics' )
				->set_priority( 20 );

			//* analyticsMedium => analytics_medium
			$analytics_medium = new SWP_Option_Text( __( 'UTM Medium', 'social-warfare' ), 'analytics_medium' );
			$analytics_medium->set_default( 'Social' )
				->set_priority( 30 )
				->set_size( 'sw-col-300' )
				->set_dependency( 'google_analytics', true )
				->set_premium( 'pro');

			$analytics_campaign = new SWP_Option_Text( __( 'UTM Campaign', 'social-warfare' ), 'analytics_campaign' );
			$analytics_campaign->set_default( 'SocialWarfare' )
				->set_priority( 40 )
				->set_size( 'sw-col-300' )
				->set_dependency( 'google_analytics', true )
				->set_premium( 'pro');

			$utm_on_pins = new SWP_Option_Toggle( __( 'UTM Tracking on Pins', 'social-warfare' ), 'utm_on_pins' );
			$utm_on_pins->set_default( false )
				->set_priority( 50 )
				->set_size( 'sw-col-300' )
				->set_dependency( 'google_analytics', true )
				->set_premium( 'pro' );

				$analytics_tracking->add_option( $utm_on_pins );

			$analytics_tracking->add_options( [$click_tracking, $google_analytics, $analytics_medium,
				$analytics_campaign, $utm_on_pins] );

		$advanced_pinterest = new SWP_Options_Page_Section( __( 'Advanced Pinterest Settings', 'social-warfare' ), 'advanced_pinterest' );
		$advanced_pinterest->set_description( __( 'Get maximum control over how your visitors are sharing your content on Pinterest.', 'social-warfare') )
			->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-advanced-pinterest-settings/' )
			->set_priority( 40 )
			->set_premium( 'pro' );

			//* advanced_pinterest_image => pin_browser_extension
			$pin_browser_extension = new SWP_Option_Toggle( __( 'Pinterest Image for Browser Extensions', 'social-warfare' ), 'pin_browser_extension' );
			$pin_browser_extension->set_default( false )
				->set_size( 'sw-col-300')
				->set_premium( 'pro' )
				->set_priority( 10 );

			//* advanced_pinterest_image_location => pinterest_image_location
			$pinterest_image_location = new SWP_Option_Select( __( 'Pinterest Image Location', 'social-warfare' ), 'pinterest_image_location' );
			$pinterest_image_location->set_choices( [
				'hidden'    => __( 'Hidden', 'social-warfare'),
				'top'       => __( 'At the top of each post.', 'social-warfare'),
				'bottom'    => __( 'At the bottom of each post.', 'social-warfare')
			])
				->set_default( 'hidden ')
				->set_size( 'sw-col-300' )
				->set_dependency( 'pin_browser_extension', true )
				->set_premium( 'pro' )
				->set_priority( 20 );

			//* advanced_pinterest_fallback => pinterest_fallback
			$pinterest_fallback = new SWP_Option_Select( __( 'Pinterest Image Fallback', 'social-warfare' ), 'pinterest_fallback' );
			$pinterest_fallback->set_choices( [
				'all'       => __( 'Show a selection of all images on the page.', 'social-warfare'),
				'featured'  => __( 'Show my featured image.', 'social-warfare')
			])
				->set_size( 'sw-col-300' )
				->set_default( 'all' )
				->set_premium( 'pro' )
				->set_priority( 30 );

			$pinterest_data_attribute = new SWP_Option_Toggle( __( 'Add a <code>data-pin-description</code> to images that do not have one', 'social-warfare' ), 'pinterest_data_attribute' );
			$pinterest_data_attribute->set_default( false )
				->set_size( 'sw-col-300' )
				->set_premium( 'pro' )
				->set_priority( 40 );

			$pinit_image_description = new SWP_Option_Select( __( 'Description Source', 'social-warfare' ), 'pinit_image_description' );
			$pinit_image_description->set_priority( 70 )
				->set_choices( [
					'alt_text' => __( 'Image ALT Text' , 'social-warfare' ) ,
					'custom'   => __( 'Custom Pin Description' , 'social-warfare' )
				])
				->set_size( 'sw-col-300' )
				->set_default( 'image' )
				->set_premium( 'pro' )
				->set_priority( 50 );

		$advanced_pinterest->add_options( [$pin_browser_extension, $pinterest_image_location, $pinterest_fallback, $pinterest_data_attribute, $pinit_image_description] );

		$share_recovery = new SWP_Options_Page_Section( __( 'Share Recovery', 'social-warfare' ), 'share_recovery' );
		$share_recovery->set_description( __( 'If at any point you have changed permalink structures or have gone from http to https (SSL) then you will have undoubtedly lost all of your share counts. This tool allows you to recover them. See <a target="_blank" href="https://warfareplugins.com/support/recover-social-share-counts-after-changing-permalink-settings/">this guide</a> for more detailed instructions on how to use this feature.', 'social-warfare') )
			->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-share-recovery/' )
			->set_priority( 50 )
			->set_premium( 'pro' );

			$recover_shares = new SWP_Option_Toggle( __( 'Activate Share Recovery', 'social-warfare' ), 'recover_shares' );
			$recover_shares->set_default( false )
				->set_priority( 10 )
				->set_size( 'sw-col-300' )
				->set_premium( 'pro' );

			$recovery_format = new SWP_Option_Select( __( 'Previous URL Format', 'social-warfare' ), 'recovery_format' );
			$recovery_format->set_choices( [
				'unchanged'         => __( 'Unchanged', 'social-warfare' ),
				'default'           => __( 'Plain', 'social-warfare' ),
				'day_and_name'      => __( 'Day and Name', 'social-warfare' ),
				'month_and_name'    => __( 'Month and Name', 'social-warfare' ),
				'numeric'           => __( 'Numeric', 'social-warfare' ),
				'post_name'         => __( 'Post Name', 'social-warfare' ),
				'custom'            => __( 'Custom', 'social-warfare' ),
			])
				->set_priority( 20 )
				->set_default( 'unchanged' )
				->set_size( 'sw-col-300' )
				->set_dependency( 'recover_shares', true )
				->set_premium( 'pro' );

			//* recovery_custom_format => recovery_permalink
			$recovery_permalink = new SWP_Option_Text( __( 'Custom Permalink Format', 'social-warfare' ), 'recovery_permalink' );
			$recovery_permalink->set_size( 'sw-col-300' )
				->set_priority( 30 )
				->set_dependency( 'recover_shares' , true )
				->set_premium( 'pro' );

			$recovery_protocol = new SWP_Option_Select( __( 'Previous Connection Protocol', 'social-warfare' ), 'recovery_protocol' );
			$recovery_protocol->set_choices( [
				'unchanged'     => __( 'Unchanged', 'social-warfare' ),
				'http'          => 'http',
				'https'         => 'https'
			])
				->set_size( 'sw-col-300' )
				->set_priority( 40 )
				->set_default( 'unchanged' )
				->set_dependency( 'recover_shares', true )
				->set_premium( 'pro' );

			$recovery_prefix = new SWP_Option_Select( __( 'Previous Domain Prefix', 'social-warfare' ), 'recovery_prefix' );
			$recovery_prefix->set_choices( [
				'unchanged' => __( 'Unchanged', 'social-warfare' ),
				'www'       => 'www',
				'nonwww'    => 'non-www',
			])
				->set_priority( 50 )
				->set_default( 'unchanged' )
				->set_size( 'sw-col-300' )
				->set_dependency( 'recover_shares', true )
				->set_premium( 'pro' );

			$recovery_subdomain = new SWP_Option_Text( __( 'Subdomain', 'social-warfare' ), 'recovery_subdomain' );
			$recovery_subdomain->set_default( '' )
				->set_priority( 60 )
				->set_size( 'sw-col-300' )
				->set_dependency( 'recover_shares', true )
				->set_premium( 'pro' );


			$cross_domain_html = '<div class="sw-grid sw-col-940 sw-fit sw-option-container cross_domain_recovery_description_wrapper">';
				$cross_domain_html .= '<p class="sw-subtitle">If you\'ve migrated your website from one domain to another, fill in these two fields to activate cross-domain share recovery.</p>';
			$cross_domain_html .= '</div>';

			$cross_domain = new SWP_Section_HTML( __( 'Cross Domain', 'social-warfare' ), 'cross_domain_recovery_description' );
			$cross_domain->set_priority( 65 )
				->set_dependency( 'recover_shares', true )
				->set_size( 'sw-col-620' )
				->set_premium( 'pro' )
				->add_HTML( $cross_domain_html );

			$former_domain = new SWP_Option_Text( __( 'Former Domain', 'social-warfare' ), 'former_domain' );
			$former_domain->set_default( '' )
				->set_priority( 70 )
				->set_size( 'sw-col-300'  )
				->set_dependency( 'recover_shares', true )
				->set_premium( 'pro' );

			$current_domain = new SWP_Option_Text( __( 'Current Domain', 'social-warfare' ), 'current_domain' );
			$current_domain->set_default( '' )
				->set_priority( 80 )
				->set_size( 'sw-col-300'  )
				->set_dependency( 'recover_shares', true )
				->set_premium( 'pro' );

		$share_recovery->add_options( [$recover_shares, $recovery_format,
			$recovery_permalink, $recovery_prefix, $recovery_subdomain, $recovery_protocol,
			$cross_domain, $former_domain, $current_domain] );

		$advanced->add_sections( array( $analytics_tracking, $advanced_pinterest, $share_recovery) );

		return $this;
	}


	/**
	* Adds a few more options and sections to the Display tab.
	*
	* @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
	*/
	public function update_display_tab() {
		global $SWP_Options_Page;

		$display = $SWP_Options_Page->tabs->display;

			$order_of_icons = new SWP_Option_Select( __( 'Button Ordering', 'social-warfare' ), 'order_of_icons_method' );
			$order_of_icons->set_priority( 30 )
				->set_choices( [
					'manual'    => __( 'Sort Manually Using Drag & Drop Above', 'social-warfare'),
					'dynamic'   => __( 'Sort Dynamically By Order Of Most Shares', 'social-warfare')
				])
				->set_size( 'sw-col-300' )
				->set_default( 'manual' )
				->set_premium( 'pro' );

		$display->sections->social_networks->add_option( $order_of_icons );

			$emphasize_icon = new SWP_Option_Select( __( 'Emphasize Buttons','social-warfare' ), 'emphasized_icon' );
			$emphasize_icon->set_choices(array(
					'0' 	=> __( 'Don\'t Emphasize Any Buttons','social-warfare' ),
					'1' 	=> __( 'Emphasize the First Button','social-warfare' ),
					'2' 	=> __( 'Emphasize the First Two Buttons','social-warfare' )
				))
				->set_priority( 100 )
				->set_size( 'sw-col-300' )
				->set_default( '0' )
				->set_premium( 'pro' );

		$SWP_Options_Page->tabs->display->sections->social_networks->add_option( $emphasize_icon );

			//* minTotes => minimum_shares
			$minimum_shares = new SWP_Option_Text( __( 'Minimum Shares', 'social-warfare' ), 'minimum_shares' );
			$minimum_shares->set_default( 0 )
				->set_priority( 25 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_premium( 'pro' );

			$delay_share_counts = new SWP_Option_Text( __( 'Delay in Hours', 'social-warfare' ), 'delay_share_counts' );
			$delay_share_counts->set_default( 0 )
				->set_priority( 26 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_premium( 'pro' );

		$display->sections->share_counts->add_option( $minimum_shares );
		$display->sections->share_counts->add_option( $delay_share_counts );

		$meta_tags = new SWP_Options_Page_Section( __( 'Social Meta Tags' , 'social-warfare'), 'meta_tags' );
		$meta_tags->set_description( __( 'Activating Open Graph Tags and Twitter Cards will cause the plugin to output certain meta tags in the head section of your site\'s HTML. Twitter cards are pretty much exactly like Open Graph meta tags, except that there is only one network, Twitter, that looks at them.', 'social-warfare') )
			->set_priority( 30 )
			->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-twitter-cards/' );

				$twitter_card = new SWP_Option_Toggle( __( 'Show Twitter Cards', 'social-warfare' ), 'twitter_cards' );
				$twitter_card->set_default( true )
					->set_priority( 10 )
					->set_size( 'sw-col-300' )
					->set_premium( 'pro' );

				$og_tags = new SWP_Option_Toggle( __( 'Show Open Graph Tags', 'social-warfare' ), 'og_tags' );
				$og_tags->set_default( true )
					->set_priority( 5 )
					->set_size( 'sw-col-300' )
					->set_premium( 'pro' );

		$meta_tags->add_options( [$twitter_card,$og_tags] );

		/* Image Hover Pin Button   */
		$image_hover = new SWP_Options_Page_Section( __( 'Image Hover Pin Button', 'social-warfare' ), 'image_hover' );
		$image_hover->set_description( __( 'If you would like a "Pin" button to appear on images when users hover over them, activate this.', 'social-warfare') )
			->set_priority( 50 )
			->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-image-hover-pin-button/' );

			$pinit_toggle = new SWP_Option_Toggle( __( 'Pinit Button', 'social-warfare' ), 'pinit_toggle' );
			$pinit_toggle->set_default( false )
				->set_size( 'sw-col-300' )
				->set_priority( 10 )
				->set_premium( 'pro' );

			$pinit_location_horizontal = new SWP_Option_Select( __( 'Horizontal Location', 'social-warfare' ), 'pinit_location_horizontal' );
			$pinit_location_horizontal->set_priority( 20 )
				->set_choices( [
					'left'   => __( 'Left', 'social-warfare' ) ,
					'center' => __( 'Center', 'social-warfare' ),
					'right'  => __( 'Right', 'social-warfare' )
				])
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_default( 'center' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );

			$pinit_min_width = new SWP_Option_Text( __( 'Min Width', 'social-warfare' ), 'pinit_min_width' );
			$pinit_min_width->set_default( '200' )
				->set_priority( 30 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_placeholder( '200' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );

			$pinit_location_vertical = new SWP_Option_Select( __( 'Vertical Location', 'social-warfare' ), 'pinit_location_vertical' );
			$pinit_location_vertical->set_priority( 40 )
				->set_choices( [
					'top'    => __( 'Top', 'social-warfare' ),
					'middle' => __( 'Middle', 'social-warfare' ),
					'bottom' => __( 'Bottom', 'social-warfare' )
				])
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_default( 'middle' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );

			$pinit_min_height = new SWP_Option_Text( __( 'Min Height', 'social-warfare' ), 'pinit_min_height' );
			$pinit_min_height->set_default( '200' )
				->set_priority( 50 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_placeholder( '200' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );

			$pinit_image_source = new SWP_Option_Select( __( 'Image Source', 'social-warfare' ), 'pinit_image_source' );
			$pinit_image_source->set_priority( 60 )
				->set_choices( [
					'image'    => __( 'Pin the Image' , 'social-warfare' ) ,
					'custom' => __( 'Pin the Custom Pin Image' , 'social-warfare' )
				])
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_default( 'image' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );

			$pinit_button_size = new SWP_Option_Select( __( 'Button Size', 'social-warfare' ), 'pinit_button_size' );
			$pinit_button_size->set_priority( 70 )
				->set_choices( [
					'0.9' => __( '90%' , 'social-warfare' ),
					'1'   => __( '100%' , 'social-warfare' ),
					'1.1'   => __( '110%' , 'social-warfare' ),
					'1.2'   => __( '120%' , 'social-warfare' ),
					'1.3'   => __( '130%' , 'social-warfare' ),
					'1.4'   => __( '140%' , 'social-warfare' ),
					'1.5'   => __( '150%' , 'social-warfare' )
				])
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_default( '1' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );



			$pinit_hide_on_anchors = new SWP_Option_Toggle( __( 'Hide on Anchors (links)', 'social-warfare'), 'pinit_hide_on_anchors' );
			$pinit_hide_on_anchors->set_priority( 80 )
				->set_default( false )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_dependency( 'pinit_toggle', [true] )
				->set_premium( 'pro' );

		$image_hover->add_options( [$pinit_toggle,
			$pinit_location_horizontal,
			$pinit_location_vertical,
			$pinit_image_source,
			$pinit_min_width,
			$pinit_min_height,
			$pinit_button_size,
			$pinit_hide_on_anchors
		] );

		$yummly_display = new SWP_Options_Page_Section( __( 'Yummly Display Control', 'social-warfare' ), 'yummly_display' );
		$yummly_display->set_description( __( 'If you would like the Yummly button to only display on posts of a specific category or tag, enter the category or tag name below (e.g "Recipe"). Leave blank to display the button on all posts.', 'social-warfare') )
			->set_priority( 60 )
			->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-yummly-display-control/' );

			$yummly_table = new SWP_Section_HTML( __( 'Yummly Table', 'social-warfare' ) );
			$yummly_table->do_yummly_display();

			$yummly_display->add_option( $yummly_table );

		$powered_by = new SWP_Options_Page_Section( __( 'Promote Social Warfare', 'social-warfare' ), 'powered_by' );
		$powered_by->set_priority( 90 )
			->set_description( __( 'If you\'d like, you can add a very small "Powered by Social Warfare" to the bottom of the "More" button overlay, "Pinterest" multi-image overlay, and (coming soon) site footer linking to our site with your affiliate link.', 'social-warfare' ) );

			//* totes => totals
			$powered_by_toggle = new SWP_Option_Toggle( __( 'Display "Powered By"?', 'social-warfare' ), 'powered_by_toggle' );
			$powered_by_toggle->set_default( false )
				->set_priority( 10 )
				->set_size( 'sw-col-460', 'sw-col-460' );

			$affiliate_link = new SWP_Option_Text( __( 'Affiliate Link', 'social-warfare' ), 'affiliate_link' );
			$affiliate_link->set_size( 'sw-col-460', 'sw-col-460' )
				->set_priority( 20 )
				->set_default( '' );

			$powered_by->add_options( [$powered_by_toggle, $affiliate_link] );


		$display->add_sections( [$meta_tags, $image_hover, $yummly_display, $powered_by] );

		return $this;
	}


	/**
	* Adds a few more options and sections to the Social Identity tab.
	*
	* @since  UNKNOWN | UNKNOWN | Created
	* @since  4.0.0 | 25 FEB 2020 | Added OpenShareCount as a twitter count source.
	* @param  void
	* @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
	*/
	public function update_social_tab() {
		global $SWP_Options_Page;

		$social_identity = $SWP_Options_Page->tabs->social_identity;

			$open_graph = new SWP_Options_Page_Section( __( 'Open Graph og:type Values', 'social-warfare' ), 'open_graph' );
			$open_graph->set_description( __( 'These options allow you to control which value you would like to use for the Open Graph og:type tag for each post type.', 'social-warfare') )
				->set_priority( 20 );

			$og_post_types = $this->get_og_post_types();
			$choices = array();

			foreach( $og_post_types as $type) {
				$choices['og_' . $type] = $type;
			}

			$default_types = ['page', 'post'];
			$post_types = array_merge( $default_types, get_post_types( ['public' => true, '_builtin' => false ], 'names' ) );
			$count = 1;

			//* Assign the hard-coded custom post types as options for the
			//* registered post types.
			foreach( $post_types as $index => $type ) {
				$priority = ( ( $count ) * 10 );
				$option = new SWP_Option_Select( ucfirst( str_replace( 'swp_og_type_', '', $type ) ), 'og_' . $type );
				$option->set_priority( $priority )
					->set_size( 'sw-col-300' )
					->set_choices( $choices )
					->set_default( 'article' )
					->set_premium( 'pro' );

				$open_graph->add_option( $option );
				$count++;
			}

			$tweet_count_registration = new SWP_Options_Page_section( __( 'Tweet Count Registration', 'social-warfare' ), 'activate_tweet_counts' );
			$tweet_count_registration->set_description( __( "In order to allow Social Warfare to track tweet counts, we've partnered with a couple of third-party share counting tools. Follow the steps below to register with one of these platforms and allow us to track your Twitter shares.", 'social-warfare') )
				->set_information_link( 'https://warfareplugins.com/support/configuring-twitter-counts/' );

			$tweet_activation = new SWP_Section_HTML( __( 'Tweet Activation', 'social-warfare'), 'tweet_activation' );
			$tweet_activation->set_priority( 10 )
				->set_default( false )
				->set_premium( 'pro' );
			$tweet_activation->do_tweet_count_registration();


			//$twitter_shares = new SWP_Option_Toggle( __( 'Tweet Counts' ,'social-warfare' ), 'twitter_shares' );
			//$twitter_shares->set_default( false )
			//    ->set_priority( 20 )
			//    ->set_premium( 'pro' );

			$tweet_count_source = new SWP_Option_Select( __( 'Tweet Count Source', 'social-warfare' ), 'tweet_count_source' );
			$tweet_count_source->set_choices( array(
					'twitcount'         => __( 'TwitCount.com' , 'social-warfare'),
					'opensharecount'    => __( 'OpenShareCount.com' , 'social-warfare')
				) )
				->set_default( 'opensharecount' )
				->set_priority( 30 )
				->set_size( 'sw-col-300' )
				->set_premium( 'pro' );

			$tweet_count_registration->set_priority( 100 )
				->add_options( [$tweet_activation, $tweet_count_source] );


		$social_identity->add_sections( [$open_graph, $tweet_count_registration ]);

		return $this;
	}

	/**
	* Adds a few more options and sections to the Styles tab.
	*
	* @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
	*/
	public function update_styles_tab() {
		global $SWP_Options_Page;

		$styles = $SWP_Options_Page->tabs->styles;

		$visual_options = new SWP_Options_Page_Section( __( 'Visual Options', 'social-warfare' ), 'visual_options' );
		$visual_options->set_description( __( 'Use the settings below to customize the look of your share buttons.', 'social-warfare') )
			->set_priority( 10 )
			->set_information_link( 'https://warfareplugins.com/support/options-page-styles-tab-visual-options/' )
			->set_premium( 'pro' );

			//* visualTheme => button_shape
			$button_shape = new SWP_Option_Select( __( 'Button Shape', 'social-warfare' ), 'button_shape' );
			$button_shape->set_choices( [
				'flat_fresh' => __( 'Flat & Fresh', 'social-warfare' ),
				'leaf'       => __( 'A Leaf on the Wind', 'social-warfare' ),
				'shift'      => __( 'Shift', 'social-warfare' ),
				'pill'       => __( 'Pills', 'social-warfare' ),
				'three_dee'  => __( 'Three-Dee', 'social-warfare' ),
				'connected'  => __( 'Connected', 'social-warfare' ),
				'modern'     => __( 'Modern', 'social-warfare' ),
				'dark'  => __( 'Dark', 'social-warfare' )
				] )
				->set_default( 'flat_fresh' )
				->set_priority( 10 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_premium( 'pro' );

			//* buttonSize => button_size
			$button_size = new SWP_Option_Select( __( 'Button Size', 'social-warfare' ), 'button_size' );
			$button_size->set_choices( [
				'1.4' => '140%',
				'1.3' => '130%',
				'1.2' => '120%',
				'1.1' => '110%',
				'1'     => '100%',
				'0.9'   => '90%',
				'0.8'   => '80%',
				'0.7'   => '70%'
				] )
				->set_default( '1' )
				->set_priority( 20 )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_premium( 'pro' );

			$color_choices = SWP_Options_Page::get_color_choices_array();

			//* dColorSet => default_colors
			$default_colors = new SWP_Option_Select( __( 'Default Color Set', 'social-warfare' ), 'default_colors' );
			$default_colors->set_choices( $color_choices )
				->set_default( 'full_color' )
				->set_priority( 30 )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_premium( 'pro' );

			//* oColorSet => hover_colors
			$hover_colors = new SWP_Option_Select( __( 'Hover Color Set', 'social-warfare' ), 'hover_colors' );
			$hover_colors->set_choices( $color_choices )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_default( 'full_color' )
				->set_priority( 40 )
				->set_premium( 'pro' );


			//* iColorSet => single_colors
			$single_colors = new SWP_Option_Select( __( 'Single Button Hover', 'social-warfare' ), 'single_colors' );
			$single_colors->set_choices( $color_choices )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_default( 'full_color' )
				->set_priority( 50 )
				->set_premium( 'pro' );

			//* button_alignment => button_alignment
			$button_alignment = new SWP_Option_Select( __( 'Button Alignment', 'social-warfare' ), 'button_alignment' );
			$button_alignment->set_choices( [
				'full_width' => __( 'Full Width', 'social_warfare'),
				'left'       => __( 'Left', 'social_warfare'),
				'right'      => __( 'Right', 'social_warfare'),
				'center'     => __( 'Center', 'social_warfare')
				] )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_priority( 60 )
				->set_default( 'full_width' )
				->set_dependency( 'button_size', [ '0.7', '0.8', '0.9'] )
				->set_premium( 'pro' );

		$visual_options->add_options( [$button_shape, $button_size, $default_colors,
			$hover_colors, $single_colors, $button_alignment,
		] );

		$floating_share_buttons = $styles->sections->floating_share_buttons;

			$float_size = new SWP_Option_Select( __( 'Float Size', 'social-warfare' ), 'float_size' );
			$float_size->set_choices( [
				'1.4' => '140%',
				'1.3' => '130%',
				'1.2' => '120%',
				'1.1' => '110%',
				'1'   => '100%',
				'0.9' => '90%',
				'0.8' => '80%',
				'0.7' => '70%'
				] )
				->set_default( '1' )
				->set_priority( 35 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_dependency( 'float_location', ['left', 'right'] )
				->set_premium( 'pro' );

			$float_alignment = new SWP_Option_Select( __( 'Float Alignment', 'social-warfare' ), 'float_alignment' );
			$float_alignment->set_choices( [
				'top'       => __('Near the top of the page'  , 'social-warfare' ) ,
				'center'    => __( 'Centered on the page' , 'social-warfare' ),
				'bottom'    => __( 'Near the bottom of the page' , 'social-warfare' )
				] )
				->set_default( 'center' )
				->set_priority( 25 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_dependency( 'float_location', ['left', 'right'] )
				->set_premium( 'pro' );

			$float_mobile = new SWP_Option_Select( __( 'On Mobile', 'social-warfare' ), 'float_mobile' );
			$float_mobile->set_choices( [
				'top'    => __( 'Top of Screen', 'social_warfare'),
				'bottom' => __( 'Bottom of Screen', 'social_warfare'),
				'off'    => __( 'Off', 'social_warfare'),
			])
				->set_priority( 40 )
				->set_default( 'bottom' )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
				->set_dependency( 'floating_panel', [true] )
				->set_premium( 'pro' );

			//* floatStyle => float_button_shape
			$float_button_shape = new SWP_Option_Select( __( 'Button Shape', 'social-warfare' ), 'float_button_shape' );
			$float_button_shape->set_choices( [
				'default' => __( 'Buttons' , 'social-warfare' ),
				'boxed'   => __( 'Boxes' , 'social-warfare' ),
				'circles' => __( 'Circles' , 'social-warfare' ),
				])
				->set_default( 'boxed' )
				->set_priority( 45 )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_dependency( 'float_location', ['left', 'right'] )
				->set_premium( 'pro' );

			$float_button_count = new SWP_Option_Text( __( 'Number of Buttons', 'social-warfare'), 'float_button_count' );

			$float_button_count->set_default( 5 )
				->set_priority( 46 )
				->set_size( 'sw-col-460', 'sw-col-460  sw-fit' )
				->set_dependency( 'float_location', ['left', 'right'] )
				->set_premium( 'pro' );

			//* floatStyleSource => float_style_source
			$float_style_source = new SWP_Option_Toggle( __( 'Inherit Visual Options', 'social-warfare' ), 'float_style_source' );
			$float_style_source->set_default( true )
				->set_priority( 50 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_dependency( 'float_location', ['left', 'right'] )
				->set_premium( 'pro' );

			unset($color_choices['custom_color']);
			unset($color_choices['custom_color_outlines']);
			$color_choices['float_custom_color'] = __( 'Custom Color', 'social-warfare' );
			$color_choices['float_custom_color_outlines'] = __( 'Custom Color Outlines', 'social-warfare' );

			//* sideDColorSet => float_default_colors
			$float_default_colors = new SWP_Option_Select( __( 'Default Color Set', 'social-warfare' ), 'float_default_colors' );
			$float_default_colors->set_choices( $color_choices )
				->set_default( 'full_color' )
				->set_priority( 60 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_dependency( 'float_style_source', [false] );

			//* sideOColorSet => float_hover_colors
			$float_hover_colors = new SWP_Option_Select( __( 'Hover Color Set', 'social-warfare' ), 'float_hover_colors' );
			$float_hover_colors->set_priority( 80 )
				->set_default('full_color')
				->set_choices( $color_choices )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_dependency( 'float_style_source', [false] );

			//* sideIColorSet => float_single_colors
			$float_single_colors = new SWP_Option_Select( __( 'Single Button Hover', 'social-warfare' ), 'float_single_colors' );
			$float_single_colors->set_priority( 90 )
				->set_default('full_color')
				->set_choices( $color_choices )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_dependency( 'float_style_source', [false] );


			//* These are all of the custom color fields. Right now their dependency is
			//* not set up in by the conventional method. They are being patched with
			//* temporary Javascript.


			//* PANEL CUSTOM COLOR *//

			//* sideCustomColor => float_custom_color
			$custom_color = new SWP_Option_Text( __( 'Custom Color', 'social-warfare' ), 'custom_color' );
			$custom_color->set_default( '#ced3dc' )
				->set_priority( 100 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_placeholder( "#f4e2d7" )
				->set_premium( 'pro' );

			//* sideCustomColor => float_custom_color
			$custom_color_outlines = new SWP_Option_Text( __( 'Custom Outlines', 'social-warfare' ), 'custom_color_outlines' );
			$custom_color_outlines->set_default( '#ced3dc' )
				->set_priority( 110 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_placeholder( "#c43ad4" )
				->set_premium( 'pro' );

			$visual_options->add_options( [$custom_color, $custom_color_outlines] );

			//* FLOAT CUSTOM COLOR *//

			//* sideCustomColor => float_custom_color
			$float_custom_color = new SWP_Option_Text( __( 'Custom Color', 'social-warfare' ), 'float_custom_color' );
			$float_custom_color->set_default( '#ced3dc' )
				->set_priority( 120 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_placeholder("#f445a4")
				->set_premium( 'pro' );

			//* sideCustomColor => float_custom_color
			$float_custom_color_outlines = new SWP_Option_Text( __( 'Custom Outlines', 'social-warfare' ), 'float_custom_color_outlines' );
			$float_custom_color_outlines->set_default( '#ced3dc' )
				->set_priority( 130 )
				->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
				->set_placeholder("#d3d654")
				->set_premium( 'pro' );

		$floating_share_buttons->add_options( [$float_size, $float_alignment, $float_button_shape, $float_button_count, $float_style_source,
			$float_mobile, $float_custom_color, $float_custom_color_outlines,
			$float_default_colors, $float_hover_colors, $float_single_colors,
		] );

		$click_to_tweet = new SWP_Options_Page_Section( __( 'Click-To-Tweet Style', 'social-warfare' ), 'click_to_tweet' );
		$click_to_tweet->set_description( __('Select the default visual style for Click-to-Tweets on your site.', 'social-warfare') )
			->set_information_link( 'https://warfareplugins.com/support/options-page-styles-tab-click-tweet-style/' )
			->set_priority( 40 );

			//* cttTheme => ctt_theme
			$ctt_theme = new SWP_Option_Select( __( 'Visual Theme', 'social-warfare' ), 'ctt_theme' );
			$ctt_theme->set_choices( [
				'style1' => 'Send Her My Love',
				'style2' => 'Roll With The Changes',
				'style3' => 'Free Bird',
				'style4' => 'Don\'t Stop Believin\'',
				'style5' => 'Thunderstruck',
				'style6' => 'Livin\' On A Prayer',
				'style7' => 'You\'re The Inspiration',
				'none' => __( 'None - Create Your Own CSS In Your Theme', 'social-warfare')
			])
				->set_size( 'sw-col-300' )
				->set_default( 'style1' )
				->set_premium( 'pro' )
				->set_priority( 10 );

			//* cttCSS => ctt_css
			$ctt_css = new SWP_Option_Textarea( __( 'Custom CSS', 'social-warfare' ), 'ctt_css' );
			$ctt_css->set_dependency( 'ctt_theme', 'none' )
				->set_premium( 'pro' )
				->set_size( 'sw-col-300' )
				->set_priority( 20 );

			//* cttPreview => ctt_preview
			//* NOTE: This key is currently kept as the old key for
			//* CSS and JS compatability.
			$ctt_preview = new SWP_Section_HTML( __( 'Click To Tweet Preview', 'social-warfare' ), 'ctt_preview' );
			$ctt_preview->do_ctt_preview()
				->set_premium( 'pro' )
				->set_priority( 30 );

		$click_to_tweet->add_options( [$ctt_theme, $ctt_css, $ctt_preview] );

		$styles->add_sections( [$visual_options, $click_to_tweet] );

		return $this;
	}
}

endif;
