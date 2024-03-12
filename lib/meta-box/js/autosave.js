( function ( $, document ) {
	'use strict';

	$( document ).ajaxSend( function ( event, xhr, settings ) {
		if ( ! Array.isArray( settings.data ) || -1 === settings.data.indexOf( 'wp_autosave' ) ) {
			return;
		}
		var inputSelectors = 'input[class*="swpmb"], textarea[class*="swpmb"], select[class*="swpmb"], button[class*="swpmb"], input[name^="nonce_"]';
		$( '.swpmb-meta-box' ).each( function () {
			var $meta_box = $( this );
			if ( true === $meta_box.data( 'autosave' ) ) {
				settings.data += '&' + $meta_box.find( inputSelectors ).serialize();
			}
		} );
	} );
} )( jQuery, document );
