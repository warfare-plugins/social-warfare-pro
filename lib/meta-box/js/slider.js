( function ( $, swpmb ) {
	'use strict';

	function transform() {
		var $input  = $( this ),
			$slider = $input.siblings( '.swpmb-slider-ui' ),
			$label  = $slider.siblings( '.swpmb-slider-label' ).find( 'span' ),
			value   = $input.val(),
			options = $slider.data( 'options' );

		$slider.html( '' );
		$label.text( value );

		if ( true === options.range ) {
			value = value.split( '|' );
			options.values = value;
		} else {
			options.value = value;
		}

		options.slide = function ( event, ui ) {
			var value = ui.value;
			if ( options.range === true ) {
				value = ui.values[ 0 ] + '|' + ui.values[ 1 ];
			}

			$input.val( value ).trigger( 'change' );
			$label.html( value );
		};

		$slider.slider( options );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-slider' ).each( transform );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.swpmb-slider', transform );
} )( jQuery, swpmb );
