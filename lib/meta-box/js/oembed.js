( function ( $, _, swpmb ) {
	'use strict';

	/**
	 * Show preview of oembeded media.
	 */
	function showPreview( e ) {
		e.preventDefault();

		var $this = $( this ),
			$spinner = $this.siblings( '.spinner' ),
			data = {
				action: 'swpmb_get_embed',
				url: this.value,
				_ajax_nonce: swpmbOembed.nonce,
				not_available: $this.data( 'not-available' ),
			};

		$spinner.css( 'visibility', 'visible' );
		$.post( ajaxurl, data, function ( response ) {
			$spinner.css( 'visibility', 'hidden' );
			$this.siblings( '.swpmb-embed-media' ).html( response.data );
		}, 'json' );
	}

	/**
	 * Remove oembed preview when cloning.
	 */
	function removePreview() {
		$( this ).siblings( '.swpmb-embed-media' ).html( '' );
	}

	swpmb.$document
		.on( 'change', '.swpmb-oembed', _.debounce( showPreview, 250 ) )
	    .on( 'clone', '.swpmb-oembed', removePreview );
} )( jQuery, _, swpmb );
