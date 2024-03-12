( function ( $, swpmb ) {
	'use strict';

	const $body = $( 'body' );

	const defaultOptions = {
		wrapper: `<div class="swpmb-modal">
			<div class="swpmb-modal-title">
				<h2></h2>
				<button type="button" class="swpmb-modal-close">&times;</button>
			</div>
			<div class="swpmb-modal-content"></div>
		</div>`,
		markupIframe: '<iframe id="swpmb-modal-iframe" width="100%" height="700" src="{URL}" border="0"></iframe>',
		markupOverlay: '<div class="swpmb-modal-overlay"></div>',
		removeElement: '',
		removeElementDefault: '#adminmenumain, #wpadminbar, #wpfooter, .row-actions, .form-wrap.edit-term-notes, #screen-meta-links, .wp-heading-inline, .wp-header-end',
		callback: null,
		closeModalCallback: null,
		isBlockEditor: false,
		$objectId: null,
		$objectDisplay: null
	};

	$.fn.swpmbModal = function ( options = {} ) {
		options = {
			...defaultOptions,
			...options
		};

		if ( $( '.swpmb-modal' ).length === 0 ) {
			return;
		}

		const $this = $( this ),
			$modal = $( '.swpmb-modal' );

		let $input = $this.closest( '.swpmb-input' );
		if ( $input.find( '.swpmb-clone' ).length > 0 && $this.closest( '.swpmb-clone' ).length > 0 ) {
			$input = $this.closest( '.swpmb-clone' );
		}

		$this.click( function ( e ) {
			e.preventDefault();

			$modal.find( '.swpmb-modal-title h2' ).html( $this.html() );
			$modal.find( '.swpmb-modal-content' ).html( options.markupIframe.replace( '{URL}', $this.data( 'url' ) ) );
			$( '#swpmb-modal-iframe' ).on( 'load', function () {
				const $contents = $( this ).contents();
				options.isBlockEditor = $contents.find( 'body' ).hasClass( 'block-editor-page' );

				if ( options.removeElement !== '' ) {
					$contents.find( options.removeElement ).remove();
				}

				$modal.find( '.swpmb-modal-title' ).css( 'background-color', '' );
				if ( options.isBlockEditor ) {
					$modal.find( '.swpmb-modal-title' ).css( 'background-color', '#fff' );
				}

				$contents
					.find( options.removeElementDefault ).remove().end()
					.find( '.swpmb-modal-add-button' ).parent().remove();
				$contents.find( 'html' ).css( 'padding-top', 0 ).end()
					.find( '#wpcontent' ).css( 'margin-left', 0 ).end()
					.find( 'a' ).on( 'click', e => e.preventDefault() );

				if ( options.callback !== null && typeof options.callback === 'function' ) {
					options.callback( $modal, $contents );
				}

				$body.addClass( 'swpmb-modal-show' );
				$( '.swpmb-modal-overlay' ).fadeIn( 'medium' );
				$modal.fadeIn( 'medium' );

				return false;
			} );

			$( '.swpmb-modal-close' ).on( 'click', function ( event ) {
				if ( options.closeModalCallback !== null && typeof options.closeModalCallback === 'function' ) {
					options.closeModalCallback( $( '#swpmb-modal-iframe' ).contents(), $input );
				}

				$modal.fadeOut( 'medium' );
				$( '.swpmb-modal-overlay' ).fadeOut( 'medium' );
				$body.removeClass( 'swpmb-modal-show' );

				// If not add new
				if ( !options.$objectId || !options.$objectDisplay ) {
					$( this ).off( event );
					return;
				}

				// Select, select advanced, select tree.
				const $select = $input.find( 'select' );
				if ( $select.length > 0 ) {
					$select.prepend( $( '<option>', {
						value: options.$objectId,
						text: options.$objectDisplay,
						selected: true
					} ) );

					$( this ).off( event );
					return;
				}

				// Radio, checkbox list, checkbox tree
				const $inputList = $input.find( '.swpmb-input-list:first' ),
					$labelClone = $inputList.find( '> label:first' ).clone(),
					$inputClone = $labelClone.find( 'input' ).clone();

				$labelClone.html(
					$inputClone.val( options.$objectId )
						.attr( 'checked', true )
						.prop( 'outerHTML' ) + options.$objectDisplay
				);
				$inputList.prepend( $labelClone );

				// Clear event after close modal.
				options.$objectId = null;
				options.$objectDisplay = null;
				$( this ).off( event );
			} );
		} );
	};

	if ( $( '.swpmb-modal' ).length === 0 ) {
		$body.append( defaultOptions.wrapper )
			.append( defaultOptions.markupOverlay );
	}

} )( jQuery, swpmb );