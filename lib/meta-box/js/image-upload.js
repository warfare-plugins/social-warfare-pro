( function ( $, swpmb ) {
	'use strict';

	var views = swpmb.views = swpmb.views || {},
		ImageField = views.ImageField,
		ImageUploadField,
		UploadButton = views.UploadButton;

	ImageUploadField = views.ImageUploadField = ImageField.extend( {
		createAddButton: function () {
			this.addButton = new UploadButton( {controller: this.controller} );
		}
	} );

	function initImageUpload() {
		var $this = $( this ),
			view = $this.data( 'view' );

		if ( view ) {
			return;
		}

		view = new ImageUploadField( { input: this } );

		$this.siblings( '.swpmb-media-view' ).remove();
		$this.after( view.el );

		// Init uploader after view is inserted to make wp.Uploader works.
		view.addButton.initUploader( $this );

		$this.data( 'view', view );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-image_upload, .swpmb-plupload_image' ).each( initImageUpload );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.swpmb-image_upload, .swpmb-plupload_image', initImageUpload )
} )( jQuery, swpmb );
