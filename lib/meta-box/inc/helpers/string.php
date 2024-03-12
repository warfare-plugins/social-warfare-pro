<?php
/**
 * String helper functions.
 */
class SWPMB_Helpers_String {
	public static function title_case( string $text ) : string {
		$text = str_replace( [ '-', '_' ], ' ', $text );
		$text = ucwords( $text );
		$text = str_replace( ' ', '_', $text );

		return $text;
	}
}
