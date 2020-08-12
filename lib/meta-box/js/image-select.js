( function ( $, rwmb ) {
	'use strict';

	function setActiveClass() {
		var $this = $( this ),
			type = $this.attr( 'type' ),
			selected = $this.is( ':checked' ),
			$parent = $this.parent(),
			$others = $parent.siblings();
		if ( selected ) {
			$parent.addClass( 'swpmb-active' );
			if ( type === 'radio' ) {
				$others.removeClass( 'swpmb-active' );
			}
		} else {
			$parent.removeClass( 'swpmb-active' );
		}
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-image-select input' ).trigger( 'change' );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'change', '.swpmb-image-select input', setActiveClass );
} )( jQuery, rwmb );
