( function ( $, swpmb ) {
	'use strict';

	function setInitialRequiredProp() {
		var $this = $( this ),
			required = $this.prop( 'required' );

		if ( required ) {
			$this.data( 'initial-required', required );
		}
	}

	function unsetRequiredProp() {
		$( this ).prop( 'required', false );
	}

	function setRequiredProp() {
		var $this = $( this );

		if ( $this.data( 'initial-required' ) ) {
			$this.prop( 'required', true );
		}
	}

	function toggleTree() {
		var $this = $( this ),
			val = $this.val(),
			$tree = $this.siblings( '.swpmb-select-tree' ),
			$selected = $tree.filter( "[data-parent-id='" + val + "']" ),
			$notSelected = $tree.not( $selected );

		$selected.removeClass( 'hidden' ).find( 'select' ).each( setRequiredProp );
		$notSelected.addClass( 'hidden' ).find( 'select' ).each( unsetRequiredProp ).prop( 'selectedIndex', 0 );
	}

	function instantiateSelect2() {
		var $this = $( this ),
			options = $this.data( 'options' );

		$this
			.removeClass( 'select2-hidden-accessible' ).removeAttr( 'data-select2-id' )
			.children().removeAttr( 'data-select2-id' ).end()
			.siblings( '.select2-container' ).remove().end()
			.select2( options );

		toggleTree.call( this );
	}

	function init( e ) {
		var $select = $( e.target ).find( '.swpmb-select-tree > select' );

		$select.each ( setInitialRequiredProp );
		$select.each( function() {
			const $this = $( this ),
				options = $this.data( 'options' );

			$this.select2( options );
		} );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'change', '.swpmb-select-tree > select', toggleTree )
		.on( 'clone', '.swpmb-select-tree > select', instantiateSelect2 );
} )( jQuery, swpmb );
