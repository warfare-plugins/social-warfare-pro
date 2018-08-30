<?php

/**
 * Hosts our Pro features for Pinterst.
 *
 * @since  3.2.0 | 26 JUL 2018 | Created the class.
 *
 */
class SWP_Pro_Pinterest {

    /**
     * Initialize the hooks and filters.
     *
     * @since  3.2.0 | 26 JUL 2018
     *
     */
    public function __construct() {
        if ( $this->should_bail() ) :
            return;
        endif;

        add_shortcode( 'pinterest_image', array( $this, 'pinterest_image' ) );
        add_filter( 'image_send_to_editor', array( $this, 'editor_add_pin_description'), 10, 8 );

        add_filter( 'the_content', array( $this, 'maybe_insert_pinterest_image' ), 10 );

        if ( true === SWP_Utility::get_option( 'pinterest_data_attribute' ) ) :
            add_filter( 'the_content', array( $this, 'content_add_pin_description' ) );
        endif;

        if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) :
            add_filter( 'the_content', array( $this, 'content_maybe_add_no_pin' ), 10 );
        endif;

        add_filter( 'swp_footer_scripts', array( $this, 'pinit_controls_output' ) );
        add_filter('attachment_fields_to_edit', array( $this, 'edit_media_custom_field'), 11, 2 );
        add_filter('attachment_fields_to_save', array( $this, 'save_media_custom_field'), 11, 2 );
    }

    public function should_bail() {
        if (  is_feed() || is_archive() ) :
            return true;
        endif;

        if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) :
            return true;
        endif;

        return false;
    }

    /**
     * A function to insert the Pinterest image for browser extensions
     *
     * @since  2.2.4 | 09 MAR 2017 | Created
     * @since  3.3.0 | 20 AUG 2018 | Refactored the method.
     * @access public
     *
     * @param  string $content The post content to filter
     * @return string $content The filtered content
     *
     */
    public function maybe_insert_pinterest_image( $content ) {
    	global $post;
    	$post_id = $post->ID;
    	$pin_browser_extension = get_post_meta( $post_id , 'swp_pin_browser_extension' , true );
    	$pin_browser_location = get_post_meta( $post_id , 'swp_pin_browser_extension_location' , true );

        // Bail early if not using a pinterest image.
        if ( 'off' == $pin_browser_extension ) :
            return $content;
        endif;

        if ( 'on' === $pin_browser_extension && !SWP_Utility::get_option( 'pin_browser_extension' ) ) :
             return $content;
        endif;

        $pinterest_image_url = get_post_meta( $post_id, 'swp_pinterest_image_url' , true );

        if ( empty( $pinterest_image_url ) || false === $pinterest_image_url ) :
            return $content;
        endif;

        // This post is using some kind of Pinterest Image, so prepare the data to compile an image.

        $location = $pin_browser_location == 'default' ? SWP_Utility::get_option( 'pinterest_image_location' ) : $pin_browser_location;


        //* Set up the Pinterest username, if it exists.
        $id = SWP_Utility::get_option( 'pinterest_id' );
        $pinterest_username = $id ? ' via @' . str_replace( '@' , '' , $id ) : '';
        $pinterest_description = get_post_meta( $post_id , 'swp_pinterest_description' , true );

    	// If there is no custom description, use the post Title
    	if( false === $pinterest_description || empty( $pinterest_image_url ) ):
    		$pinterest_description = urlencode( html_entity_decode( get_the_title() . $pinterest_username, ENT_COMPAT, 'UTF-8' ) );
    	endif;

    	// If the image is hidden, give it the swp_hidden_pin_image class.
    	if( 'hidden' === $location ) :

    		$image_html = '<img class="no_pin swp_hidden_pin_image" src="' . $pinterest_image_url .
                          '" data-pin-url="' . get_the_permalink() .
                          '" data-pin-media="' . $pinterest_image_url .
                          '" alt="' . $pinterest_description .
                          '" data-pin-description="' . $pinterest_description .
                          '" />';

    		$content .= $image_html;

        // Put the image in a container otherwise
    	else :

            $extra_class = 'swp-pinterest-image-' . $location;

            $image_html = '<div class="swp-pinterest-image-wrapper ' . $extra_class . '">
                              <img class="swp-pinterest-image " src="' . $pinterest_image_url .
                            '" alt="' . $pinterest_description .
                            '" data-pin-url="' . get_the_permalink() .
                            '" data-pin-media="' . $pinterest_image_url .
                            '" data-pin-description="' . $pinterest_description .
                            '" />
                          </div>';

    		if ('top' === $location):
    			$content = $image_html . $content;
    		endif;

    		if ('bottom' === $location):
    			$content .= $image_html;
    		endif;

    	endif;

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
        //* Prefer the user-defined Pin Description.
        $description = get_post_meta( $id, 'swp_pinterest_description', true );

        if ( empty( $description ) ) :
            $image = get_post( $id );
            //* The description as set in the Media Gallery.
            $description = $image->post_content;
        endif;

        //* Pinterest limits the description to 500 characters.
        if ( empty( $description ) || strlen( $description ) > 500 ) {
            $alt = get_post_meta( $id, '_wp_attachment_image_alt', true );

            if ( !empty( $alt ) ) :
                $description = $alt;
            else:
                //* Use the caption instead.
                $description = $image->post_excerpt;
            endif;
        }

        if ( empty( $description ) ) :
            $description = $image->post_title;
        endif;

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
                $style = 'style="text-align: left";';
                break;
            case 'right':
                $style = 'style="text-align: right";';
                break;
            case 'center':
                $style = 'style="text-align: center"';
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
        $description = $description = get_post_meta( $image_id, 'swp_pinterest_description', true );

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


        if ( class_exists( 'DOMDocument') ) :

            //* DOMDocument works better with an XML delcaration.
            if ( false === strpos( $the_content, '?xml version' ) ) :
                $xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
                $html = $xml_statement . $the_content;
                $added_xml_statement = true;
            else :
                $html = $the_content;
            endif;

            libxml_use_internal_errors( true );
            $doc = @DOMDocument::loadHTML( $html );
            libxml_use_internal_errors( false );
            libxml_clear_errors();

            $img = $doc->getElementsByTagName("img")[0];

            $replacement = $img->cloneNode();
            $replacement->setAttribute( "data-pin-description", $description );

            $img->parentNode->replaceChild($replacement, $img);

            $html = $doc->saveHTML();

            if ( $added_xml_statement ) :
                $html = str_replace( $xml_statement, '', $the_content );
            endif;

        else:
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
        endif;

        return $html;
    }

    public function content_add_pin_description( $the_content ) {
        global $post;

        $description_fallback = $description = get_post_meta( $post->ID, 'swp_pinterest_description', true );


        if ( class_exists( 'DOMDocument') ) :

            //* DOMDocument works better with an XML delcaration.
            if ( false === strpos( $the_content, '?xml version' ) ) :
                $xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
                $html = $xml_statement . $the_content;
                $added_xml_statement = true;
            else :
                $html = $the_content;
            endif;

            libxml_use_internal_errors( true );
            $doc = DOMDocument::loadHTML( $html );
            libxml_use_internal_errors( false );
            libxml_clear_errors();

            $imgs = $doc->getElementsByTagName("img");

            foreach( $imgs as $img ) {

                if ( $img->hasAttribute( "data-pin-description" ) ) {
                    continue;
                }

                $replacement = $img->cloneNode();

                if ( 'alt_text' == SWP_Utility::get_option( 'pinit_image_description' ) && $img->hasAttribute( 'alt' ) ) {
                    $replacement->setAttribute( "data-pin-description", $img->getAttribute( "alt" ) );
                } else if ( !empty( $description_fallback ) ) {
                    $replacement->setAttribute( "data-pin-description", $description_fallback );
                } else {
                    continue;
                }

                $img->parentNode->replaceChild($replacement, $img);

            }

            $the_content = $doc->saveHTML();

            if ( $added_xml_statement ) :
                $the_content = str_replace( $xml_statement, '', $the_content );
            endif;

        endif;

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

        if ( !class_exists( 'DOMDocument') ) :
            return $the_content;
        endif;

        $images = get_attached_media( 'image' );

        //* Filter image array to only include those that opted out of Pin Hover
        $opt_out_images = array_filter($images, function($image) {
            return 1 == (bool) get_post_meta( $image->ID, 'swp_pin_button_opt_out', true );
            return (bool) $checked == 1;
        });

        //* All images use the pin on hover feature.
        if ( 0 == count( $opt_out_images ) ) :
            return $the_content;
        endif;

        /**
         * Begin processing the DOM to add a no-pin class to targeted images.
         */

        //* DOMDocument works better with an XML delcaration.
        if ( false === strpos( $the_content, '?xml version' ) ) :
            $xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
            $html = $xml_statement . $the_content;
            $added_xml_statement = true;
        else :
            $html = $the_content;
        endif;

        libxml_use_internal_errors( true );
        $doc = DOMDocument::loadHTML( $html );
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

        if ( $added_xml_statement ) :
            $the_content = str_replace( $xml_statement, '', $the_content );
        endif;


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
    public function pinterest_image( $atts ) {
        global $post;

        $whitelist = ['id', 'width', 'height', 'class', 'alignment'];

        //* Instantiate and santiize each of the variables passed as attributes.
        foreach( $whitelist as $var ) {
            $$var = isset( $atts[$var] ) ? sanitize_text_field( trim ( $atts[$var] ) ) : "";
        }

        if ( empty( $id ) && is_object( $post )) :
            $id = get_post_meta( $post->ID, 'swp_pinterest_image', true);
            $src = get_post_meta( $post->ID, 'swp_pinterest_image_url', true );
        endif;

        if ( !is_numeric( $id ) ) {
            return;
        }

        $image = get_post( $id );

        $description = SWP_Pro_Pinterest::get_pin_description( $id );

        if ( !empty( $width ) && !empty( $height ) ):
            $dimensions = ' width="' . $width . '"';
            $dimensions .= ' height="' . $height . '"';
        else :
            $dimensions = "";
        endif;

        if ( empty( $class ) ) :
            $class = "swp-pinterest-image";
        endif;

        $alignment = SWP_Pro_Pinterest::get_alignment_style( $alignment );

        if ( 1 == (bool) get_post_meta( $image->ID, 'swp_pin_button_opt_out', true ) ) :
            $class .= ' no-pin ';
        endif;

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
    		if ( 'custom' == SWP_Utility::get_option( 'pinit_image_source' ) && get_post_meta( get_the_ID() , 'swp_pinterest_image_url' , true ) ):
    			$pin_vars['image_source'] = get_post_meta( get_the_ID() , 'swp_pinterest_image_url' , true );
    		endif;

    		// Set the description Source
    		if('custom' == SWP_Utility::get_option( 'pinit_image_description' ) && get_post_meta( get_the_ID() , 'swp_pinterest_description' , true ) ):
    			$pin_vars['image_description'] = get_post_meta( get_the_ID() , 'swp_pinterest_description' , true );
    		endif;
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

        if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) :

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
        endif;

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

        if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) :
            $checked = isset( $attachment['swp_pin_button_opt_out'] );
            update_post_meta( $post['ID'], 'swp_pin_button_opt_out', $checked );
        endif;

        return $post;
    }
}