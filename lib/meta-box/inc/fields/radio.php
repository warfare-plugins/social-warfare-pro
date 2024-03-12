<?php
defined( 'ABSPATH' ) || die;

/**
 * The radio field.
 */
class SWPMB_Radio_Field extends SWPMB_Input_List_Field {
	public static function normalize( $field ) {
		$field['multiple'] = false;
		$field             = parent::normalize( $field );

		return $field;
	}
}
