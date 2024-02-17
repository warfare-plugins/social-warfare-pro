( function ( $, swpmb ) {
	'use strict';

	var frame;

	function openSelectPopup( e ) {
		e.preventDefault();
		var $el = $( this );

		// Create a frame only if needed
		if ( ! frame ) {
			frame = wp.media( {
				className: 'media-frame swpmb-file-frame',
				multiple: false,
				title: swpmbFileInput.frameTitle
			} );
		}

		// Open media uploader
		frame.open();

		// Remove all attached 'select' event
		frame.off( 'select' );

		// Handle selection
		frame.on( 'select', function () {
			var url = frame.state().get( 'selection' ).first().toJSON().url;
			$el.siblings( 'input' ).val( url ).trigger( 'change' ).siblings( 'a' ).removeClass( 'hidden' );
		} );
	}

	function changeValueInput( e ) {
		e.preventDefault();
		var $el = $( this ),
			url = $el.val(),
			fileType = url.split( '.' ).pop().toLowerCase(),
			imageTypes = [ 'gif', 'jpeg', 'png', 'jpg' ],
			validImageTypes = imageTypes.includes( fileType );

		if ( validImageTypes ) {
			$el.closest( '.swpmb-file-input-inner' ).siblings( '.swpmb-file-input-image' ).removeClass( 'swpmb-file-input-hidden' ).find( 'img' ).attr( 'src', url );
		} else {
			$el.closest( '.swpmb-file-input-inner' ).siblings( '.swpmb-file-input-image' ).addClass( 'swpmb-file-input-hidden' );
		}
	}

	function clearSelection( e ) {
		e.preventDefault();
		$( this ).addClass( 'hidden' ).siblings( 'input' ).val( '' ).trigger( 'change' );
		$( this ).closest( '.swpmb-file-input-inner' ).siblings( '.swpmb-file-input-image' ).addClass( 'swpmb-file-input-hidden' );
	}

	function hideRemoveButtonWhenCloning() {
		$( this ).siblings( '.swpmb-file-input-remove' ).addClass( 'hidden' );
	}

	swpmb.$document
		.on( 'click', '.swpmb-file-input-select', openSelectPopup )
		.on( 'input change', '.swpmb-file_input', changeValueInput )
		.on( 'click', '.swpmb-file-input-remove', clearSelection )
		.on( 'clone', '.swpmb-file_input', hideRemoveButtonWhenCloning );
} )( jQuery, swpmb );
