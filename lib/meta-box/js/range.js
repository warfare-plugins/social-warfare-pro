( function ( $, swpmb ) {
	'use strict';

	/**
	 * Update text value.
	 */
	function update() {
		const $this = $( this ),
			$output = $this.siblings( '.swpmb-range-output' );

		$output.html( $this.val() );
		$this.on( 'input propertychange change', () => $output.html( $this.val() ) );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-range' ).each( update );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.swpmb-range', update );
} )( jQuery, swpmb );
