<?php

/**
 * A function for opting an image out of having a hover pin button
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     Not yet finished and released.
 * @todo      Complete these options for future release
 *
 */

defined( 'WPINC' ) || die;

/**
 * swp_media_options A function to modify the media options
 * @since 	2.0.0
 * @param  	array $form_fields
 * @param   object $post
 * @return 	array $form_fields The modified array of fields
 *
 */
add_filter( 'attachment_fields_to_edit' , 'swp_add_media_options' , 10 , 2 );
function swp_add_media_options( $form_fields, $post ) {
	$checked = get_post_meta( $post->ID, 'swp_pin_button_opt_out', false ) ? 'checked="checked"' : '';
	$form_fields['swp_pin_button_opt_out'] = array(
		'label' => 'Hover Pin Opt Out',
		'input' => 'html',
		'html'  => '<input type="checkbox" name="attachments[{$post->ID}][swp_pin_button_opt_out]" id="attachments[{$post->ID}][swp_pin_button_opt_out]" value="1" {$checked} /><br />',
	);
	return $form_fields;
}

/**
 * swp_media_options A function to save the media options
 * @since 	2.0.0
 * @param  	object $post
 * @param   array $attachment
 *
 */
add_filter( 'attachment_fields_to_save', 'swp_attachment_fields_to_save', 10 , 2 );
function swp_attachment_fields_to_save( $post, $attachment ) {
	if ( isset( $attachment['swp_pin_button_opt_out'] ) ) {
		update_post_meta( $post['ID'], 'swp_pin_button_opt_out', 1 );
	} else {
		update_post_meta( $post['ID'], 'swp_pin_button_opt_out', 0 );
	}
}
