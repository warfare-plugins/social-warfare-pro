<?php

if ( class_exists( 'SWP_Header_Output' ) ) :

/**
 * Register and output open graph tags, Twitter cards, and custom color CSS.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0 | Unknown     | Created
 * @since     2.2.4 | 05 MAY 2017 | Added the global options for og:type values.
 * @since     3.0.0 | 21 FEB 2018 | Refactored into a class-based system.
 * @since     3.0.8 | 23 MAY 2018 | Added compatibility for custom color/outline combos.
 * @since     3.1.0 | 05 JUL 2018 | Added global $post variable.
 *
 *
 * Hook into the core header filter
 *
 * Create and return the values to be used in the header meta tags
 *
 * All meta values will be returned in the $info['meta_tag_values'] array.
 *
 * The following values will be returned from the function open_graph_values():
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
 * The following values will be returned from the function twitter_card_values():
 *     Twitter Card type        $info['meta_tag_values']['twitter_card']
 *     Twitter Title            $info['meta_tag_values']['twitter_title']
 *     Twitter Description      $info['meta_tag_values']['twitter_description']
 *     Twitter Image            $info['meta_tag_values']['twitter_image']
 *     Twitter Site             $info['meta_tag_values']['twitter_site']
 *     Twitter creator          $info['meta_tag_values']['twitter_creator']
 *
 *
 */
class SWP_Pro_Header_Output extends SWP_Header_Output {
    public function __construct() {
        global $post, $swp_user_options;

		array(
			'article_author',
			'article_publisher',
			'article_published_time',
			'article_modified_time',
		);

		array(
			'twitter_card',
			'twitter_title',
			'twitter_description',
			'twitter_image',
			'twitter_site',
			'twitter_creator'
		);


        $this->options = $swp_user_options;
		// $this->establish_header_values();
        $this->establish_custom_colors();

		add_action( 'wp', array ($this, 'establish_header_values' ) );
		add_filter( 'swp_header_html', array( $this, 'render_meta_html' ) );
        $this->init();
    }

     public function init() {
        // add_filter( 'swp_header_values' , array( $this , 'open_graph_values' ), 5 );
        add_filter( 'swp_header_values' , array( $this , 'twitter_card_values' ) , 10 );
        // add_filter( 'swp_header_html'   , array( $this , 'open_graph_html' ) , 5 );
        // add_filter( 'swp_header_html'   , array( $this , 'twitter_card_html' ) , 10 );
        // add_filter( 'swp_header_html'   , array( $this , 'output_ctt_css' ) , 15 );
        // add_filter( 'swp_header_html'   , array( $this , 'output_custom_color' ), 15 );
    }

    /**
     * Parses user options and prepares data for header output.
     *
     * Any <meta> tags which can be configured with options or post_meta will be
     * touched by the callbacks in this method body.
     *
     * @return void
     */
	public function establish_header_values() {
		global $post;

		if( false === is_singular() || !is_object( $post ) ) {
    		return;
    	}

		$this->post = $post;
		$this->setup_open_graph();
		$this->generate_og_html();

		// die(var_dump(esc_html__($this->og_html)));
		// $this->setup_twitter();
	}

    /**
     * Appends $this object HTML to the <head>
     *
	 * @hook   swp_header_html | filter | origin SWP_Header_Output
	 * @param  string $meta_html Ready to print HTML for the <head>.
     * @return string $meta_html Ready to print HTML for the <head>.
     *
     */
	public function render_meta_html( $meta_html ) {
		$meta_html .= $this->og_html;
		return $meta_html;
	}

    /**
	 * Priorities.
     * 1. Did they fill out our open graph field?
     * 2. Did they fill out Yoast's social field?
     * 3. Did they fill out Yoast's SEO field?
     * 4. Aauto-generate the field from the post.
     */
	public function setup_open_graph() {
		if ( false === SWP_Utility::get_option( 'og_tags' ) && false === SWP_Utility::get_option( 'twitter_cards' ) ) {
            return;
        }

		add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
    	add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );

		$fields = array(
			'og_title',
			'og_description',
			'og_image_url',
			'og_image_width',
			'og_image_height',
			'og_url',
			'og_site_name',
		);

		$basic_fields = array(
			'og_type' => 'article',
			'og_url' => get_permalink(),
	    	'og_site_name' => get_bloginfo( 'name' ),
	    	'article:published_time' => get_post_time( 'c' ),
	    	'article:modified_time' => get_post_modified_time( 'c' ),
	    	'og:updated_time' => get_post_modified_time( 'c' )
		);

        // 1. Get post meta, if it exists.
		foreach ($fields as $index => $key) {
			$maybe_value = SWP_Utility::get_meta( $this->post->ID, "swp_$key" );
			// Go from indexed array to associative, with possibly missing values.
			unset($fields[$index]);
			$fields[$key] = $maybe_value;
		}

        // 2, 3. Yoast, if it exists.
		if ( defined( 'WPSEO_VERSION' ) ) {
			global $wpseo_og;
    		$info['yoast_og_setting'] = has_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ) );
			$fields = $this->fetch_yoast_fields( $fields );
		}

		// 4. Default to post content.
		$fields = $this->apply_default_fields( $fields );
		$fields = array_map( 'htmlspecialchars', $fields );

		$fields = array_merge( $basic_fields, $fields );

		$this->og_data = $fields;
	}

	protected function fetch_yoast_fields( $fields ) {
		global $wpseo_og;
		remove_action( 'wpseo_head', array( $wpseo_og, 'opengraph' ), 30 );

		$yoast_og_map = array(
			'og_title' => '_yoast_wpseo_opengraph-title',
			'og_description' => '_yoast_wpseo_opengraph-description',
			'og_image'	=> '_yoast_wpseo_opengraph-image',
		);

		$yoast_social_map = array(
			'og_title'	=> '_yoast_wpseo_title',
			'og_description'	=> '_yoast_wpseo_metadesc'
		);

		foreach ($fields as $swp_meta_key => $maybe_value) {
			if ( isset( $maybe_value ) ) {
				// post_meta value already exists from SWP.
				continue;
			}

			// OG Values
			if ( array_key_exists( $swp_meta_key, $yoast_og_map ) ) :
				foreach ($yoast_og_map as $swp_og_key => $yoast_og_key) {
					$yoast_og_value = SWP_Utility::get_meta( $this->post->ID, $yoast_og_key );

					if ( !empty( $yoast_og_value ) ) {
						if ( function_exists (' wpseo_replace_vars' ) ) {
							$yoast_og_value = wpseo_replace_vars( $yoast_og_value, $this->post );
						}
						$fields[$swp_meta_key] = $yoast_og_value;
						$maybe_value = $yoast_og_value;
						break;
					}
				}
			endif;

			// Social Values
			if ( empty( $maybe_value ) && array_key_exists( $swp_meta_key, $yoast_social_map) ) :
				foreach( $yoast_social_map as $swp_og_key => $yoast_social_key ) {
					$yoast_social_value = SWP_Utility::get_meta( $this->post->ID, $yoast_social_key );

					if ( !empty( $yoast_og_value ) ) {
						if ( function_exists (' wpseo_replace_vars' ) ) {
							$yoast_og_value = wpseo_replace_vars( $yoast_og_value, $this->post );
						}
						$fields[$swp_meta_key] = $yoast_social_value;
						break;
					}
				}
			endif;
		}

		return $fields;
	}

    protected function apply_default_fields( $fields ) {
		$defaults = array(
			'og_description' => html_entity_decode( SWP_Utility::convert_smart_quotes( htmlspecialchars_decode( SWP_Utility::get_the_excerpt( $this->post->ID ) ) ) ),
			'og_title' => trim( SWP_Utility::convert_smart_quotes( htmlspecialchars_decode( get_the_title() ) ) )
		);

		$thumbnail_url = wp_get_attachment_url( get_post_thumbnail_id( $this->post->ID ) );
		if ( $thumbnail_url ) {
			$defaults['og_image'] = $thumbnail_url;
		}

		$fields = array_merge( $defaults, $fields );

		return $fields;


        // @TODO what is this, What is get_the_author_meta()?
        // $author = SWP_User_Profile::get_author( $this->post->ID );
		//
		// if ( get_the_author_meta( 'swp_fb_author' , $author ) ) :
    	// 	$info['meta_tag_values']['article_author'] = get_the_author_meta( 'swp_fb_author' , SWP_User_Profile::get_author( $info['postID'] ) );
    	// elseif ( get_the_author_meta( 'facebook' , SWP_User_Profile::get_author( $info['postID'] ) ) && defined( 'WPSEO_VERSION' ) ) :
    	// 	$info['meta_tag_values']['article_author'] = get_the_author_meta( 'facebook' , SWP_User_Profile::get_author( $info['postID'] ) );
    	// endif;
	}
    public function open_graph_values(){
    	// if ( !empty( $custom_og_image_data ) ) :
    	// 	$info['meta_tag_values']['og_image_width']   = $custom_og_image_data[1];
    	// 	$info['meta_tag_values']['og_image_height']	 = $custom_og_image_data[2];
    	// endif;

    	/**
    	 * Facebook Author
    	 *
    	 */
    	if ( get_the_author_meta( 'swp_fb_author' , SWP_User_Profile::get_author( $info['postID'] ) ) ) :
    		$info['meta_tag_values']['article_author'] = get_the_author_meta( 'swp_fb_author' , SWP_User_Profile::get_author( $info['postID'] ) );
    	elseif ( get_the_author_meta( 'facebook' , SWP_User_Profile::get_author( $info['postID'] ) ) && defined( 'WPSEO_VERSION' ) ) :
    		$info['meta_tag_values']['article_author'] = get_the_author_meta( 'facebook' , SWP_User_Profile::get_author( $info['postID'] ) );
    	endif;

    	/**
    	 * Open Graph Publisher
    	 *
    	 */
    	if ( !empty( $this->options['facebook_publisher_url'] )) :
    		$info['meta_tag_values']['article_publisher'] = $this->options['facebook_publisher_url'];
    	elseif ( isset( $wpseo_social ) && !empty( $wpseo_social['facebook_site'] ) ) :
    		$info['meta_tag_values']['article_publisher'] = $wpseo_social['facebook_site'];
    	endif;

    	/**
    	 * Open Graph App ID
    	 *
    	 */
    	if ( !empty( $this->options['facebook_app_id'] ) ) :
    		$info['meta_tag_values']['fb_app_id'] = $this->options['facebook_app_id'];
    	elseif ( isset( $wpseo_social ) && !empty( $wpseo_social['fbadminapp'] ) ) :
    		$info['meta_tag_values']['fb_app_id'] = $wpseo_social['fbadminapp'];
    	else :
    		$info['meta_tag_values']['fb_app_id'] = '529576650555031';
    	endif;

    	return $info;
    }

    /**
     * Loops through open graph data to create <meta> tags for the <head>
     *
     * @return string The HTML for meta tags.
     */
	public function generate_og_html() {
		$meta = '';

        foreach ( $this->og_data as $key => $content ) {
			$field = str_replace( 'og_', 'og:', $key ); // map post_meta keys directly to og:keys.
			$meta .= "<meta property='$field' content='$content' >" . PHP_EOL;
		}

		$this->og_html = $meta;
	}

    /**
     * A function to compile the meta tags into HTML
     * @since  3.0.0 | 03 FEB 2018 | Added the option to disable OG tag output
     * @param  array $info The info array
     * @return array $info The modified info array
     */
    public function open_graph_html($meta_html) {
    	if ( false === is_singular() ) {
    		return $info;
    	}

    	// Don't compile them if the OG Tags are Disabled on the options page
    	if ( isset( $this->options['og_tags'] ) && false === $this->options['og_tags'] ) {
    		return $info;
    	}

    	// Check to ensure that we don't need to defer to Yoast
    	if ( isset( $info['yoast_og_setting'] ) && false != $info['yoast_og_setting'] ) {
			return $info;
		}

		if( isset( $info['meta_tag_values']['og_type'] ) && !empty( $info['meta_tag_values']['og_type'] ) ) :
			$meta_html .= PHP_EOL . '<meta property="og:type" content="'. trim( $info['meta_tag_values']['og_type'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_title'] ) && !empty( $info['meta_tag_values']['og_title'] ) ) :
			$meta_html .= PHP_EOL . '<meta property="og:title" content="'. trim( $info['meta_tag_values']['og_title'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_description'] ) && !empty( $info['meta_tag_values']['og_description'] ) ) :
			$meta_html .= PHP_EOL . '<meta property="og:description" content="'. trim( $info['meta_tag_values']['og_description'] ).'" />';
		endif;

		if( isset( $info[ 'meta_tag_values'][ 'og_image' ] )         && !empty( $info['meta_tag_values']['og_image'] ) ) :
			$meta_html .= PHP_EOL . '<meta property="og:image" content="'. trim( $info['meta_tag_values']['og_image'] ).'" />';
		endif;

		if( isset( $info[ 'meta_tag_values'][ 'og_image_width' ] )  && !empty( $info['meta_tag_values']['og_image_width'] ) ):
			$meta_html .= PHP_EOL . '<meta property="og:image:width" content="'. trim( $info['meta_tag_values']['og_image_width'] ).'" />';
		endif;
		if( isset( $info[ 'meta_tag_values'][ 'og_image_height' ] ) && !empty( $info['meta_tag_values']['og_image_height'] ) ):
			$meta_html .= PHP_EOL . '<meta property="og:image:height" content="'. trim( $info['meta_tag_values']['og_image_height'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_url'] ) && !empty( $info['meta_tag_values']['og_url'] ) ) :
			$meta_html .= PHP_EOL . '<meta property="og:url" content="'. trim( $info['meta_tag_values']['og_url'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_site_name'] ) && !empty( $info['meta_tag_values']['og_site_name'] ) ) :
			$meta_html .= PHP_EOL . '<meta property="og:site_name" content="'. trim( $info['meta_tag_values']['og_site_name'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_author'] ) && !empty( $info['meta_tag_values']['article_author'] ) ):
			$meta_html .= PHP_EOL . '<meta property="article:author" content="'. trim( $info['meta_tag_values']['article_author'] ).'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_publisher'] ) && !empty( $info['meta_tag_values']['article_publisher'] ) ):
			$meta_html .= PHP_EOL . '<meta property="article:publisher" content="'. trim( $info['meta_tag_values']['article_publisher'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_published_time'] ) && !empty( $info['meta_tag_values']['article_published_time'] ) ):
			$meta_html .= PHP_EOL . '<meta property="article:published_time" content="'. trim( $info['meta_tag_values']['article_published_time'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['article_modified_time'] ) && !empty( $info['meta_tag_values']['article_modified_time'] ) ):
			$meta_html .= PHP_EOL . '<meta property="article:modified_time" content="'. trim( $info['meta_tag_values']['article_modified_time'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['og_modified_time'] ) && !empty( $info['meta_tag_values']['og_modified_time'] ) ):
			$meta_html .= PHP_EOL . '<meta property="og:updated_time" content="'. trim( $info['meta_tag_values']['og_modified_time'] ) .'" />';
		endif;

		if( isset( $info['meta_tag_values']['fb_app_id'] ) && !empty( $info['meta_tag_values']['fb_app_id'] ) ):
			$meta_html .= PHP_EOL . '<meta property="fb:app_id" content="'. trim( $info['meta_tag_values']['fb_app_id'] ).'" />';
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
     *
     */
    public function twitter_card_values($info) {
    	if( false === is_singular() ) {
    		return $info;
    	}

    	if ( !SWP_Utility::get_option( 'twitter_cards' ) ) {
			return $info;
		}

		$twitter_use_open_graph = get_post_meta( $info['postID'], 'swp_twitter_use_open_graph', true );
        $twitter_use_open_graph = ( 'true' == $twitter_use_open_graph || false == $twitter_use_open_graph );

		if ( !$twitter_use_open_graph ) {
			$twitter_card_title 		= get_post_meta( $info['postID'] , 'swp_twitter_card_title' , true );
			$twitter_card_description 	= get_post_meta( $info['postID'] , 'swp_twitter_card_description' , true );
			$twitter_card_image 		= get_post_meta( $info['postID'] , 'swp_twitter_card_image' , true );

			if ( $twitter_card_image ) {
				$twitter_card_image = wp_get_attachment_url( $twitter_card_image );
			} else {
				$twitter_card_image = '';
			}
		}

		/**
		 * JET PACK: If ours are activated, disable theirs
		 *
		 */
		add_filter( 'jetpack_disable_twitter_cards', '__return_true', 99 );

		/**
		 * Begin by fetching the user's default custom settings
		 *
		 */
        $custom_og_title       = get_post_meta( $info['postID'] , 'swp_og_title' , true );
        if ( !empty( $custom_og_title) ) :
            $custom_og_title = htmlspecialchars( $custom_og_title );
        endif;

        $custom_og_description = get_post_meta( $info['postID'] , 'swp_og_description' , true );
        if ( !empty( $custom_og_description ) ) :
            $custom_og_description = htmlspecialchars( $custom_og_description );
        endif;

		$custom_og_image_id    = get_post_meta( $info['postID'] , 'swp_og_image' , true );
		$custom_og_image_data  = SWP_Utility::get_meta_array( $info['postID'], 'swp_og_image_data' );
		$custom_og_image_url   = $custom_og_image_data[0];
		$user_twitter_handle   = get_the_author_meta( 'swp_twitter' , SWP_User_Profile::get_author( $info['postID'] ) );

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
		 * The Twitter Card Site
		 *
		 */
		$twitter_id = SWP_Utility::get_option( 'twitter_id' );
		if ( !empty( $twitter_id ) ) :
			$twitter_id = trim( $twitter_id );
			$info['meta_tag_values']['twitter_site'] = '@' . str_replace( '@' , '' , $twitter_id );
		endif;

		$author = SWP_User_Profile::get_author( $info['postID'] );
        $author_twitter_handle = get_the_author_meta( 'swp_twitter' , $author );

		if ($author_twitter_handle) {
			$twitter_id = trim( $author_twitter_handle );
		}

		if ( !empty( $twitter_id) ) {
			$info['meta_tag_values']['twitter_creator'] = '@' . str_replace( '@' , '' , $twitter_id );
		}

		/**
		 * TWITTER TITLE
		 *
		 */
		 if( !$twitter_use_open_graph && !empty( $twitter_card_title ) ):
             $info['meta_tag_values']['twitter_title'] = $twitter_card_title;
         elseif ( !empty( $custom_og_title ) ):
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
		if( !$twitter_use_open_graph && !empty( $twitter_card_description ) ):
 			$info['meta_tag_values']['twitter_description'] = $twitter_card_description;
 		elseif ( !empty( $custom_og_description ) ):
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
		 if( !$twitter_use_open_graph && !empty( $twitter_card_image ) ):
  			$info['meta_tag_values']['twitter_image'] = $twitter_card_image;
  		elseif ( !empty( $custom_og_image_url ) ):
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



        return $info;
    }

	/**
     *  Generate the Twitter Card meta fields HTML
     *
     * This function will take the values for the Twitter Cards and convert
     * those values into HTML to be output to the screen.
     *
     * @since 2.1.4
     * @access public
     * @param array $info An array of information about the post
     * @return array $info The modified array
     *
     */
    public function twitter_card_html($meta_html) {

    	if( false === is_singular() ) {
    		return $info;
    	}

    	if ( isset( $this->options['twitter_cards'] ) ) :

    		if( isset( $info['meta_tag_values']['twitter_card'] ) && !empty( $info['meta_tag_values']['twitter_card'] ) ) :
    			$meta_html .= PHP_EOL . '<meta name="twitter:card" content="'. trim( $info['meta_tag_values']['twitter_card'] ) .'">';
    		endif;

    		if( isset( $info['meta_tag_values']['twitter_title'] ) && !empty( $info['meta_tag_values']['twitter_title'] ) ) :
    			$meta_html .= PHP_EOL . '<meta name="twitter:title" content="' . trim( $info['meta_tag_values']['twitter_title'] ) . '">';
    		endif;

    		if( isset( $info['meta_tag_values']['twitter_description'] ) && !empty( $info['meta_tag_values']['twitter_description'] ) ) :
    			$meta_html .= PHP_EOL . '<meta name="twitter:description" content="' . trim( $info['meta_tag_values']['twitter_description'] ) . '">';
    		endif;

    		if( isset( $info['meta_tag_values']['twitter_image'] ) && !empty($info['meta_tag_values']['twitter_image']) ):
    			$meta_html .= PHP_EOL . '<meta name="twitter:image" content="' . trim( $info['meta_tag_values']['twitter_image'] ) . '">';
    		endif;

    		if ( isset( $info['meta_tag_values']['twitter_site'] ) && !empty( $info['meta_tag_values']['twitter_site'] ) ) :
    			$meta_html .= PHP_EOL . '<meta name="twitter:site" content="' . trim( $info['meta_tag_values']['twitter_site'] ) . '">';
    		endif;

    		if ( isset( $info['meta_tag_values']['twitter_creator'] ) && !empty( $info['meta_tag_values']['twitter_creator'] ) ) :
    			$meta_html .= PHP_EOL . '<meta name="twitter:creator" content="' . trim( $info['meta_tag_values']['twitter_creator'] ) . '">';
    		endif;

    	endif;

    	return $meta_html;
    }

    /**
     * Verifies that the color has been properly set.
     *
     * @since 3.0.8 | MAY 23 2018 | Created the method.
     * @param string $hex The color to check.
     * @return string $hex The sanitized color string.
     *
     */
    private function parse_hex_color( $hex ) {
        if ( !isset( $hex ) ) :
            //* Default to a dark grey.
            return  "#333333";
        endif;

        if ( strpos( $hex, "#" !== 0 ) ) :
            $hex = "#" . $hex;
        endif;

        return $hex;
    }

    /**
     * Localizes the custom color settings from admin.
     *
     * @since  3.0.8 | MAY 23 2018 | Created the method.
     * @param  none
     * @return void
     *
     */
    private function establish_custom_colors() {

        //* Static custom color.
        if ( SWP_Utility::get_option('default_colors') == 'custom_color' || SWP_Utility::get_option('single_colors') == 'custom_color' || SWP_Utility::get_option('hover_colors') == 'custom_color' ) :

            $custom_color = $this->parse_hex_color( $this->options['custom_color'] );
            $this->custom_color = $custom_color;

        else :
            $this->custom_color = '';
        endif;

        //* Float custom color.
        if ( SWP_Utility::get_option('float_default_colors') == 'float_custom_color' ||  SWP_Utility::get_option('float_single_colors') == 'float_custom_color' ||  SWP_Utility::get_option('float_hover_colors') == 'float_custom_color' ) :

            if ( true === $this->options['float_style_source'] ) :
                //* Inherit the static button style.
                $this->float_custom_color = $this->custom_color;
            else :
                $this->float_custom_color = $this->parse_hex_color( $this->options['float_custom_color'] );
            endif;

        else :
            $this->float_custom_color = '';
        endif;

        //* Static custom outlines.
        if ( SWP_Utility::get_option('default_colors') == 'custom_color_outlines' ||  SWP_Utility::get_option('single_colors') == 'custom_color_outlines' ||  SWP_Utility::get_option('hover_colors') == 'custom_color_outlines' ) :

            $custom_color_outlines = $this->parse_hex_color( $this->options['custom_color_outlines'] );
            $this->custom_color_outlines = $custom_color_outlines;

        else:
            $this->custom_color_outlines = '';
        endif;

        if (  SWP_Utility::get_option('float_default_colors') == 'float_custom_color_outlines' ||  SWP_Utility::get_option('float_single_colors') == 'float_custom_color_outlines' ||  SWP_Utility::get_option('float_hover_colors') == 'float_custom_color_outlines' ) :
            if ( true === $this->options['float_style_source'] ) :

                //* Inherit the static button style.
                $this->float_custom_color_outlines = $this->custom_color_outlines;
            else:
                $this->float_custom_color_outlines = $this->parse_hex_color( $this->options['float_custom_color_outlines'] );
            endif;

        else:
            $this->float_custom_color_outlines = '';
        endif;

    }


	/**
	 * A function to render custom color CSS
	 *
	 * @since  3.0.8 | 25 MAY 2018 | Moved the .swp_social_panel to a variable to avoid making
	 *                               the default override the hover states on the floaters.
	 * @param  boolean $floating True = floating CSS, False = non-floating CSS.
	 * @return string            The CSS to be output.
	 *
	 */
    private function get_css( $floating = false ) {
        $float = '';
        $class = '';
		$panel = '';
        $custom_color = $this->custom_color;
        $custom_outlines = $this->custom_color_outlines;

        if ( $floating ) {
            $float = 'float_';
            $class = '.swp_social_panelSide';
            $custom_color = $this->float_custom_color;
            $custom_outlines = $this->float_custom_color_outlines;
        } else {
			$panel = '.swp_social_panel';
		}

        $css = '';


		/**
		 * DEFAULT
		 *
		 *
		 */
        // Default: Custom Color
        if ( SWP_Utility::get_option($float . "default_colors") === $float . "custom_color" ) :
            $css .= "

            $class.swp_default_custom_color a
                {color:white}
            $class$panel.swp_default_custom_color .nc_tweetContainer
                {
                    background-color:" . $custom_color . ";
                    border:1px solid " . $custom_color . ";
                }
            ";
        endif;

		// Default: Custom Outlines
        if ( SWP_Utility::get_option($float . "default_colors") === $float . "custom_color_outlines" ) :
                $css .= "

            $class.swp_default_custom_color_outlines a
                {color: " . $custom_outlines . "}
            $class.swp_default_custom_color_outlines .nc_tweetContainer
                {
                    background-color: transparent ;
                    border:1px solid " . $custom_outlines . " ;
                }
            ";
        endif;


		/**
		 * INDIVIDUAL
		 *
		 *
		 */
        // Individual: Custom Color
        if ( SWP_Utility::get_option($float . "single_colors") === $float . "custom_color" ) :
            $css .= "

            html body $class$panel.swp_individual_custom_color .nc_tweetContainer:not(.total_shares):hover a
                {color:white !important}
            html body $class$panel.swp_individual_custom_color .nc_tweetContainer:not(.total_shares):hover
                {
                    background-color:" . $custom_color . "!important;
                    border:1px solid " . $custom_color . "!important;
                }
            ";
        endif;

        // Individual: Custom Outlines
        if ( SWP_Utility::get_option($float . "single_colors") === $float . "custom_color_outlines" ) :
            $css .= "

            html body $class.swp_individual_custom_color_outlines .nc_tweetContainer:not(.total_shares):hover a
                {color:" . $custom_outlines . " !important}
            html body $class.swp_individual_custom_color_outlines .nc_tweetContainer:not(.total_shares):hover
                {
                    background-color: transparent !important;
                    border:1px solid " . $custom_outlines . "!important ;
                }
            ";
        endif;


		/**
		 * OTHER
		 *
		 *
		 */
        // Other: Custom Color
        if ( SWP_Utility::get_option($float . "hover_colors") === $float . "custom_color" ) :
            $css .= "

            body $class$panel.swp_other_custom_color:hover a
                {color:white}
            body $class$panel.swp_other_custom_color:hover .nc_tweetContainer
                {
                    background-color:" . $custom_color . ";
                    border:1px solid " . $custom_color . ";
                }
            ";
        endif;

		// Other: Custom Outlines
        if (SWP_Utility::get_option($float . "hover_colors") === $float . "custom_color_outlines" ) :
            $css .= "

            html body $class.swp_other_" . $float . "custom_color_outlines:hover a
                {color:" . $custom_outlines . " }
            html body $class.swp_other_" . $float . "custom_color_outlines:hover .nc_tweetContainer
                {
                    background-color: transparent ;
                    border:1px solid " . $custom_outlines . " ;
                }
            ";
        endif;

        return $css;

    }


    /**
     * Output the CSS for custom selected colors
     *
     * Don't nest the CSS. This way it will be fully "minified" on output.
     *
     * @since  1.4.0
     * @access public
     * @param  array $info The array of information about the post
     * @return array $info The modified array
     *
     */
    public function output_custom_color( $meta_html ) {
        $static = $this->get_css();
        $floaters_on = SWP_Utility::get_option( 'floating_panel' );
        $floating = $this->get_css( $floaters_on );

        $css = $static . $floating;

        if ( !empty( $css) ) :
            $css = '<style type="text/css">' . $css . '</style>';
        endif;

        //* Replaces newlines and excessive whitespace with a single space.
        $meta_html .= trim( preg_replace( '/\s+/', ' ', $css ) );
		// $meta_html .= $css;
    	return $meta_html;
    }

    /**
     * Output custom CSS for Click To Tweet
     *
     * Note: This is done in the header rather than in a CSS file to
     * avoid having the styles called from a CDN
     *
     * @since  3.0.0
     * @access public
     * @param  array  $info An array of information about the post
     * @return array  $info The modified array
     */
    public function output_ctt_css( $meta_html ) {
        if (!empty($this->options['ctt_css']) && count($this->options)['ctt_css'] > 0) {
            // Add it to our array if we're using the frontend Head Hook
            $meta_html .= PHP_EOL . '<style id=ctt-css>' . $this->options['ctt_css'] . '</style>';

        }

        return $meta_html;
    }
}

endif;
