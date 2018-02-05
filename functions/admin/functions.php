<?php
/**
 * Created by Andrew D Howe.
 * Date: 12/22/15
 * Time: 1:01 AM
 */
function theme_scripts() {

    wp_enqueue_style('roboto-font','https://fonts.googleapis.com/css?family=Roboto:300','1.0');
    wp_enqueue_style('open-sans-font','https://fonts.googleapis.com/css?family=Open+Sans:400','1.0');
    wp_enqueue_script('bootstrap',get_template_directory_uri() . '/js/bootstrap.min.js',array('jquery'));
   // wp_enqueue_script('app-js', get_template_directory_uri() . '/js/app.min.js',array('jquery'));
}
add_action( 'wp_enqueue_scripts', 'theme_scripts' );
