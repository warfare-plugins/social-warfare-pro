( function ( $, rwmb ) {
	'use strict';

	var views = rwmb.views = rwmb.views || {},
		MediaField = views.MediaField,
		MediaItem = views.MediaItem,
		MediaList = views.MediaList,
		ImageField;

	ImageField = views.ImageField = MediaField.extend( {
		createList: function () {
			this.list = new MediaList( {
				controller: this.controller,
				itemView: MediaItem.extend( {
					className: 'swpmb-image-item attachment',
					template: wp.template( 'swpmb-image-item' ),
					initialize: function( models, options ) {
						MediaItem.prototype.initialize.call( this, models, options );
						this.$el.addClass( this.controller.get( 'imageSize' ) );
					}
				} )
			} );
		}
	} );

	/**
	 * Initialize image fields
	 */
	function initImageField() {
		var $this = $( this ),
			view = $this.data( 'view' );

		if ( view ) {
			return;
		}

		view = new ImageField( { input: this } );

		$this.siblings( '.swpmb-media-view' ).remove();
		$this.after( view.el );
		$this.data( 'view', view );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-image_advanced' ).each( initImageField );
	}

	rwmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.swpmb-image_advanced', initImageField );
} )( jQuery, rwmb );
