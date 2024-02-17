( function ( $, swpmb ) {
	'use strict';

	function setActiveClass() {
		var $this = $( this ),
			$input = $this.find( 'input' ),
			$label = $input.parent();

		if ( $input.prop( 'checked' ) ) {
			$label.addClass( 'selected' );
		} else {
			$label.removeClass( 'selected' );
		}
	}

	function clickHandler() {
		var $this = $( this ),
			$input = $this.find( 'input' ),
			$label = $input.parent(),
			type = $input.attr( 'type' ),
			$allLabels = $this.parent().find( 'label' );
		if ( ! $input.prop( 'checked' ) ) {
			$label.removeClass( 'selected' );
			return;
		}
		$label.addClass( 'selected' );

		if ( 'radio' === type ) {
			$allLabels.removeClass( 'selected' );
			$label.addClass( 'selected' );
		}
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-button-input-list label' ).each( setActiveClass );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'click', '.swpmb-button-input-list label', clickHandler )
		.on( 'clone', '.swpmb-button-input-list label', setActiveClass );
} )( jQuery, swpmb );
