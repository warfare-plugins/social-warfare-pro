( function ( $, rwmb ) {
	'use strict';

	function toggleAddInput( e ) {
		e.preventDefault();
		this.nextElementSibling.classList.toggle( 'swpmb-hidden' );
	}

	rwmb.$document.on( 'click', '.swpmb-taxonomy-add-button', toggleAddInput );
} )( jQuery, rwmb );
