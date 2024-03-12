( function ( $, swpmb ) {
	'use strict';

	var views = swpmb.views = swpmb.views || {},
		MediaField = views.MediaField,
		MediaItem = views.MediaItem,
		MediaList = views.MediaList,
		VideoField;

	VideoField = views.VideoField = MediaField.extend( {
		createList: function ()
		{
			this.list = new MediaList( {
				controller: this.controller,
				itemView: MediaItem.extend( {
					className: 'swpmb-video-item',
					template : wp.template( 'swpmb-video-item' ),
					render: function()
					{
						var settings =  ! _.isUndefined( window._wpmejsSettings ) ? _.clone( _wpmejsSettings ) : {};
						MediaItem.prototype.render.apply( this, arguments );
						this.player = new MediaElementPlayer( this.$( 'video' ).get(0), settings );
					}
				} )
			} );
		}
	} );

	function initVideoField() {
		var $this = $( this ),
			view = new VideoField( { input: this } );
		$this.siblings( '.swpmb-media-view' ).remove();
		$this.after( view.el );
	}

	function init( e ) {
		$( e.target ).find( '.swpmb-video' ).each( initVideoField );
	}

	swpmb.$document
		.on( 'mb_ready', init )
		.on( 'clone', '.swpmb-video', initVideoField );
} )( jQuery, swpmb );
