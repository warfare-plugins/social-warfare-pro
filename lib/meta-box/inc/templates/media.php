<?php defined( 'ABSPATH' ) || die ?>

<script id="tmpl-swpmb-media-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="swpmb-media-input">
	<div class="swpmb-file-icon">
		<# if ( data.sizes ) { #>
			<# if ( data.sizes.thumbnail ) { #>
				<img src="{{{ data.sizes.thumbnail.url }}}">
			<# } else { #>
				<img src="{{{ data.sizes.full.url }}}">
			<# } #>
		<# } else { #>
			<# if ( data.image && data.image.src && data.image.src !== data.icon ) { #>
				<img src="{{ data.image.src }}" />
			<# } else { #>
				<img src="{{ data.icon }}" />
			<# } #>
		<# } #>
	</div>
	<div class="swpmb-file-info">
		<a href="{{{ data.url }}}" class="swpmb-file-title" target="_blank">
			<# if( data.title ) { #>
				{{{ data.title }}}
			<# } else { #>
				{{{ i18nRwmbMedia.noTitle }}}
			<# } #>
		</a>
		<div class="swpmb-file-name">{{{ data.filename }}}</div>
		<div class="swpmb-file-actions">
			<a class="swpmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
				{{{ i18nRwmbMedia.edit }}}
			</a>
			<# if( data.file ) { #>
			<a href="#" class="swpmb-remove-media" data-file_id="{{{ data.file.id }}}" title="{{{ i18nRwmbMedia.remove }}}">
				{{{ i18nRwmbMedia.remove }}}
			</a>
			<# } else { #>
			<a href="#" class="swpmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
				{{{ i18nRwmbMedia.remove }}}
			</a>
			<# } #>
		</div>
	</div>
</script>

<script id="tmpl-swpmb-media-status" type="text/html">
	<# if ( data.maxFiles > 0 ) { #>
		{{{ data.length }}}/{{{ data.maxFiles }}}
		<# if ( 1 < data.maxFiles ) { #>{{{ i18nRwmbMedia.multiple }}}<# } else {#>{{{ i18nRwmbMedia.single }}}<# } #>
	<# } #>
</script>

<script id="tmpl-swpmb-media-button" type="text/html">
	<a class="button">{{{ data.text }}}</a>
</script>
