<?php

class SWP_Pro_Pinterest {
    public function __construct() {
        add_filter( 'image_send_to_editor', array( $this, 'add_pin_description') );
    }


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


    public function add_pin_description( $html, $id, $caption, $title, $align, $url, $size, $alt ) {
        global $post;
        
        $description = $this::get_pin_description( $post->ID );
    }


}
