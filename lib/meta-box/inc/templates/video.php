<script id="tmpl-swpmb-video-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="swpmb-media-input">
	<div class="swpmb-media-preview">
		<div class="swpmb-media-content">
			<div class="centered">
				<# if( _.indexOf( i18nRwmbVideo.extensions, data.url.substr( data.url.lastIndexOf('.') + 1 ) ) > -1 ) { #>
				<div class="swpmb-video-wrapper">
					<video controls="controls" class="swpmb-video-element" preload="metadata"
						<# if ( data.width ) { #>width="{{ data.width }}"<# } #>
						<# if ( data.height ) { #>height="{{ data.height }}"<# } #>
						<# if ( data.image && data.image.src !== data.icon ) { #>poster="{{ data.image.src }}"<# } #>>
						<source type="{{ data.mime }}" src="{{ data.url }}"/>
					</video>
				</div>
				<# } else { #>
					<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
						<img src="{{ data.image.src }}" />
					<# } else { #>
						<img src="{{ data.icon }}" />
					<# } #>
				<# } #>
			</div>
		</div>
	</div>
	<div class="swpmb-media-info">
		<a href="{{{ data.url }}}" class="swpmb-media-title" target="_blank">
			<# if( data.title ) { #>
				{{{ data.title }}}
			<# } else { #>
				{{{ i18nRwmbMedia.noTitle }}}
			<# } #>
		</a>
		<p class="swpmb-media-name">{{{ data.filename }}}</p>
		<p class="swpmb-media-actions">
			<a class="swpmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
				<span class="dashicons dashicons-edit"></span>{{{ i18nRwmbMedia.edit }}}
			</a>
			<a href="#" class="swpmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
				<span class="dashicons dashicons-no-alt"></span>{{{ i18nRwmbMedia.remove }}}
			</a>
		</p>
	</div>
</script>
