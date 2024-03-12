<?php defined( 'ABSPATH' ) || die ?>

<script id="tmpl-swpmb-media-item" type="text/html">
	<input type="hidden" name="{{{ data.controller.fieldName }}}" value="{{{ data.id }}}" class="swpmb-media-input">
	<div class="swpmb-media-preview">
		<div class="swpmb-media-content">
			<div class="centered">
				<# if ( 'image' === data.type && data.sizes ) { #>
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
		</div>
	</div>
	<div class="swpmb-media-info">
		<h4>
			<a href="{{{ data.url }}}" target="_blank" title="{{{ i18nRwmbMedia.view }}}">
				<# if( data.title ) { #> {{{ data.title }}}
					<# } else { #> {{{ i18nRwmbMedia.noTitle }}}
				<# } #>
			</a>
		</h4>
		<p>{{{ data.mime }}}</p>
		<p>
			<a class="swpmb-edit-media" title="{{{ i18nRwmbMedia.edit }}}" href="{{{ data.editLink }}}" target="_blank">
				<span class="dashicons dashicons-edit"></span>{{{ i18nRwmbMedia.edit }}}
			</a>
			<a href="#" class="swpmb-remove-media" title="{{{ i18nRwmbMedia.remove }}}">
				<span class="dashicons dashicons-no-alt"></span>{{{ i18nRwmbMedia.remove }}}
			</a>
		</p>
	</div>
</script>
