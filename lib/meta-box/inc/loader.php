<?php
/**
 * Load plugin's files with check for installing it as a standalone plugin or
 * a module of a theme / plugin. If standalone plugin is already installed, it
 * will take higher priority.
 *
 * @package Meta Box
 */

/**
 * Plugin loader class.
 *
 * @package Meta Box
 */
class SWPMB_Loader {
	/**
	 * Define plugin constants.
	 */
	protected function constants() {
		// Script version, used to add version for scripts and styles.
		define( 'SWPMB_VER', '5.3.3' );

		list( $path, $url ) = self::get_path( dirname( dirname( __FILE__ ) ) );

		// Plugin URLs, for fast enqueuing scripts and styles.
		define( 'SWPMB_URL', $url );
		define( 'SWPMB_JS_URL', trailingslashit( SWPMB_URL . 'js' ) );
		define( 'SWPMB_CSS_URL', trailingslashit( SWPMB_URL . 'css' ) );

		// Plugin paths, for including files.
		define( 'SWPMB_DIR', $path );
		define( 'SWPMB_INC_DIR', trailingslashit( SWPMB_DIR . 'inc' ) );
	}

	/**
	 * Get plugin base path and URL.
	 * The method is static and can be used in extensions.
	 *
	 * @link http://www.deluxeblogtips.com/2013/07/get-url-of-php-file-in-wordpress.html
	 * @param string $path Base folder path.
	 * @return array Path and URL.
	 */
	public static function get_path( $path = '' ) {
		// Plugin base path.
		$path       = wp_normalize_path( untrailingslashit( $path ) );
		$themes_dir = wp_normalize_path( untrailingslashit( dirname( get_stylesheet_directory() ) ) );

		// Default URL.
		$url = plugins_url( '', $path . '/' . basename( $path ) . '.php' );

		// Included into themes.
		if (
			0 !== strpos( $path, wp_normalize_path( WP_PLUGIN_DIR ) )
			&& 0 !== strpos( $path, wp_normalize_path( WPMU_PLUGIN_DIR ) )
			&& 0 === strpos( $path, $themes_dir )
		) {
			$themes_url = untrailingslashit( dirname( get_stylesheet_directory_uri() ) );
			$url        = str_replace( $themes_dir, $themes_url, $path );
		}

		$path = trailingslashit( $path );
		$url  = trailingslashit( $url );

		return array( $path, $url );
	}

	/**
	 * Bootstrap the plugin.
	 */
	public function init() {
		$this->constants();

		// Register autoload for classes.
		require_once SWPMB_INC_DIR . 'autoloader.php';
		$autoloader = new SWPMB_Autoloader();
		$autoloader->add( SWPMB_INC_DIR, 'SWPMB_' );
		$autoloader->add( SWPMB_INC_DIR, 'SWPMB_' );
		$autoloader->add( SWPMB_INC_DIR . 'about', 'SWPMB_' );
		$autoloader->add( SWPMB_INC_DIR . 'fields', 'SWPMB_', '_Field' );
		$autoloader->add( SWPMB_INC_DIR . 'walkers', 'SWPMB_Walker_' );
		$autoloader->add( SWPMB_INC_DIR . 'interfaces', 'SWPMB_', '_Interface' );
		$autoloader->add( SWPMB_INC_DIR . 'storages', 'SWPMB_', '_Storage' );
		$autoloader->add( SWPMB_INC_DIR . 'helpers', 'SWPMB_Helpers_' );
		$autoloader->add( SWPMB_INC_DIR . 'update', 'SWPMB_Update_' );
		$autoloader->register();

		// Plugin core.
		$core = new SWPMB_Core();
		$core->init();

		// Validation module.
		new SWPMB_Validation();

		$sanitizer = new SWPMB_Sanitizer();
		$sanitizer->init();

		$media_modal = new SWPMB_Media_Modal();
		$media_modal->init();

		// WPML Compatibility.
		$wpml = new SWPMB_WPML();
		$wpml->init();

		// Update.
		$update_option  = new SWPMB_Update_Option();
		$update_checker = new SWPMB_Update_Checker( $update_option );
		$update_checker->init();
		$update_settings = new SWPMB_Update_Settings( $update_checker, $update_option );
		$update_settings->init();
		$update_notification = new SWPMB_Update_Notification( $update_checker, $update_option );
		$update_notification->init();

		if ( is_admin() ) {
			$about = new SWPMB_About( $update_checker );
			$about->init();
		}

		// Public functions.
		require_once SWPMB_INC_DIR . 'functions.php';
	}
}
