<?php defined( 'ABSPATH' ) || die ?>

<script id="tmpl-swpmb-image-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="swpmb-media-input">
	<div class="swpmb-file-icon">
		<# if ( 'image' === data.type && data.sizes ) { #>
			<# if ( data.sizes[data.controller.imageSize] ) { #>
				<img src="{{{ data.sizes[data.controller.imageSize].url }}}">
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
	<div class="swpmb-image-overlay"></div>
	<div class="swpmb-image-actions">
		<a class="swpmb-image-edit swpmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
			<span class="dashicons dashicons-edit"></span>
		</a>
		<a href="#" class="swpmb-image-delete swpmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
			<span class="dashicons dashicons-no-alt"></span>
		</a>
	</div>
</script>
