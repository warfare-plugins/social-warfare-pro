<?php
/**
 * Register and output open graph tags, Twitter cards, custom color CSS, and the icon fonts.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Created | Uknown
 * @since     2.2.4 | Updated | 05 MAY 2017 | Added the global options for og:type values
 */

defined( 'WPINC' ) || die;

/**
 * Hook into the core header filter
 *
 * Create and return the values to be used in the header meta tags
 *
 * All meta values will be returned in the $info['meta_tag_values'] array.
 *
 * The following values will be returned from the function swp_open_graph_values():
 *     Open Graph Type          $info['meta_tag_values']['og_type']
 *     Open Graph Title         $info['meta_tag_values']['og_title']
 *     Open Graph Description   $info['meta_tag_values']['og_description']
 *     Open Graph Image         $info['meta_tag_values']['og_image']
 *     Open Graph Image Width   $info['meta_tag_values']['og_image_width']
 *     Open Graph Image Height  $info['meta_tag_values']['og_image_height']
 *     Open Graph URL           $info['meta_tag_values']['og_url']
 *     Open Graph Site Name     $info['meta_tag_values']['og_site_name']
 *     Article Author           $info['meta_tag_values']['article_author']
 *     Article Publisher        $info['meta_tag_values']['article_publisher']
 *     Article Published Time   $info['meta_tag_values']['article_published_time']
 *     Article Modified Time    $info['meta_tag_values']['article_modified_time']
 *     OG Modified Time         $info['meta_tag_values']['og_modified_time']
 *     Facebook App ID          $info['meta_tag_values']['fb_app_id']
 *
 * The following values will be returned from the function swp_twitter_card_values():
 *     Twitter Card type        $info['meta_tag_values']['twitter_card']
 *     Twitter Title            $info['meta_tag_values']['twitter_title']
 *     Twitter Description      $info['meta_tag_values']['twitter_description']
 *     Twitter Image            $info['meta_tag_values']['twitter_image']
 *     Twitter Site             $info['meta_tag_values']['twitter_site']
 *     Twitter creator          $info['meta_tag_values']['twitter_creator']
 *
 *
 */
add_filter( 'swp_header_values' , 'swp_open_graph_values'    , 5 );
add_filter( 'swp_header_values' , 'swp_twitter_card_values'  , 10 );
add_filter( 'swp_header_html'   , 'swp_open_graph_html'      , 5 );
add_filter( 'swp_header_html'   , 'swp_twitter_card_html'    , 10 );
add_filter( 'swp_header_html'   , 'swp_output_custom_color'  , 15 );

/**
 * Open Graph Meta Tag Values
 *
 * Notes: If the user specifies an Open Graph tag,
 * we're going to develop a complete set of tags. Order
 * of preference for each tag is as follows:
 * 1. Did they fill out our open graph field?
 * 2. Did they fill out Yoast's social field?
 * 3. Did they fill out Yoast's SEO field?
 * 4. We'll just auto-generate the field from the post.
 *
 * @since  2.1.4
 * @access public
 * @param  array $info An array of data about the post
 * @return array $info The modified array
 */
function swp_open_graph_values($info){

	if( false === is_singular() ) {
		return $info;
	}

	global $swp_user_options;

	/**
	 * Begin by fetching the user's default custom settings
	 *
	 */
	$custom_og_title       = htmlspecialchars( get_post_meta( $info['postID'] , 'nc_ogTitle' , true ) );
	$custom_og_description = htmlspecialchars( get_post_meta( $info['postID'] , 'nc_ogDescription' , true ) );
	$custom_og_image_id    = get_post_meta( $info['postID'] , 'nc_ogImage' , true );
	$custom_og_image_url   = get_post_meta( $info['postID'] , 'swp_open_graph_image_url' , true );
	$custom_og_image_data  = json_decode( get_post_meta( $info['postID'] , 'swp_open_graph_image_data' , true ) );

	/**
	 * Disable Jetpack's Open Graph tags
	 *
	 */
	add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
	add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );

	/**
	 * Check for and coordinate with Yoast to create the best possible values for each tag
	 *
	 */
	if ( defined( 'WPSEO_VERSION' ) ) :
		global $wpseo_og;
		$info['yoast_og_setting'] = has_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ) );
	else :
		$info['yoast_og_setting'] = false;
	endif;

	// Check if the user has filled out at least one of the custom fields
	if ( defined( 'WPSEO_VERSION' ) && ( !empty( $custom_og_title ) || !empty( $custom_og_description ) || !empty( $custom_og_image_url ) ) ):

		/**
		 * YOAST SEO: It rocks, so if it's installed, let's coordinate with it
		 *
		 */

		// Collect their Social Descriptions as backups if they're not defined in ours
		$yoast_og_title         = get_post_meta( $info['postID'] , '_yoast_wpseo_opengraph-title' , true );
		$yoast_og_description   = get_post_meta( $info['postID'] , '_yoast_wpseo_opengraph-description' , true );
		$yoast_og_image         = get_post_meta( $info['postID'] , '_yoast_wpseo_opengraph-image' , true );
		$yoast_seo_title        = get_post_meta( $info['postID'] , '_yoast_wpseo_title' , true );
		$yoast_seo_description  = get_post_meta( $info['postID'] , '_yoast_wpseo_metadesc' , true );

		// Cancel their output if ours have been defined so we don't have two sets of tags
		global $wpseo_og;
		remove_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ), 30 );
		$info['yoast_og_setting'] = false;

		// Fetch the WPSEO_SOCIAL Values
		$wpseo_social = get_option( 'wpseo_social' );

	// End of the Yoast Conditional
	endif;

	/**
	 * Open Graph Tags (The Easy Ones That Don't Need Conditional Fallbacks)
	 *
	 */
	$info['meta_tag_values']['og_url']                 = get_permalink();
	$info['meta_tag_values']['og_site_name']           = get_bloginfo( 'name' );
	$info['meta_tag_values']['article_published_time'] = get_post_time( 'c' );
	$info['meta_tag_values']['article_modified_time']  = get_post_modified_time( 'c' );
	$info['meta_tag_values']['og_modified_time']       = get_post_modified_time( 'c' );

	/**
	 * Open Graph Type
	 * @since 2.2.4 | Updated | 05 MAY 2017 | Added the global options for og:type values
	 */
	$swp_post_type = get_post_type();
	if(!isset($swp_user_options['swp_og_type_'.$swp_post_type])):
		$swp_user_options['swp_og_type_'.$swp_post_type] = 'article';
	endif;
	$og_type_from_global_options = $swp_user_options['swp_og_type_'.$swp_post_type];
	$og_type_from_custom_field = get_post_meta( $info['postID'] , 'swp_og_type' , true );
	if( $og_type_from_custom_field ):
		$info['meta_tag_values']['og_type'] = $og_type_from_custom_field;
	else:
 		$info['meta_tag_values']['og_type'] = $og_type_from_global_options;
	endif;

	/**
	 *  Open Graph Title: Create an open graph title meta tag
	 *
	 */
	if ( !empty( $custom_og_title ) ) :
		$info['meta_tag_values']['og_title'] = $custom_og_title;
	elseif ( !empty( $yoast_og_title )) :
		$info['meta_tag_values']['og_title'] = $yoast_og_title;
	elseif ( !empty( $yoast_seo_title ) ) :
		$info['meta_tag_values']['og_title'] = $yoast_seo_title;
	else :
		$info['meta_tag_values']['og_title'] = trim( convert_smart_quotes( htmlspecialchars_decode( get_the_title() ) ) );
	endif;

	/**
	 * Open Graph Description
	 *
	 */
	if ( !empty( $custom_og_description ) ) :
		$info['meta_tag_values']['og_description'] = $custom_og_description;
	elseif ( !empty( $yoast_og_description ) ) :
		$info['meta_tag_values']['og_description'] = $yoast_og_description;
	elseif ( !empty( $yoast_seo_description ) ) :
		$info['meta_tag_values']['og_description'] = $yoast_seo_description;
	else :
		$info['meta_tag_values']['og_description'] = convert_smart_quotes( htmlspecialchars_decode( swp_get_excerpt_by_id( $info['postID'] ) ) ) ;
	endif;

	/**
	 * Open Graph image
	 *
	 */
	if ( !empty( $custom_og_image_url ) ) :
		$info['meta_tag_values']['og_image'] = $custom_og_image_url;
	elseif ( !empty( $yoast_og_image ) ) :
		$info['meta_tag_values']['og_image'] = $yoast_og_image;
	else :
		$thumbnail_url = wp_get_attachment_url( get_post_thumbnail_id( $info['postID'] ) );
		if ( $thumbnail_url ) :
			$info['meta_tag_values']['og_image'] = $thumbnail_url;
		endif;
	endif;

	/**
	 * Open Graph Image Dimensions
	 *
	 */
	if ( !empty( $custom_og_image_data ) ) :
		$info['meta_tag_values']['og_image_width']   = $custom_og_image_data[1];
		$info['meta_tag_values']['og_image_height']	 = $custom_og_image_data[2];
	endif;

	/**
	 * Facebook Author
	 *
	 */
	if ( get_the_author_meta( 'swp_fb_author' , swp_get_author( $info['postID'] ) ) ) :
		$info['meta_tag_values']['article_author'] = get_the_author_meta( 'swp_fb_author' , swp_get_author( $info['postID'] ) );
	elseif ( get_the_author_meta( 'facebook' , swp_get_author( $info['postID'] ) ) && defined( 'WPSEO_VERSION' ) ) :
		$info['meta_tag_values']['article_author'] = get_the_author_meta( 'facebook' , swp_get_author( $info['postID'] ) );
	endif;

	/**
	 * Open Graph Publisher
	 *
	 */
	if ( !empty( $swp_user_options['facebookPublisherUrl'] )) :
		$info['meta_tag_values']['article_publisher'] = $swp_user_options['facebookPublisherUrl'];
	elseif ( isset( $wpseo_social ) && !empty( $wpseo_social['facebook_site'] ) ) :
		$info['meta_tag_values']['article_publisher'] = $wpseo_social['facebook_site'];
	endif;

	/**
	 * Open Graph App ID
	 *
	 */
	if ( !empty( $swp_user_options['facebookAppID'] ) ) :
		$info['meta_tag_values']['fb_app_id'] = $swp_user_options['facebookAppID'];
	elseif ( isset( $wpseo_social ) && !empty( $wpseo_social['fbadminapp'] ) ) :
		$info['meta_tag_values']['fb_app_id'] = $wpseo_social['fbadminapp'];
	else :
		$info['meta_tag_values']['fb_app_id'] = '529576650555031';
	endif;

	return $info;
}

/**
 * A function to compile the meta tags into HTML
 * @param  array $info The info array
 * @return array $info The modified info array
 */
function swp_open_graph_html($info) {

	if( false === is_singular() ) {
		return $info;
	}

	// Check to ensure that we don't need to defer to Yoast
	if(false === $info['yoast_og_setting']):

		if( isset( $info['meta_tag_values']['og_type'] ) && !empty( $info['meta_tag_values']['og_type'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta property="og:type" content="'. trim( $info['meta_tag_values']['og_type'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_title'] ) && !empty( $info['meta_tag_values']['og_title'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta property="og:title" content="'. trim( $info['meta_tag_values']['og_title'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_description'] ) && !empty( $info['meta_tag_values']['og_description'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta property="og:description" content="'. trim( $info['meta_tag_values']['og_description'] ).'" />';
		endif;

		if( isset( $info[ 'meta_tag_values'][ 'og_image' ] )         && !empty( $info['meta_tag_values']['og_image'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta property="og:image" content="'. trim( $info['meta_tag_values']['og_image'] ).'" />';
		endif;

		if( isset( $info[ 'meta_tag_values'][ 'og_image_width' ] )  && !empty( $info['meta_tag_values']['og_image_width'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="og:image:width" content="'. trim( $info['meta_tag_values']['og_image_width'] ).'" />';
		endif;
		if( isset( $info[ 'meta_tag_values'][ 'og_image_height' ] ) && !empty( $info['meta_tag_values']['og_image_height'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="og:image:height" content="'. trim( $info['meta_tag_values']['og_image_height'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_url'] ) && !empty( $info['meta_tag_values']['og_url'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta property="og:url" content="'. trim( $info['meta_tag_values']['og_url'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_site_name'] ) && !empty( $info['meta_tag_values']['og_site_name'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta property="og:site_name" content="'. trim( $info['meta_tag_values']['og_site_name'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_author'] ) && !empty( $info['meta_tag_values']['article_author'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="article:author" content="'. trim( $info['meta_tag_values']['article_author'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_publisher'] ) && !empty( $info['meta_tag_values']['article_publisher'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="article:publisher" content="'. trim( $info['meta_tag_values']['article_publisher'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_published_time'] ) && !empty( $info['meta_tag_values']['article_published_time'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="article:published_time" content="'. trim( $info['meta_tag_values']['article_published_time'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_modified_time'] ) && !empty( $info['meta_tag_values']['article_modified_time'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="article:modified_time" content="'. trim( $info['meta_tag_values']['article_modified_time'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_modified_time'] ) && !empty( $info['meta_tag_values']['og_modified_time'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="og:updated_time" content="'. trim( $info['meta_tag_values']['og_modified_time'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['fb_app_id'] ) && !empty( $info['meta_tag_values']['fb_app_id'] ) ):
			$info['html_output'] .= PHP_EOL . '<meta property="fb:app_id" content="'. trim( $info['meta_tag_values']['fb_app_id'] ).'" />';
		endif;

	endif;

	return $info;
}

/**
 *  Generate the Twitter Card fields
 *
 *	Notes: If the user has Twitter cards turned on, we
 *	need to generate them, but we also like Yoast so we'll
 *	pay attention to their settings as well. Here's the order
 *	of preference for each field:
 *	1. Did the user fill out the Social Media field?
 *	2. Did the user fill out the Yoast Twitter Field?
 *	3. Did the user fill out the Yoast SEO field?
 *	4. We'll auto generate something logical from the post.
 *
 * @since 2.1.4
 * @access public
 * @param array $info An array of information about the post
 * @return array $info The modified array
 */
function swp_twitter_card_values($info) {

	if( false === is_singular() ) {
		return $info;
	}

	global $swp_user_options;

	if ( is_singular() && $swp_user_options['swp_twitter_card'] ) :

		/**
		 * Begin by fetching the user's default custom settings
		 *
		 */
		$custom_og_title       = htmlspecialchars( get_post_meta( $info['postID'] , 'nc_ogTitle' , true ) );
		$custom_og_description = htmlspecialchars( get_post_meta( $info['postID'] , 'nc_ogDescription' , true ) );
		$custom_og_image_id    = get_post_meta( $info['postID'] , 'nc_ogImage' , true );
		$custom_og_image_url   = get_post_meta( $info['postID'] , 'swp_open_graph_image_url' , true );
		$user_twitter_handle   = get_the_author_meta( 'swp_twitter' , swp_get_author( $info['postID'] ) );

		/**
		 * YOAST SEO: It rocks, so if it's installed, let's coordinate with it
		 *
		 */
		if ( defined( 'WPSEO_VERSION' ) ) :
			$yoast_twitter_title        = get_post_meta( $info['postID'] , '_yoast_wpseo_twitter-title' , true );
			$yoast_twitter_description  = get_post_meta( $info['postID'] , '_yoast_wpseo_twitter-description' , true );
			$yoast_twitter_image        = get_post_meta( $info['postID'] , '_yoast_wpseo_twitter-image' , true );
			$yoast_seo_title            = get_post_meta( $info['postID'] , '_yoast_wpseo_title' , true );
			$yoast_seo_description      = get_post_meta( $info['postID'] , '_yoast_wpseo_metadesc' , true );

			// Cancel their output if ours have been defined so we don't have two sets of tags
			remove_action( 'wpseo_head' , array( 'WPSEO_Twitter', 'get_instance' ) , 40 );
		endif;

		/**
		 * JET PACK: If ours are activated, disable theirs
		 *
		 */
		add_filter( 'jetpack_disable_twitter_cards', '__return_true', 99 );

		/**
		 * TWITTER TITLE
		 *
		 */
		if ( !empty( $custom_og_title ) ):
			$info['meta_tag_values']['twitter_title'] = $custom_og_title;
		elseif( !empty( $yoast_twitter_title ) ) :
			$info['meta_tag_values']['twitter_title'] = $yoast_twitter_title;
		else:
			$info['meta_tag_values']['twitter_title'] = $info['meta_tag_values']['og_title'];
		endif;

		/**
		 * TWITTER DESCRIPTION
		 *
		 */
		if( !empty( $custom_og_description ) ):
			$info['meta_tag_values']['twitter_description'] = $custom_og_description;
		elseif ( !empty( $yoast_twitter_description ) ) :
			$info['meta_tag_values']['twitter_description'] = $yoast_twitter_description;
		else:
			$info['meta_tag_values']['twitter_description'] = $info['meta_tag_values']['og_description'];
		endif;

		/**
		 * TWITTER IMAGE
		 *
		 */
		if ( !empty( $custom_og_image_url ) ):
			$info['meta_tag_values']['twitter_image'] = $custom_og_image_url;
		elseif ( !empty( $yoast_twitter_image ) ) :
			$info['meta_tag_values']['twitter_image'] = $yoast_twitter_image;
		elseif( !empty( $info['meta_tag_values']['og_image'] ) ):
			$info['meta_tag_values']['twitter_image'] = $info['meta_tag_values']['og_image'];
		endif;

		/**
		 * The Twitter Card Type
		 *
		 */
		if( !empty( $info['meta_tag_values']['twitter_image'] ) ):
			$info['meta_tag_values']['twitter_card'] = 'summary_large_image';
		else:
			$info['meta_tag_values']['twitter_card'] = 'summary';
		endif;

		/**
		 * The Twitter Card Site
		 *
		 */
		if ( $swp_user_options['twitterID'] ) :
			$info['meta_tag_values']['twitter_site'] = '@' . str_replace( '@' , '' , trim( $swp_user_options['twitterID'] ) );
		endif;

		/**
		 * The Twitter Card Creator
		 */
		if ( $user_twitter_handle ) :
			$info['meta_tag_values']['twitter_creator'] = '@' . str_replace( '@' , '' , trim( $user_twitter_handle ) );
		endif;

	endif;
	return $info;
}

function swp_twitter_card_html($info) {

	if( false === is_singular() ) {
		return $info;
	}

	global $swp_user_options;

	if ( is_singular() && $swp_user_options['swp_twitter_card'] ) :

		if( isset( $info['meta_tag_values']['twitter_card'] ) && !empty( $info['meta_tag_values']['twitter_card'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta name="twitter:card" content="'. trim( $info['meta_tag_values']['twitter_card'] ) .'">';
		endif;

		if( isset( $info['meta_tag_values']['twitter_title'] ) && !empty( $info['meta_tag_values']['twitter_title'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta name="twitter:title" content="' . trim( $info['meta_tag_values']['twitter_title'] ) . '">';
		endif;

		if( isset( $info['meta_tag_values']['twitter_description'] ) && !empty( $info['meta_tag_values']['twitter_description'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta name="twitter:description" content="' . trim( $info['meta_tag_values']['twitter_description'] ) . '">';
		endif;

		if( isset( $info['meta_tag_values']['twitter_image'] ) && !empty($info['meta_tag_values']['twitter_image']) ):
			$info['html_output'] .= PHP_EOL . '<meta name="twitter:image" content="' . trim( $info['meta_tag_values']['twitter_image'] ) . '">';
		endif;

		if ( isset( $info['meta_tag_values']['twitter_site'] ) && !empty( $info['meta_tag_values']['twitter_site'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta name="twitter:site" content="' . trim( $info['meta_tag_values']['twitter_site'] ) . '">';
		endif;

		if ( isset( $info['meta_tag_values']['twitter_creator'] ) && !empty( $info['meta_tag_values']['twitter_creator'] ) ) :
			$info['html_output'] .= PHP_EOL . '<meta name="twitter:creator" content="' . trim( $info['meta_tag_values']['twitter_creator'] ) . '">';
		endif;

	endif;

	return $info;
}

/**
 * Output the CSS for custom selected colors
 *
 * @since  1.4.0
 * @access public
 * @param  array $info The array of information about the post
 * @return array $info The modified array
 */
function swp_output_custom_color( $info ) {

	global $swp_user_options;

	if ( $swp_user_options['dColorSet'] == 'customColor' || $swp_user_options['iColorSet'] == 'customColor' || $swp_user_options['oColorSet'] == 'customColor' ) :
		$info['html_output'] .= PHP_EOL . '<style type="text/css">.nc_socialPanel.swp_d_customColor a, html body .nc_socialPanel.swp_i_customColor .nc_tweetContainer:hover a, body .nc_socialPanel.swp_o_customColor:hover a {color:white} .nc_socialPanel.swp_d_customColor .nc_tweetContainer, html body .nc_socialPanel.swp_i_customColor .nc_tweetContainer:hover, body .nc_socialPanel.swp_o_customColor:hover .nc_tweetContainer {background-color:' . $swp_user_options['customColor'] . ';border:1px solid ' . $swp_user_options['customColor'] . ';} </style>';
	endif;

	if ( $swp_user_options['dColorSet'] == 'ccOutlines' || $swp_user_options['iColorSet'] == 'ccOutlines' || $swp_user_options['oColorSet'] == 'ccOutlines' ) :
		$info['html_output'] .= PHP_EOL . '<style type="text/css">.nc_socialPanel.swp_d_ccOutlines a, html body .nc_socialPanel.swp_i_ccOutlines .nc_tweetContainer:hover a, body .nc_socialPanel.swp_o_ccOutlines:hover a { color:' . $swp_user_options['customColor'] . '; }
.nc_socialPanel.swp_d_ccOutlines .nc_tweetContainer, html body .nc_socialPanel.swp_i_ccOutlines .nc_tweetContainer:hover, body .nc_socialPanel.swp_o_ccOutlines:hover .nc_tweetContainer { background:transparent; border:1px solid ' . $swp_user_options['customColor'] . '; } </style>';

	endif;

	if ( $swp_user_options['floatStyleSource'] == false && ($swp_user_options['sideDColorSet'] == 'customColor' || $swp_user_options['sideIColorSet'] == 'customColor' || $swp_user_options['sideOColorSet'] == 'customColor') ) :
		$info['html_output'] .= PHP_EOL . '<style type="text/css">.nc_socialPanel.swp_d_customColor a, html body .nc_socialPanel.nc_socialPanelSide.swp_i_customColor .nc_tweetContainer:hover a, body .nc_socialPanel.nc_socialPanelSide.swp_o_customColor:hover a {color:white} .nc_socialPanel.nc_socialPanelSide.swp_d_customColor .nc_tweetContainer, html body .nc_socialPanel.nc_socialPanelSide.swp_i_customColor .nc_tweetContainer:hover, body .nc_socialPanel.nc_socialPanelSide.swp_o_customColor:hover .nc_tweetContainer {background-color:' . $swp_user_options['sideCustomColor'] . ';border:1px solid ' . $swp_user_options['sideCustomColor'] . ';} </style>';
	endif;

	if ( $swp_user_options['floatStyleSource'] == false && ( $swp_user_options['sideDColorSet'] == 'ccOutlines' || $swp_user_options['sideIColorSet'] == 'ccOutlines' || $swp_user_options['sideOColorSet'] == 'ccOutlines' ) ) :
		$info['html_output'] .= PHP_EOL . '<style type="text/css">.nc_socialPanel.nc_socialPanelSide.swp_d_ccOutlines a, html body .nc_socialPanel.nc_socialPanelSide.swp_i_ccOutlines .nc_tweetContainer:hover a, body .nc_socialPanel.nc_socialPanelSide.swp_o_ccOutlines:hover a { color:' . $swp_user_options['sideCustomColor'] . '; }
.nc_socialPanel.nc_socialPanelSide.swp_d_ccOutlines .nc_tweetContainer, html body .nc_socialPanel.nc_socialPanelSide.swp_i_ccOutlines .nc_tweetContainer:hover, body .nc_socialPanel.nc_socialPanelSide.swp_o_ccOutlines:hover .nc_tweetContainer { background:transparent; border:1px solid ' . $swp_user_options['sideCustomColor'] . '; } </style>';

		endif;
	return $info;
}
