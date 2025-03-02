( function ( $, swpmb ) {
	'use strict';

	function toggleTree() {
		var $this = $( this ),
		$children = $this.closest( 'label' ).next( 'fieldset' );

		if ( $this.is( ':checked' ) ) {
			$children.removeClass( 'hidden' );
		} else {
			$children.addClass( 'hidden' ).find( 'input' ).prop( 'checked', false );
		}
	}

	function toggleAll( e ) {
		e.preventDefault();

		var $this = $( this ),
			checked = $this.data( 'checked' );

		if ( undefined === checked ) {
			checked = true;
		}

		$this.parent().siblings( '.swpmb-input-list' ).find( 'input' ).prop( 'checked', checked ).trigger( 'change' );

		checked = !checked;
		$this.data( 'checked', checked );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-input-list.swpmb-collapse input[type="checkbox"]' ).each( toggleTree );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'change', '.swpmb-input-list.swpmb-collapse input[type="checkbox"]', toggleTree )
		.on( 'clone', '.swpmb-input-list.swpmb-collapse input[type="checkbox"]', toggleTree )
		.on( 'click', '.swpmb-input-list-select-all-none', toggleAll );
} )( jQuery, swpmb );
