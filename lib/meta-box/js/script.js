// Global object for shared functions and data.
window.swpmb = window.swpmb || {};

( function( $, document, swpmb ) {
	'use strict';

	// Selectors for all plugin inputs.
	swpmb.inputSelectors = 'input[class*="swpmb"], textarea[class*="swpmb"], select[class*="swpmb"], button[class*="swpmb"]';

	// Detect Gutenberg.
	swpmb.isGutenberg = document.body.classList.contains( 'block-editor-page' );

	// Generate unique ID.
	swpmb.uniqid = () => Math.random().toString( 36 ).substr( 2 );

	// Trigger a custom ready event for all scripts to hook to.
	// Used for static DOM and dynamic DOM (loaded in MB Blocks extension for Gutenberg).
	swpmb.$document = $( document );

	$( function() {
		swpmb.$document.trigger( 'mb_ready' );
	} );
} )( jQuery, document, swpmb );