<?php
/**
 * Plugin Name: Meta Box
 * Plugin URI:  https://metabox.io
 * Description: Create custom meta boxes and custom fields in WordPress.
 * Version:     5.9.3
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: meta-box
 * Domain Path: /languages/
 *
 * @package Meta Box
 */

if ( defined( 'ABSPATH' ) && ! defined( 'SWPMB_VER' ) ) {
	require_once __DIR__ . '/inc/loader.php';
	$swpmb_loader = new SWPMB_Loader();
	$swpmb_loader->init();
}
