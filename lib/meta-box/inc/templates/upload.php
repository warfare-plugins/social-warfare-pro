<?php defined( 'ABSPATH' ) || die ?>

<script id="tmpl-swpmb-upload-area" type="text/html">
	<div class="swpmb-upload-inside">
		<h3>{{{ i18nRwmbMedia.uploadInstructions }}}</h3>
		<p>{{{ i18nRwmbMedia.or }}}</p>
		<button type="button" class="swpmb-browse-button browser button button-hero" id="{{{ _.uniqueId( 'swpmb-upload-browser-') }}}">{{{ i18nRwmbMedia.select }}}</button>
	</div>
</script>
