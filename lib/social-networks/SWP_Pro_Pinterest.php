<?php

/**
 * Hosts our Pro features for Pinterst.
 *
 * This class contains a host of methods that at some pretty cool Pinterest
 * related functionality to the plugin.
 *
 * 1. It creates and controls the output for the [pinterest_image] shortcode.
 * 2. It adds Pinterest images to posts via the_content filter.
 * 3. It adds data-pin-description to images dynamically via the_content filter.
 *
 * @since  3.2.0 | 26 JUL 2018 | Created the class.
 *
 */
class SWP_Pro_Pinterest {


    /**
     * Initialize the hooks and filters.
     *
     * @since  3.2.0 | 26 JUL 2018 | Created
     * @since  3.3.2 | 14 SEP 2018 | Added admin an singular checks.
     * @param  void
     * @return void
     *
     */
    public function __construct() {
        if ( $this->should_bail() ) {
            return;
        }

        //* Admin hooks for editing pinterest-specific content.
        $this->add_admin_actions();

        //* Defer to a later hook so `global $post` is defined.
        add_filter( 'template_redirect', array( $this, 'add_frontend_actions' ) );
        add_filter( 'swp_footer_scripts', array( $this, 'pinit_controls_output' ) );
    }


	/**
	 * There are certain conditions under which we should simply exclude all of
	 * the functionality in this class to avoid conflicts.
	 *
	 * @since 3.2.0 | 26 JUL 2018 | Created
	 * @param  void
	 * @return bool True: bail; false: continue
	 *
	 */
    public function should_bail() {

		// Bail if another plugin is causing a conflict with this one.
        if ( Social_Warfare::has_plugin_conflict() ) {
            return true;
        }

		// We don't want these Pinterest features on feeds and archives.
		if( is_feed() || is_archive() ) {
			return true;
		}

		// We don't need Pinterest images on pages delivered via AMP.
        if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
            return true;
        }

        return false;
    }


	/**
	 * Add the Pinterest related admin hooks and filters.
	 *
	 * @since  3.2.3 | 17 SEP 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
    public function add_admin_actions() {
		if ( false == is_admin() ) {
            return;
        }

	    add_filter( 'image_send_to_editor', array( $this, 'editor_add_pin_description'), 10, 8 );
        add_filter( 'attachment_fields_to_edit', array( $this, 'edit_media_custom_field'), 11, 2 );
        add_filter( 'attachment_fields_to_save', array( $this, 'save_media_custom_field'), 11, 2 );
    }


	/**
	 * Add the Pinterest related frontend hooks and filters.
	 *
	 * This is deferred to the "template_redirect" hook so that we have access
	 * to the necessary conditionals (e.g. is_singular()) in order to control
	 * what gets queued up where and when.
	 *
	 * @since  3.2.3 | 17 SEP 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
    public function add_frontend_actions() {

		/**
		 * By using the callback '__return_false', it will cause the shortcode
		 * to be processed but not output anything. This will stop the raw
		 * shortcode text [pinterest_image] from appearing on excerpts.
		 *
		 */
        if ( false == is_singular() ) {
            add_shortcode( 'pinterest_image' , '__return_false' );
            return;
        }


		/**
		 * This is the filter that will add the data-pin-description to each
		 * image in the post ensuring that Pinterest and Tailwind browser
		 * extensions will have descriptions to use when sharing.
		 *
		 */
        if ( true === SWP_Utility::get_option( 'pinterest_data_attribute' ) ) {
			add_filter( 'the_content' , array( $this, 'content_add_pin_description' ) );
        }


		/**
		 * This is the filter that adds the "no-pin" class to images that the
		 * user has opted out of the Image Hover Pin buttons.
		 *
		 */
        if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) {
			add_filter( 'the_content' , array( $this, 'content_maybe_add_no_pin' ) );
        }

		add_shortcode( 'pinterest_image', array( $this, 'pinterest_image_shortcode' ) );
		add_filter( 'the_content', array( $this, 'maybe_insert_pinterest_image') ) ;
    }


    /**
     * A function to insert the Pinterest image for browser extensions
     *
     * This will add the user-defined, post-level Pinterest image directly into
     * the post content complete with the necessary data-pin-description and
     * other attributes. When Pinterest's official browser extension
     * and others like Tailwind scrape the page, they will pick up and see the
     * Pinterest optimized image along with the Pinterest optimized description.
     *
     * @since  2.2.4 | 09 MAR 2017 | Created
     * @since  3.3.0 | 20 AUG 2018 | Refactored the method.
     * @since  3.3.2 | 13 SEP 2018 | Added check for is_singular()
     * @since  3.4.0 | 01 NOV 2018 | Added check for global on/off status.
     * @param  string $content The post content to filter
     * @return string $content The filtered content
     *
     */
    public function maybe_insert_pinterest_image( $content ) {
    	global $post;
    	$post_id                = $post->ID;
    	$meta_browser_extension = get_post_meta( $post_id, 'swp_pin_browser_extension' , true );
    	$pin_browser_location   = get_post_meta( $post_id, 'swp_pin_browser_extension_location' , true );
        $pinterest_image_url    = get_post_meta( $post_id, 'swp_pinterest_image_url' , true );


		/**
		 * If the option is turned off globally, and the post level option is
		 * set to default, bail out and keep this feature turned off. If the
		 * post level option is set to off, it will get caught below.
		 *
		 */
		if ( false == SWP_Utility::get_option( 'pin_browser_extension' ) && 'default' == $meta_browser_extension ) {
			return $content;
		}


        /**
         * Bail early if the Pinterest browser image is explicitly turned to the
         * off position at the post level.
         *
         */
        if ( 'off' == $meta_browser_extension ) {
            return $content;
        }

		// Bail if this post doesn't have a specifically defined Pinterest image.
        if ( empty( $pinterest_image_url ) || false === $pinterest_image_url ) {
			//* Check to see if the image is set, even if the url is not.
			$pinterest_image_id = get_post_meta( $post_id, 'swp_pinterest_image', true );

			if ( !$pinterest_image_id ) {
				return $content;
			}

			$pinterest_image_url = wp_get_attachment_url( $pinterest_image_id, 'full' );
        }

        // This post is using some kind of Pinterest Image.
        $location = $pin_browser_location == 'default' ? SWP_Utility::get_option( 'pinterest_image_location' ) : $pin_browser_location;

        //* Set up the Pinterest username, if it exists.
        $id = SWP_Utility::get_option( 'pinterest_id' );
        $pinterest_username = $id ? ' via @' . str_replace( '@' , '' , $id ) : '';
        $pinterest_description = get_post_meta( $post_id , 'swp_pinterest_description' , true );

    	// If there is no custom description, use the post Title
    	if ( false == $pinterest_description || empty( $pinterest_image_url ) ) {
    		$pinterest_description = get_the_title();
    	}

    	// If the image is hidden, give it the swp_hidden_pin_image class.
    	if ( 'hidden' === $location ) {

    		$image_html = '<img class="no_pin swp_hidden_pin_image swp-pinterest-image" src="' . $pinterest_image_url .
                          '" data-pin-url="' . get_the_permalink() .
                          '" data-pin-media="' . $pinterest_image_url .
                          '" alt="' . $pinterest_description .
                          '" data-pin-description="' . $pinterest_description .
                          '" />';

    		$content .= $image_html;

        // Put the image in a container otherwise
        } else {

            $extra_class = 'swp-pinterest-image-' . $location;

            $image_html = '<div class="swp-pinterest-image-wrapper ' . $extra_class . '">
                              <img class="swp-pinterest-image " src="' . $pinterest_image_url .
                            '" alt="' . $pinterest_description .
                            '" data-pin-url="' . get_the_permalink() .
                            '" data-pin-media="' . $pinterest_image_url .
                            '" data-pin-description="' . $pinterest_description .
                            '" />
                          </div>';

    		if ('top' === $location) {
    			$content = $image_html . $content;
    		}

    		if ('bottom' === $location) {
    			$content .= $image_html;
    		}

    	}

    	return $content;
    }


    /**
     * Get the Pinterest description set by the Admin, or a fallback.
     *
     * @param  int $id The Post to check for a pinterest description.
     * @return string $html Our version of the markup.
     *
     */
    public static function get_pin_description( $id ) {
       // die(__METHOD__);
        //* (1) Prefer the user-defined Pin Description.
        $description = get_post_meta( $id, 'swp_pinterest_description', true );

        if ( empty( $description ) ) {
            $image = get_post( $id );
            //* The description as set in the Media Gallery.
            $description = $image->post_content;
        }

        //* Pinterest limits the description to 500 characters.
        if ( empty( $description ) || strlen( $description ) > 500 ) {
            $alt = get_post_meta( $id, '_wp_attachment_image_alt', true );

            if ( !empty( $alt ) ) {
                $description = $alt;
            } else {
                //* Use the caption instead.
                $description = $image->post_excerpt;
            }
        }

        if ( empty( $description ) ) {
            $description = $image->post_title;
        }

        return $description;
    }


    /**
     * Get the image alignment style for the image wrapper.
     *
     * @param  string $alignment One of '', 'center', 'left', 'right'
     * @return string $style The style declaration for an image wrapper element.
     *
     */
    public static function get_alignment_style( $alignment = '' ) {
        switch ( $alignment ) {
            default:
                $style = '';
            case 'left':
                $style = 'style="text-align: left;"';
                break;
            case 'right':
                $style = 'style="text-align: right;"';
                break;
            case 'center':
                $style = 'style="text-align: center;"';
                break;
        }

        return $style;
    }


    /**
     *
     *
     * This filter callback receives many variables.
     * $html is the fully rendered HTML that WordPress created.
     * We are bascially ignoring it and creating our own.
     *
     * @return $html Our version of the markup.
     */
    public function editor_add_pin_description( $html, $image_id, $caption, $title, $alignment, $url, $size = "", $alt ) {
        $description = get_post_meta( $image_id, 'swp_pinterest_description', true );

        if ( empty( $description ) ) {
            //* We only permastore the pin description when they have specifically set one for this image.
            return $html;
        }

        if ( is_string( $size ) ) {
            $size = $this->get_image_size( $size );
            $width = $size['width'];
            $height = $size['height'];
        } else {
            $width = '';
            $height = '';
        }


        if ( class_exists( 'DOMDocument' ) ) {

            //* DOMDocument works better with an XML delcaration.
            if ( false === strpos( $html, '?xml version' ) ) {
                $xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
                $html = $xml_statement . $html;
                $added_xml_statement = true;
            }

            //* Prevent warnings for 'Invalid Tag' on HTML5 tags.
            libxml_use_internal_errors( true );
            $doc = new DOMDocument();
            $doc->loadHTML( $html );

            libxml_use_internal_errors( false );
            libxml_clear_errors();

            //* There is only one image in the content passed in the filter.
            $img = $doc->getElementsByTagName( "img" )[0];

            $replacement = $img->cloneNode();
            $replacement->setAttribute( "data-pin-description", $description );

            $img->parentNode->replaceChild( $replacement, $img );

            $html = $doc->saveHTML();

            if ( $added_xml_statement ) {
                $html = str_replace( $xml_statement, '', $html );
            }

        } else {
            $alignment = $this::get_alignment_style( $alignment );
            $html = '<div class="swp-pinterest-image-wrap" ' . $alignment . '>';

                $html .= '<img ';
                    $html .= ' src="' . $url . '"';
                    $html .= ' width="' . $width . '"';
                    $html .= ' height="' . $height . '"';
                    $html .= ' class="swp-pinterest-image"';
                    $html .= ' data-pin-description="' . $description . '"';
                    $html .= ' title="' . $title . '"';
                    $html .= ' alt="' . $alt . '"';
                $html .= "/>";

            $html .= '</div>';
        }

        return $html;
    }


	/**
	 * Add data-pin-descriptions to all images that don't have one.
	 *
	 *
     * Order of Precedence:
     * 1. The user defined pinterest description for the given image.
     * 2. The ALT text for the image.
     * 3. The pinterest description set for the post.
     * 4. The title and excerpt for the post.
     *
     * @since  3.3.2 | 12 SEP 2018 | Refined order of precedence logic
	 * @param  string $the_content String of text for the content.
	 * @return string The modified content text.
	 *
	 */
    public function content_add_pin_description( $the_content ) {
        global $post;

        $post_pinterest_description = get_post_meta( $post->ID, 'swp_pinterest_description', true );

        if ( class_exists( 'DOMDocument' ) ) {

            //* DOMDocument works better with an XML delcaration.
            if ( false === strpos( $the_content, '?xml version' ) ) {
                $xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
                $html = $xml_statement . $the_content;
                $added_xml_statement = true;
            } else {
                $html = $the_content;
            }

            //* Prevent warnings for 'Invalid Tag' on HTML5 tags. ibxml_use_internal_errors( true );
            $doc = new DOMDocument();
            libxml_use_internal_errors( true );
            $doc->loadHTML( $html );
            libxml_use_internal_errors( false );
            libxml_clear_errors();

            $imgs = $doc->getElementsByTagName("img");

            foreach( $imgs as $img ) {

                if ( $img->hasAttribute( "data-pin-description" ) ) {
                    continue;
                }

                $replacement = $img->cloneNode();

				// Check for alt text
				$alt_attribute = $img->getAttribute( 'alt' );
                if ( 'alt_text' == SWP_Utility::get_option( 'pinit_image_description' ) && !empty( $alt_attribute ) ) {
                    $replacement->setAttribute( "data-pin-description", $alt_attribute );

				// Check for the post pinterest description
				} else if ( !empty( $post_pinterest_description ) ) {
                    $replacement->setAttribute( "data-pin-description", $post_pinterest_description );

				// Use the post title and excerpt.
                } else {

					$title = get_the_title();
					$excerpt = SWP_Utility::get_the_excerpt( $post->ID );
					$description = $title . ': ' . $excerpt;
					$description = str_replace( '"', '\'', $description );
					$replacement->setAttribute( "data-pin-description", $description );

                }

                $img->parentNode->replaceChild($replacement, $img);

            }

            $the_content = $doc->saveHTML();

            if ( $added_xml_statement ) {
                $the_content = str_replace( $xml_statement, '', $the_content );
            }
		}

        return $the_content;
    }

    /**
     * Adds the 'no-pin' CSS class to an image for opted-out images.
     *
     * @param  string $the_content The post content, passsed by WordPress
     * @return string $the_content The filtered content, with or without classnames.
     *
     */
    public function content_maybe_add_no_pin( $the_content ) {
        global $post;

        if ( !class_exists( 'DOMDocument') ) {
            return $the_content;
        }

        $images = get_attached_media( 'image' );

        //* Filter image array to only include those that opted out of Pin Hover
        $opt_out_images = array_filter($images, function($image) {
            return 1 == (bool) get_post_meta( $image->ID, 'swp_pin_button_opt_out', true );
        });

        //* All images use the pin on hover feature.
        if ( 0 == count( $opt_out_images ) ) {
            return $the_content;
        }

        /**
         * Begin processing the DOM to add a no-pin class to targeted images.
         */

        //* DOMDocument works better with an XML delcaration.
        if ( false === strpos( $the_content, '?xml version' ) ) {
            $xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
            $html = $xml_statement . $the_content;
            $added_xml_statement = true;
        } else {
            $html = $the_content;
        }

        libxml_use_internal_errors( true );
        $doc = new DOMDocument();
        $doc->loadHTML( $html );
        libxml_use_internal_errors( false );
        libxml_clear_errors();

        $dom_images = $doc->getElementsByTagName("img");

        //* Replace existing nodes with updated 'no-pin' notes.
        foreach( $dom_images as $image ) {
            $src = $image->getAttribute('src');

            foreach( $opt_out_images as $i ) {
                $href = wp_get_attachment_url( $i->ID );
                $guid = $i->guid;

                if ( $href == $src || $guid == $src ) {
                    $img = $image->cloneNode();
                    $class = $img->getAttribute('class');

                    $class = $class ? $class . ' no-pin ' : 'no-pin';

                    $img->setAttribute('class', $class);

                    $image->parentNode->replaceChild($img, $image);
                }
            }
        }

        $the_content = $doc->saveHTML();

        if ( $added_xml_statement ) {
            $the_content = str_replace( $xml_statement, '', $the_content );
        }


        return $the_content;
    }

    /**
	 * Create the [pinterest_image] shortcode.
	 *
	 * @since  3.2.0 | 25 JUL 2018 | Created
	 * @param  array $atts  Shortcode parameters
	 * @return string       The rendered HTML for a Pinterest image.
	 *
	 */
    public function pinterest_image_shortcode( $atts ) {

        global $post;


		/**
		 * This is a list of acceptable arguments that can be passed into
		 * the shortcode allowing the user some level of control as to how
		 * the Pinterest image will be displayed on output. Anything outside
		 * of this list will be filtered out of the passed arguments.
		 *
		 */
        $whitelist = ['id', 'width', 'height', 'class', 'alignment'];
        foreach( $whitelist as $var ) {
            $$var = isset( $atts[$var] ) ? sanitize_text_field( trim ( $atts[$var] ) ) : "";
        }


		/**
		 * If an ID was not passed in as an argument, but we have a valid
		 * $post object, we can use that to find the Pinterest image for
		 * the post in which the shortcode is being used.
		 *
		 */
        if ( empty( $id ) && is_object( $post )) {
            $id = get_post_meta( $post->ID, 'swp_pinterest_image', true);
            $src = get_post_meta( $post->ID, 'swp_pinterest_image_url', true );
        }


		/**
		 * If we were not able to find a specific Pinterest image for this
		 * post then we will attempt to see if the post has a featured
		 * image, but we will only do this if the user has set the "featured
		 * image" to be used as the fallback on the options page.
		 *
		 */
        if ( empty( $id ) && 'featured' == SWP_Utility::get_option( 'pinterest_fallback' ) ) {
            $id = get_post_thumbnail_id();
            $src = wp_get_attachment_image_src( $id, 'full' )[0];
        }


        /**
         * No ID was provided by shortcode attribute OR by setting a
         * pinterest image in the meta box, OR by looking for a featured
         * image.
         *
         */
		if ( empty( $id ) ) {
			return;
		}

        $image = get_post( $id );

        $description = SWP_Pro_Pinterest::get_pin_description( $id );

        //* If the user provided width & height attributes.
        if ( !empty( $width ) && !empty( $height ) ) {
            $dimensions = ' width="' . $width . '"';
            $dimensions .= ' height="' . $height . '"';
        } else {
            $dimensions = "";
        }

        //* Instantiate a default class regardless of user input.
        if ( empty( $class ) ) {
            $class = "swp-pinterest-image";
        } else {
            $class .= " swp-pinterest-image ";
        }

        //* Parse the alignment from user input to inline style declaration.
        $alignment = SWP_Pro_Pinterest::get_alignment_style( $alignment );

        //* Display a Pinterest 'Save' button on hover?
        if ( 1 == (bool) get_post_meta( $image->ID, 'swp_pin_button_opt_out', true ) ) {
            $class .= ' no-pin ';
        }

        $html = '<div class="swp-pinterest-image-wrap" ' . $alignment . '>';
            $html .= '<img src="' . $src . '"';
            $html .= $alignment;
            $html .= $dimensions;
            $html .= ' class="' . $class . '"';
            $html .= ' data-pin-description="' . $description . '"';
            $html .= ' />';
        $html .= '</div>';

        return $html;
    }


    /**
     *
     * @credits
     *
     * These methods are taken from https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
     *
     */


     /**
      * Get size information for a specific image size.
      *
      * @uses   get_image_sizes()
      * @param  string $size The image size for which to retrieve data.
      * @return bool|array $size Size data about an image size or false if the size doesn't exist.
      */
     private function get_image_size( $size ) {
         $sizes = $this->get_image_sizes();

         if ( isset( $sizes[ $size ] ) ) {
             return $sizes[ $size ];
         }

         //* Return a dummy array of [$width, $height]
         return array("", "");
     }


    /**
     * Get size information for all currently-registered image sizes.
     *
     * @global $_wp_additional_image_sizes
     * @uses   get_intermediate_image_sizes()
     * @return array $sizes Data for all currently-registered image sizes.
     */
    private function get_image_sizes() {
        global $_wp_additional_image_sizes;

        $sizes = array();

        foreach ( get_intermediate_image_sizes() as $_size ) {
            if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
                $sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
                $sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
                $sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
            } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
                $sizes[ $_size ] = array(
                    'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
                    'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                    'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
                );
            }
        }

        return $sizes;
    }


    /**
     * Get the width of a specific image size.
     *
     * @uses   get_image_size()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|string $size Width of an image size or false if the size doesn't exist.
     */
    private function get_image_width( $size ) {
        if ( ! $size = $this->get_image_size( $size ) ) {
            return false;
        }

        if ( isset( $size['width'] ) ) {
            return $size['width'];
        }

        return false;
    }

    /**
     * Get the height of a specific image size.
     *
     * @uses   get_image_size()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|string $size Height of an image size or false if the size doesn't exist.
     */
    private function get_image_height( $size ) {
        if ( ! $size = $this->get_image_size( $size ) ) {
            return false;
        }

        if ( isset( $size['height'] ) ) {
            return $size['height'];
        }

        return false;
    }

    /**
     * A function to output the Pin Button option controls
     *
     * @since  2.1.4
     * @since  3.3.0 | 21 AUG 2018 | Moved from main file into SWP_Pro_Pinterest.
     * @access public
     *
     * @param  array $info An array of footer script information.
     * @return array $info A modified array of footer script information.
     *
     */
    public function pinit_controls_output( $info ) {
        $custom_pin_description = get_post_meta( get_the_ID() , 'swp_pinterest_description' , true );
		$custom_pinterest_image = get_post_meta( get_the_ID() , 'swp_pinterest_image_url' , true );

    	$pin_vars = array(
    		'enabled' => false,
    	);

    	if ( SWP_Utility::get_option( 'pinit_toggle' ) ) {
    		$pin_vars['enabled']   = true;
    		$pin_vars['hLocation'] = SWP_Utility::get_option( 'pinit_location_horizontal' );
    		$pin_vars['vLocation'] = SWP_Utility::get_option( 'pinit_location_vertical' );
    		$pin_vars['minWidth']  = str_replace( 'px', '', SWP_Utility::get_option( 'pinit_min_width' ) );
    		$pin_vars['minHeight'] = str_replace( 'px', '', SWP_Utility::get_option( 'pinit_min_height' ) );
            $pin_vars['disableOnAnchors'] = SWP_Utility::get_option( 'pinit_hide_on_anchors' );

    		// Set the image source
    		if ( 'custom' == SWP_Utility::get_option( 'pinit_image_source' ) && $custom_pinterest_image ) {
    			$pin_vars['image_source'] = $custom_pinterest_image;
    		}

    		// Set the description Source
    		if( 'custom' == SWP_Utility::get_option( 'pinit_image_description' ) && $custom_pin_description ) {
    			$pin_vars['image_description'] = $custom_pin_description;
    		}
    	}

    	$info['footer_output'] .= ' swpPinIt=' . json_encode($pin_vars) . ';';

    	return $info;
    }


    /**
     * Adds the Pinterest Description custom field when editing an image.
     *
     * @since  3.2.0 | 07 AUG 2018 | Creatd
     *
     * @param  array $form_fields The other fields present in the media editor.
     * @param  object $post The WP Attachment object.
     *
     * @return array $form_fields The filtered form fields, now including our box.
     *
     */
    public function edit_media_custom_field( $form_fields, $post ) {
        $form_fields['swp_pinterest_description'] = array(
            'label' => 'Social Warfare Pin Description',
            'input' => 'textarea',
            'value' => get_post_meta( $post->ID, 'swp_pinterest_description', true )
        );

        if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) {

            $bool = get_post_meta( $post->ID, 'swp_pin_button_opt_out', true );
            $checked = (int) $bool === 1 ? 'checked' : '';

            $form_fields['swp_pin_button_opt_out'] = array(
                'label' => 'Hover Pin Opt Out',
                'input' => 'html',
                'html'  => '<input
                               type="checkbox"
                               id="attachments-' . $post->ID . '-swp_pin_button_opt_out"
                               name="attachments[' . $post->ID . '][swp_pin_button_opt_out]"
                               ' . $checked . '
                             />',
            );
        }

        return $form_fields;
    }


    /**
     * Adds the Pinterest Description custom field when editing an image.
     *
     * @since  3.2.0 | 07 AUG 2018 | Creatd
     *
     * @param  object $post The WP Attachment object.
     * @param  array  $attachment $key => $value data about $post.
     *
     * @return array $post The updated post object.
     *
     */
    public function save_media_custom_field( $post, $attachment ) {
        update_post_meta( $post['ID'], 'swp_pinterest_description', $attachment['swp_pinterest_description'] );

        if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) {
            $checked = isset( $attachment['swp_pin_button_opt_out'] );
            update_post_meta( $post['ID'], 'swp_pin_button_opt_out', $checked );
        }

        return $post;
    }
}
