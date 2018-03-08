<?php
//* For options whose database name has changed, it is notated as follows:
//* prevOption => new_option
//* @see SWP_Database_Migration

class SWP_Pro_Options_Page extends SWP_Options_Page {
    /**
     * Reference to the global SWP_Options_Page object.
     * SWP_Option_Page $core
     */

	public function __construct() {
		global $SWP_Options_Page;

        $this->core = $SWP_Options_Page;

    }

    public function update_display_tab() {
        $display = $this->core->display;

        //* minTotes => minimum_shares
        $minimum_shares = new SWP_Options_Text( 'Minimum Shares' );
        $minimum_shares->set_default( 0 )
            ->set_priority( 30 )
            ->set_size( 'two-thirds' )
            ->set_premium( 'pro' );

        $this->core->display->share_counts->add_option( $minimum_shares );

        /* Image Hover Pin Button   */
        $image_hover = new SWP_Options_Page_Option( 'Image Hover Pin Button' );
        $image_hover->set_description( 'If you would like a "Pin" button to appear on images when users hover over them, activate this.' )
            ->set_priority( 40 )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-display-tab-image-hover-pin-button/' );

            $pinit_button = new SWP_Options_Toggle( 'Pinit Button' );
            $pinit_button->set_default( true )
                ->set_size( 'two-thirds' )
                ->set_premium( 'pro' );

        $image_hover->add_option( $pinit_button );

        $display->image_hover = $image_hover;

        return $this;
    }

    public function update_styles_tab() {
        $styles = $this->core->styles;

        //* visualTheme => button_shape
        $button_shape = new SWP_Options_Select( 'Button Shape' );
        $button_shape->set_choices( [
            'flat_fresh'=> 'Flat & Fresh',
            'leaft'     => 'A Leaf on the Wind',
            'shift'     => 'Shift',
            'pill'      => 'Pills',
            'three_dee' => 'Three-Dee',
            'connectd'  => 'Connected',
            'boxed'     => 'Boxed'
        ])
            ->set_default( 'flat_fresh' )
            ->set_priority( 10 )
            ->set_size( 'two-fourths' )
            ->set_premium( 'pro' );

        //* buttonSize => button_size
        $button_size = new SWP_Options_Select( 'Button Size' );
        $button_size->set_choices( [
            '1.4' => '140%',
            '1.3' => '130%',
            '1.2' => '120%',
            '1.1' => '110%',
            '1'     => '100%',
            '0.9'   => '90%',
            '0.8'   => '80%',
            '0.7'   => '70%'
        ])
            ->set_default( '1' )
            ->set_priority( 20 )
            ->set_size( 'two-fourths' )
            ->set_premium( 'pro' );

        $color_choices = $this->get_color_choices_array();

        //* dColorSet => default_colors
        $default_colors = new SWP_Options_Select( 'Default Color Set' );
        $default_colors->set_choices( $color_choices )
            ->set_default( 'full_color' )
            ->set_priority( 30 )
            ->set_size( 'two-fourths' )
            ->set_premium( 'pro' );

        $styles->visual_options->add_options( [$button_shape, $button_size, $default_colors] );

        //* sideReveal => transition
        $transition = new SWP_Options_Select( 'Transition' );
        $transition->set_choices( [
            'slide' => 'Slide In / Slide Out',
            'fade'  => 'Fade In / Fade Out'
        ])
            ->set_default( 'slide' )
            ->set_priority( 40 )
            ->set_dependency( 'floating_panel', ['left', 'right'] );
            ->set_premium( 'pro' );

        //* floatStyle => float_button_shape
        $float_button_shape = new SWP_Options_Select( 'Button Shape' );
        $float_button_shape->set_choices( [
            'default'   => 'Buttons',
            'boxed'     => 'Boxes',
        ])
            ->set_default( 'boxed' )
            ->set_priority( 50 )
            ->set_dependency( 'floating_panel', ['left', 'rigt'] )
            ->set_premium( 'pro' );

        //* floatStyleSource => float_style_source
        $float_style_source = new SWP_Options_Select( 'Inherit Visual Options' );
        $float_style_source->set_default( true )
            ->set_priority( 60 )
            ->set_dependency( 'floating_panel', ['left', 'right'] )
            ->set_premium( true )
            ->set_addon( 'pro ');

        //* sideCustomColor => float_custom_color
        $float_custom_color = new SWP_Options_Text( 'Custom Color' );
        $float_custom_color->set_default( '#ced3dc' )
            ->set_priority( 90 )
            ->set_size( 'two-fourths' )
            ->set_premium( true )
            ->set_addon( 'pro ');

        $styles->floating_panel->add_options( [$transition, $float_button_shape, $float_style_source, $float_custom_color] );

        $click_to_tweet = new SWP_Options_Page_Section( 'Click-To-Tweet Style' );
        $click_to_tweet->set_description( 'Select the default visual style for Click-to-Tweets on your site.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-styles-tab-click-tweet-style/' )
            ->set_priority( 40 );

        $ctt_style = new SWP_Options_Select( 'Visual Theme' );
        $ctt_style->set_choices( [
            'style1' => 'Send Her My Love',
            'style2' => 'Roll With The Changes',
            'style3' => 'Free Bird',
            'style4' => 'Don\'t Stop Believin\'',
            'style5' => 'Thunderstruck',
            'style6' => 'Livin\' On A Prayer',
            'none' => 'None - Create Your Own CSS In Your Theme'
        ])
            ->set_default( 'style1' )
            ->set_premium( 'pro' );

        $ctt_css = new SWP_Options_Textarea( 'Custom CSS ' );
        $ctt_css->set_dependency( 'ctt_style', 'none' )
            ->set_premium( 'pro' );

        //* TODO: $click_to_tweet needs the preview added to its section.

        $click_to_tweet->add_options( [$ctt_style, $ctt_css] );
        $styles->add_section( $click_to_tweet );

        return $this;
    }

    public function update_social_tab() {
        $sitewide_identity = $this->core->social_identity->sitewide_identity;

        foreach( $post_types as $index => $type ) {
            $priority = ( ( $index + 1 ) * 10 );
            $option = new SWP_Options_Select( usfirst( $type ) );
            $option->set_priority( $priority )
                ->set_choices( $custom_post_types )
                ->set_default( 'article' )
                ->set_premium( 'pro' );

            $sitewide_identity->open_graph->add_option( $option );
        }

        return $this;

    }

    public function update_advanced_tab() {
        $advanced = $this->core->advanced;

        $frame_buster = new SWP_Option_Page_Section( 'Frame Buster' );
        $frame_buster->set_priority( 10 )
            ->set_description( 'If you want to stop content pirates from framing your content, turn this on.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-frame-buster/');

            $frame_buster_toggle = new SWP_Option_Page_Checkbox( '' );
            $frame_buster_toggle->set_default( true )
                ->set_premium( 'pro' );

        $analytics_tracking = new SWP_Option_Page_Section( 'Analytics Tracking' );
        $analytics_tracking->set_description( 'If you want to activate UTM tracking for shared URL, turn this on.' )
            ->set_priority( 30 )


            //* swp_click_tracking => click_tracking
            $click_tracking = new SWP_Option_Page_Checkbox( 'Button Click Tracking ');
            $click_tracking->set_priority( 10 )
                ->set_size( 'two-thirds' )
                ->set_default( false )
                ->set_premium( true )
                ->set_addon( 'pro ');

            //* googleAnalytics => google_analytics
            $google_analytics = clone $click_tracking;
            $google_analytics->set_name( 'UTM Link Tracking' )
                ->set_priority( 20 );

            //* analyticsMedium => analytics_medium
            $analytics_medium = new SWP_Options_Text( 'UTM Medium' );
            $analytics_medium->set_default( 'Social' )
                ->set_priority( 30 )
                ->set_dependency( 'google_analytics', true )
                ->set_premium( true)
                ->set_addon( 'pro' );

            $analytics_campaign = clone $analytics_medium;
            $analytics_campaign->set_name( 'UTM Campaign' )
                ->set_priority( 40 )
                ->set_default( 'SocialWarfare' );
            }

        $analytics_tracking->add_options( [$click_tracking, $google_analytics, $analytics_medium,
                $analytics_campaign] );

            $analytics_pin_tracking = new SWP_Option_Page_Checkbox( 'UTM Tracking on Pins ' );
            $analytics_pin_tracking->set_default( false )
                ->set_priority( 50 )
                ->set_size( 'two-thirds' )
                ->set_dependency( 'google_analytics', true )
                ->set_premium( 'pro' );

        $advanced_pinterest = new SWP_Option_Page_Section( 'Advanced Pinterest Settings ');
        $advanced_pinterest->set_description( 'Get maximum control over how your visitors are sharing your content on Pinterest.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-advanced-pinterest-settings/' )
            ->set_priority( 40 )
            ->set_premium( 'pro' );

            //* advanced_pinterest_image => pin_browser_extension
            $pin_browser_extension = new SWP_Options_Toggle( 'Pinterest Image for Browser Extensions' );
            $pin_browser_extension->set_default( false )
                ->set_size( 'two-thirds ')
                ->set_premium( 'pro' );

            //* advanced_pinterest_image_location => pinterest_image_location
            $pinterest_image_location = new SWP_Options_Select( 'Pinterest Image Location' );
            $pinterest_image_location->set_choices( [
                'hidden'    => 'Hidden',
                'top'       => 'At the top of each post.',
                'bottom'    => 'At the bottom of each post.'
            ])
                ->set_default( 'hidden ')
                ->set_size( 'two-thirds' )
                ->set_dependency( 'pin_browser_extension', true )
                ->set_premium( true )
                ->set_addon( 'pro ');

            //* advanced_pinterest_fallback => pinterest_fallback
            $pinterest_fallback = new SWP_Options_Select( 'Pinterest Image Fallback ');
            $pinterest_fallback->set_choices( [
                'all'   => 'Show a selection of all images on the page.',
                'featured'  => 'Show my featured image.'
            ])
                ->set_default( 'all' )
                ->set_premium( 'pro' );

        $advanced_pinterest->add_options( [$pin_browser_extension, $pinterest_image_location, $pinterest_fallback] );


        $share_recovery = new SWP_Option_Page_Section( 'Share Recovery' );
        $share_recovery->set_description( 'If at any point you have changed permalink structures or have gone from http to https (SSL) then you will have undoubtedly lost all of your share counts. This tool allows you to recover them. See <a target="_blank" href="https://warfareplugins.com/support/recover-social-share-counts-after-changing-permalink-settings/">this guide</a> for more detailed instructions on how to use this feature.' )
            ->set_information_link( 'https://warfareplugins.com/support/options-page-advanced-tab-share-recovery/' )
            ->set_priority( 50 )
            ->set_premium( 'pro' );

            $recover_shares = new SWP_Option_Page_Checkbox( 'Activate Share Recovery' );
            $recover_shares->set_default( false )
                ->set_priority( 10 )
                ->set_size( 'two-thirds' )
                ->set_premium( true )
                ->set_addon( 'pro ');

            $recovery_format = new SWP_Options_Select( 'Previous URL Format ');
            $recovery_format->set_choices( [
                'unchanged'			=> 'Unchanged',
                'default' 			=> 'Plain',
                'day_and_name' 		=> 'Day and Name',
                'month_and_name' 	=> 'Month and Name',
                'numeric' 			=> 'Numeric',
                'post_name' 		=> 'Post Name',
                'custom'			=> 'Custom'
            ])
                ->set_priority( 20 )
                ->set_default( 'unchanged' )
                ->set_size( 'two-thirds' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            //* recovery_custom_format => recovery_permalink
            $recovery_permalink = new SWP_Options_Text( 'Custom Permalink Format' );
            $recovery_permalink->set_size( 'two-thirds' )
                ->set_priority( 30 )
                ->set_dependency( 'recover_shares' , true )
                ->set_premium( 'pro' );

            $recovery_protocol = new SWP_Options_Select( 'Previous Connection Protocol ');
            $recovery_protocol->set_choices( [
                'unchanged'     => 'Unchanged',
                'http'  => 'http',
                'https' => 'https'
            ])
                ->set_priority( 40 )
                ->set_default( 'unchanged' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            $recovery_prefix = new SWP_Options_Select( 'Previous Domain Prefix ' );
            $recovery_prefix->set_choices( [
                'Unchanged' => 'Unchanged',
                'www'       => 'www',
                'nonwww'    => 'non-www',
            ])
                ->set_priority( 50 )
                ->set_default( 'unchanged' )
                ->set_size( 'two-thirds' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            $recovery_subdomain = new SWP_Options_Text( 'Submdomain' );
            $recovery_subdomain->set_default( '' )
                ->set_priority( 60 )
                ->set_size( 'two-thirds' )
                ->set_dependency( 'recover_shares', true );
                ->set_premium( 'pro' );

            //* TODO: Add the Cross Domain text here.

            $former_domain = new SWP_Options_Text( 'Former Domain ' );
            $former_domain->set_default( '' )
                ->set_priority( 70 )
                ->set_size( 'two-thirds' )
                ->set_dependency( 'recover_shares', true )
                ->set_premium( 'pro' );

            $current_domain = clone $former_domain;
            $current_domain->set_name( 'Current Domain ');

        $advanced->add_sections( [$frame_buster, $analytics_tracking, $advanced_pinterest, $share_recovery] );
    }

}
