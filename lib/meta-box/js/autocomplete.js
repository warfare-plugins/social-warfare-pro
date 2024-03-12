( function ( $, swpmb, i18n ) {
	'use strict';

	/**
	 * Transform an input into an autocomplete.
	 */
	function transform( e ) {
		var $this = $( this ),
			$search = $this.siblings( '.swpmb-autocomplete-search' ),
			$result = $this.siblings( '.swpmb-autocomplete-results' ),
			name = $this.attr( 'name' );

		// If the function is called on cloning, then change the field name and clear all results
		if ( e.hasOwnProperty( 'type' ) && 'clone' == e.type ) {
			$result.html( '' );
		}

		$search.removeClass( 'ui-autocomplete-input' ).autocomplete( {
			minLength: 0,
			source: $this.data( 'options' ),
			select: function ( event, ui ) {
				$result.append(
					'<div class="swpmb-autocomplete-result">' +
					'<div class="label">' + ( typeof ui.item.excerpt !== 'undefined' ? ui.item.excerpt : ui.item.label ) + '</div>' +
					'<div class="actions">' + i18n.delete + '</div>' +
					'<input type="hidden" class="swpmb-autocomplete-value" name="' + name + '" value="' + ui.item.value + '">' +
					'</div>'
				);

				// Reinitialize value.
				$search.val( '' ).trigger( 'change' );

				return false;
			}
		} );
	}

	function deleteSelection( e ) {
		e.preventDefault();
		var $item = $( this ).parent(),
			$search = $item.parent().siblings( '.swpmb-autocomplete-search' );

		$item.remove();
		$search.trigger( 'change' );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-autocomplete-wrapper input[type="hidden"]' ).each( transform );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.swpmb-autocomplete', transform )
		.on( 'click', '.swpmb-autocomplete-result .actions', deleteSelection );
} )( jQuery, swpmb, SWPMB_Autocomplete );
