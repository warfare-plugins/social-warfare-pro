<?php
//* For options whose database name has changed, it is notated as follows:
//* prevOption => new_option
//* @see SWP_Database_Migration

class SWP_Pro_Options_Page extends SWP_Options_Page {
    /**
    * Reference to the global SWP_Options_Page object.
    * SWP_Options_Page $core
    */

	public function __construct() {

		global $SWP_Options_Page;
        $this->core = $SWP_Options_Page;

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
        $advanced = $this->core->tabs->advanced;

        $bitly = new SWP_Options_Page_Section( 'Bitly Link Shortening' );
        $bitly->set_description( 'If you like to have all of your links automatically shortened, turn this on.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-bitly-link-shortening/' )
            ->set_priority( 20 );

            //* linkShortening => bitly_authentication
            $bitly_authentication = new SWP_Option_Toggle( 'Bitly Link Shortening', 'bitly_authentication' );
            $bitly_authentication->set_size( 'sw-col-300' )
                ->set_priority( 10 )
                ->set_default( false )
                ->set_premium( 'pro' );

            $bitly_connection = new SWP_Section_HTML( 'Connect Your Bitly Account' );
            $bitly_connection->set_priority( 20 )
                ->set_premium( 'pro' )
                ->do_bitly_authentication_button();

            $bitly->add_options( [$bitly_authentication, $bitly_connection] );


        $analytics_tracking = new SWP_Options_Page_Section( 'Analytics Tracking' );
        $analytics_tracking->set_description( 'If you want to activate UTM tracking for shared URL, turn this on.' )
            ->set_priority( 30 );

            //* swp_click_tracking => click_tracking
            $click_tracking = new SWP_Option_Toggle( 'Button Click Tracking', 'click_tracking' );
            $click_tracking->set_priority( 10 )
                ->set_size( 'sw-col-300' )
                ->set_default( false )
                ->set_premium( 'pro' );

            //* googleAnalytics => google_analytics
            $google_analytics = clone $click_tracking;
            $google_analytics->set_name( 'UTM Link Tracking' )
                ->set_key( 'google_analytics' )
                ->set_priority( 20 );

            //* analyticsMedium => analytics_medium
            $analytics_medium = new SWP_Option_Text( 'UTM Medium', 'analytics_medium' );
            $analytics_medium->set_default( 'Social' )
                ->set_priority( 30 )
                ->set_dependency( 'google_analytics', true )
                ->set_premium( 'pro');

            $analytics_campaign = clone $analytics_medium;
            $analytics_campaign->set_name( 'UTM Campaign' )
                ->set_key( 'analytics_campaign ')
                ->set_priority( 40 )
                ->set_default( 'SocialWarfare' );

            $utm_on_pins = new SWP_Option_Toggle( 'UTM Tracking on Pins', 'utm_on_pins' );
            $utm_on_pins->set_default( false )
                ->set_priority( 50 )
                ->set_size( 'sw-col-300' )
                ->set_dependency( 'google_analytics', true )
                ->set_premium( 'pro' );

                $analytics_tracking->add_option( $utm_on_pins );

            $analytics_tracking->add_options( [$click_tracking, $google_analytics, $analytics_medium,
                $analytics_campaign, $utm_on_pins] );

        $advanced_pinterest = new SWP_Options_Page_Section( 'Advanced Pinterest Settings ');
        $advanced_pinterest->set_description( 'Get maximum control over how your visitors are sharing your content on Pinterest.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-advanced-pinterest-settings/' )
            ->set_priority( 40 )
            ->set_premium( 'pro' );

            //* advanced_pinterest_image => pin_browser_extension
            $pin_browser_extension = new SWP_Option_Toggle( 'Pinterest Image for Browser Extensions', 'pin_browser_extension' );
            $pin_browser_extension->set_default( false )
                ->set_size( 'sw-col-300')
                ->set_premium( 'pro' );

            //* advanced_pinterest_image_location => pinterest_image_location
            $pinterest_image_location = new SWP_Option_Select( 'Pinterest Image Location', 'pinterest_image_location' );
            $pinterest_image_location->set_choices( [
                'hidden'    => 'Hidden',
                'top'       => 'At the top of each post.',
                'bottom'    => 'At the bottom of each post.'
            ])
                ->set_default( 'hidden ')
                ->set_size( 'sw-col-300' )
                ->set_dependency( 'pin_browser_extension', true )
                ->set_premium( 'pro' );

            //* advanced_pinterest_fallback => pinterest_fallback
            $pinterest_fallback = new SWP_Option_Select( 'Pinterest Image Fallback', 'pinterest_fallback' );
            $pinterest_fallback->set_choices( [
                'all'   => 'Show a selection of all images on the page.',
                'featured'  => 'Show my featured image.'
            ])
                ->set_size( 'sw-col-300' )
                ->set_default( 'all' )
                ->set_premium( 'pro' );

        $advanced_pinterest->add_options( [$pin_browser_extension, $pinterest_image_location, $pinterest_fallback] );

        $share_recovery = new SWP_Options_Page_Section( 'Share Recovery' );
        $share_recovery->set_description( 'If at any point you have changed permalink structures or have gone from http to https (SSL) then you will have undoubtedly lost all of your share counts. This tool allows you to recover them. See <a target="_blank" href="https://warfareplugins.com/support/recover-social-share-counts-after-changing-permalink-settings/">this guide</a> for more detailed instructions on how to use this feature.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-share-recovery/' )
            ->set_priority( 50 )
            ->set_premium( 'pro' );

            $recover_shares = new SWP_Option_Toggle( 'Activate Share Recovery', 'recover_shares' );
            $recover_shares->set_default( false )
                ->set_priority( 10 )
                ->set_size( 'sw-col-300' )
                ->set_premium( 'pro' );

            $recovery_format = new SWP_Option_Select( 'Previous URL Format', 'recovery_format' );
            $recovery_format->set_choices( [
                'unchanged'         => 'Unchanged',
                'default'           => 'Plain',
                'day_and_name'      => 'Day and Name',
                'month_and_name'    => 'Month and Name',
                'numeric'           => 'Numeric',
                'post_name'         => 'Post Name',
                'custom'            => 'Custom'
            ])
                ->set_priority( 20 )
                ->set_default( 'unchanged' )
                ->set_size( 'sw-col-300' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            //* recovery_custom_format => recovery_permalink
            $recovery_permalink = new SWP_Option_Text( 'Custom Permalink Format', 'recovery_permalink' );
            $recovery_permalink->set_size( 'sw-col-300' )
                ->set_priority( 30 )
                ->set_dependency( 'recover_shares' , true )
                ->set_premium( 'pro' );

            $recovery_protocol = new SWP_Option_Select( 'Previous Connection Protocol', 'recovery_protocol' );
            $recovery_protocol->set_choices( [
                'unchanged'     => 'Unchanged',
                'http'  => 'http',
                'https' => 'https'
            ])
                ->set_priority( 40 )
                ->set_default( 'unchanged' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            $recovery_prefix = new SWP_Option_Select( 'Previous Domain Prefix', 'recovery_prefix' );
            $recovery_prefix->set_choices( [
                'Unchanged' => 'Unchanged',
                'www'       => 'www',
                'nonwww'    => 'non-www',
            ])
                ->set_priority( 50 )
                ->set_default( 'unchanged' )
                ->set_size( 'sw-col-300' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            $recovery_subdomain = new SWP_Option_Text( 'Subdomain', 'recovery_subdomain' );
            $recovery_subdomain->set_default( '' )
                ->set_priority( 60 )
                ->set_size( 'sw-col-300' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );


            $cross_domain_html = '<div class="sw-grid sw-col-940 sw-fit sw-option-container cross_domain_recovery_description_wrapper" </div>';
                $cross_domain_html .= '<p class="sw-subtitle">If you\'ve migrated your website from one domain to another, fill in these two fields to activate cross-domain share recovery.</p>';
            $cross_domain_html .= '</div>';

            $cross_domain = new SWP_Section_HTML( 'Cross Domain', 'cross_domain_recovery_description' );
            $cross_domain->set_priority( 65 )
                ->set_dependency( 'recover_shares', true )
                ->set_size( 'sw-col-620' )
                ->set_premium( 'pro' )
                ->add_HTML( $cross_domain_html );

            $former_domain = new SWP_Option_Text( 'Former Domain', 'former_domain' );
            $former_domain->set_default( '' )
                ->set_priority( 70 )
                ->set_size( 'sw-col-300'  )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            $current_domain = clone $former_domain;
            $current_domain->set_name( 'Current Domain ')
                ->set_key( 'current_domain' );

        $share_recovery->add_options( [$recover_shares, $recovery_format,
            $recovery_permalink, $recovery_prefix, $recovery_subdomain,
            $cross_domain, $former_domain, $current_domain] );

        $advanced->add_sections( [$bitly, $analytics_tracking, $advanced_pinterest, $share_recovery] );

        return $this;
    }


    /**
    * Adds a few more options and sections to the Display tab.
    *
    * @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
    */
    public function update_display_tab() {
        $display = $this->core->tabs->display;

            $order_of_icons = new SWP_Option_Select( 'Button Ordering', 'order_of_icons' );
            $order_of_icons->set_priority( 30 )
                ->set_choices( [
                    'manual'    => 'Sort Manually Using Drag & Drop Above' ,
                    'dynamic'   => 'Sort Dynamically By Order Of Most Shares'
                ])
                ->set_size( 'sw-col-460')
                ->set_default( 'manual' )
                ->set_premium( 'pro' );

        $display->sections->social_networks->add_option( $order_of_icons );

            //* minTotes => minimum_shares
            $minimum_shares = new SWP_Option_Text( 'Minimum Shares', 'minimum_shares' );
            $minimum_shares->set_default( 0 )
                ->set_priority( 30 )
                ->set_size( 'sw-col-300' )
                ->set_premium( 'pro' );

        $display->sections->share_counts->add_option( $minimum_shares );

        $twitter_cards = new SWP_Options_Page_Section( 'Twitter Cards' );
        $twitter_cards->set_description( 'Activating Twitter Cards will cause the plugin to output certain meta tags in the head section of your site\'s HTML. Twitter cards are pretty much exactly like Open Graph meta tags, except that there is only one network, Twitter, that looks at them.' )
            ->set_priority( 30 )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-twitter-cards/' );

                $twitter_card = new SWP_Option_Toggle( 'Show Twitter Cards', 'twitter_cards' );
                $twitter_card->set_default( true )
                    ->set_priority( 10 )
                    ->set_size( 'sw-col-300' )
                    ->set_premium( 'pro' );

        $twitter_cards->add_option( $twitter_card );

        /* Image Hover Pin Button   */
        $image_hover = new SWP_Options_Page_Section( 'Image Hover Pin Button' );
        $image_hover->set_description( 'If you would like a "Pin" button to appear on images when users hover over them, activate this.' )
            ->set_priority( 50 )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-image-hover-pin-button/' );

            $pinit_toggle = new SWP_Option_Toggle( 'Pinit Button', 'pinit_toggle' );
            $pinit_toggle->set_default( true )
                ->set_size( 'sw-col-300' )
                ->set_premium( 'pro' );

        $image_hover->add_option( $pinit_toggle );

        $yummly_display = new SWP_Options_Page_Section( 'Yummy Display Control' );
        $yummly_display->set_description( 'If you would like the Yummly button to only display on posts of a specific category or tag, enter the category or tag name below (e.g "Recipe"). Leave blank to display the button on all posts.' )
            ->set_priority( 60 )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-yummly-display-control/' );

            $yummly_table = new SWP_Section_HTML( 'Yummly Table' );
            $yummly_table->do_yummly_display();

            $yummly_display->add_option( $yummly_table );

        $display->add_sections( [$twitter_cards, $image_hover, $yummly_display] );

        return $this;
    }


    /**
    * Adds a few more options and sections to the Social Identity tab.
    *
    * @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
    */
    public function update_social_tab() {
        $social_identity = $this->core->tabs->social_identity;

            $open_graph = new SWP_Options_Page_Section( 'Open Graph og:type Values');
            $open_graph->set_description( 'These options allow you to control which value you would like to use for the Open Graph og:type tag for each post type.' )
                ->set_priority( 20 );

            $custom_post_types = $this->get_custom_post_types();
            $choices = [];

            foreach( $custom_post_types as $type) {
                $choices[$type] = $type;
            }

            $default_types = ['swp_og_type_page', 'swp_og_type_post'];
            $post_types = array_merge( $default_types, get_post_types( ['public' => true, '_builtin' => false ], 'names' ) );

            //* Assign the hard-coded custom post types as options for the
            //* registered post types.
            foreach( $post_types as $index => $type ) {
                $priority = ( ( $index + 1 ) * 10 );
                $option = new SWP_Option_Select( ucfirst( str_replace( 'swp_og_type_', '', $type ) ), $type );
                $option->set_priority( $priority )
                    ->set_size( 'sw-col-300' )
                    ->set_choices( $choices )
                    ->set_default( 'article' )
                    ->set_premium( 'pro' );

                $open_graph->add_option( $option );
            }

        $social_identity->add_section( $open_graph );

        return $this;
    }

    /**
    * Adds a few more options and sections to the Styles tab.
    *
    * @return SWP_Pro_Options_Page $this The calling instance, for method chaining.
    */
    public function update_styles_tab() {
        $styles = $this->core->tabs->styles;

        //* visualTheme => button_shape
        $button_shape = new SWP_Option_Select( 'Button Shape', 'button_shape' );
        $button_shape->set_choices( [
            'flat_fresh'=> 'Flat & Fresh',
            'leaf'     => 'A Leaf on the Wind',
            'shift'     => 'Shift',
            'pill'      => 'Pills',
            'three_dee' => 'Three-Dee',
            'connected'  => 'Connected',
            'boxed'     => 'Boxed'
            ] )
            ->set_default( 'flat_fresh' )
            ->set_priority( 10 )
            ->set_size( 'sw-col-460' )
            ->set_premium( 'pro' );

        //* buttonSize => button_size
        $button_size = new SWP_Option_Select( 'Button Size', 'button_size' );
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
            ->set_size( 'sw-col-460' )
            ->set_premium( 'pro' );

        $color_choices = SWP_Options_Page::get_color_choices_array();

        $visual_options = $styles->sections->visual_options;

            //* dColorSet => default_colors
            $default_colors = new SWP_Option_Select( 'Default Color Set', 'default_colors' );
            $default_colors->set_choices( $color_choices )
                ->set_default( 'full_color' )
                ->set_priority( 30 )
                ->set_size( 'sw-col-460' )
                ->set_premium( 'pro' );

            //* oColorSet => hover_colors
            $hover_colors = clone $default_colors;
            $hover_colors->set_name( 'Hover Color Set')
                ->set_key( 'hover_colors' )
                ->set_priority( 40 );

            //* iColorSet => single_colors
            $single_colors = clone $default_colors;
            $single_colors->set_name( 'Single Button Hover' )
                ->set_key( 'single_colors' )
                ->set_priority( 50 );

            //* buttonFloat => button_alignment
            $button_alignment = new SWP_Option_Select( 'Button Alignment', 'button_alignment' );
            $button_alignment->set_choices( [
                //* fullWidth -> full_width
                'full_width' => 'Full Width',
                'left'      => 'Left',
                'right'     => 'Right',
                'center'    => 'Center'
            ])
                ->set_size( 'sw-col-460' )
                ->set_priority( 60 )
                ->set_default( 'full_width' )
                ->set_dependency( 'button_size', [ '0.7', '0.8', '0.9'] )
                ->set_premium( 'pro' );

        $visual_options->add_options( [$button_shape, $button_size, $default_colors, $default_colors, $single_colors, $button_alignment] );

        $floating_share_buttons = $styles->sections->floating_share_buttons;

            $float_mobile = new SWP_Option_Select( 'On Mobile', 'float_mobile' );
            $float_mobile->set_choices( [
                'bottom'=> 'Bottom of Screen',
                'off'   => 'Off'
            ])
                ->set_priority( 40 )
                ->set_default( 'bottom' )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
                ->set_dependency( 'float_location', ['left', 'right'] )
                ->set_premium( 'pro' );

            //* floatStyle => float_button_shape
            $float_button_shape = new SWP_Option_Select( 'Button Shape', 'float_button_shape' );
            $float_button_shape->set_choices( [
                'default'   => 'Buttons',
                'boxed'     => 'Boxes',
                ])
                ->set_default( 'boxed' )
                ->set_priority( 45 )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit' )
                ->set_dependency( 'float_location', ['left', 'right'] )
                ->set_premium( 'pro' );

            //* floatStyleSource => float_style_source
            $float_style_source = new SWP_Option_Toggle( 'Inherit Visual Options', 'float_style_source' );
            $float_style_source->set_default( true )
                ->set_priority( 50 )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
                ->set_dependency( 'float_location', ['left', 'right'] )
                ->set_premium( 'pro' );

            //* sideDColorSet => float_default_colors
            $float_default_colors = new SWP_Option_Select( 'Default Color Set', 'default_colors' );
            $float_default_colors->set_choices( $color_choices )
                ->set_default( 'full_color' )
                ->set_priority( 60 )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
                ->set_dependency( 'float_style_source', [true] );

            //* sideOColorSet => float_hover_colors
            $float_hover_colors = new SWP_Option_Select( 'Hover Color Set', 'float_hover_colors' );
            $float_hover_colors->set_priority( 80 )
                ->set_choices( $color_choices )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
                ->set_dependency( 'float_style_source', [true] );

            //* sideIColorSet => float_single_colors
            $float_single_colors = new SWP_Option_Select( 'Single Button Hover', 'float_single_colors' );
            $float_single_colors->set_priority( 90 )
                ->set_choices( $color_choices )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
                ->set_dependency( 'float_style_source', [true] );

            //* sideCustomColor => float_custom_color
            $float_custom_color = new SWP_Option_Text( 'Custom Color', 'float_custom_color' );
            $float_custom_color->set_default( '#ced3dc' )
                ->set_priority( 100 )
                ->set_size( 'sw-col-460', 'sw-col-460 sw-fit')
                ->set_dependency( 'float_style_source', [false] )
                ->set_premium( 'pro' );




        $floating_share_buttons->add_options( [$float_button_shape, $float_style_source, $float_mobile, $float_custom_color,
            $float_default_colors, $float_hover_colors, $float_single_colors,] );

        $click_to_tweet = new SWP_Options_Page_Section( 'Click-To-Tweet Style' );
        $click_to_tweet->set_description( 'Select the default visual style for Click-to-Tweets on your site.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-styles-tab-click-tweet-style/' )
            ->set_priority( 40 );

            //* cttTheme => ctt_theme
            $ctt_theme = new SWP_Option_Select( 'Visual Theme', 'ctt_theme' );
            $ctt_theme->set_choices( [
                'style1' => 'Send Her My Love',
                'style2' => 'Roll With The Changes',
                'style3' => 'Free Bird',
                'style4' => 'Don\'t Stop Believin\'',
                'style5' => 'Thunderstruck',
                'style6' => 'Livin\' On A Prayer',
                'none' => 'None - Create Your Own CSS In Your Theme'
            ])
                ->set_size( 'sw-col-300' )
                ->set_default( 'style1' )
                ->set_premium( 'pro' )
                ->set_priority( 10 );

            //* cttCSS => ctt_css
            $ctt_css = new SWP_Option_Textarea( 'Custom CSS', 'ctt_css' );
            $ctt_css->set_dependency( 'ctt_theme', 'none' )
                ->set_premium( 'pro' )
                ->set_size( 'sw-col-300' )
                ->set_priority( 20 );

            //* cttPreview => ctt_preview
            //* NOTE: This key is currently kept as the old key for
            //* CSS and JS compatability.
            $ctt_preview = new SWP_Section_HTML( 'Click To Tweet Preview', 'ctt_preview' );
            $ctt_preview->do_ctt_preview()
                ->set_premium( 'pro' )
                ->set_priority( 30 );

        $click_to_tweet->add_options( [$ctt_theme, $ctt_css, $ctt_preview] );

        $styles->add_section( $click_to_tweet );

        return $this;
    }
}
