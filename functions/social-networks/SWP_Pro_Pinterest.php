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
        add_filter( 'image_send_to_editor', array( $this, 'editor_add_pin_description'), 10, 8 );
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
        $description = get_post_meta( $post->ID, 'swp_pinterest_description', true );

        if ( empty( $description ) ) :
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
    public static function get_alignment_style( $alignment ) {
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
    public function editor_add_pin_description( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
        global $post;

        $description = $this::get_pin_description( $post->ID );
        $alignment = $this::get_alignment( $alignment );


        if ( is_string( $size ) ) {
            $size = get_size( $size );
        }
        //* Else $size is array( $width, $height )

        $width = $size[0];
        $height = $size[1];

        $html = '<div class="swp-pinterest-image-wrap" ' . $alignment . '>';

            $html .= '<img ';
                $html .= ' src="' . $url . '"';
                $html .= ' width="' . $width . '"';
                $html .= ' height="' . $height . '"';
                $html .= ' class="swp-pinterest-image"';
                $html .= ' title="' . $title . '"';
                $html .= ' alt="' . $alt . '"';
            $html .= "/>";

        $html .= '</div>';

        return $html;
    }

    /**
     * Taken from https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
     *
     * Defines utility functions for handling WordPress's image sizing.
     *
     * @return function get_image_size( $size )
     */
    public function get_size( $size ) {
        /**
         * Get size information for all currently-registered image sizes.
         *
         * @global $_wp_additional_image_sizes
         * @uses   get_intermediate_image_sizes()
         * @return array $sizes Data for all currently-registered image sizes.
         */
        function get_image_sizes() {
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
         * Get size information for a specific image size.
         *
         * @uses   get_image_sizes()
         * @param  string $size The image size for which to retrieve data.
         * @return bool|array $size Size data about an image size or false if the size doesn't exist.
         */
        function get_image_size( $size ) {
        	$sizes = get_image_sizes();

        	if ( isset( $sizes[ $size ] ) ) {
        		return $sizes[ $size ];
        	}

        	return false;
        }

        /**
         * Get the width of a specific image size.
         *
         * @uses   get_image_size()
         * @param  string $size The image size for which to retrieve data.
         * @return bool|string $size Width of an image size or false if the size doesn't exist.
         */
        function get_image_width( $size ) {
        	if ( ! $size = get_image_size( $size ) ) {
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
        function get_image_height( $size ) {
        	if ( ! $size = get_image_size( $size ) ) {
        		return false;
        	}

        	if ( isset( $size['height'] ) ) {
        		return $size['height'];
        	}

        	return false;
        }

        return get_image_size( $size );
    }

}
