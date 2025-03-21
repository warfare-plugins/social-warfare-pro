<?php
use MetaBox\Support\Arr;
/**
 * A very simple request class that handles form inputs.
 * Based on the code of Symphony framework, (c) Fabien Potencier <fabien@symfony.com>
 *
 * @link https://github.com/laravel/framework/blob/6.x/src/Illuminate/Http/Request.php
 * @link https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/HttpFoundation/ParameterBag.php
 */
class SWPMB_Request {
	private $get_data  = [];
	private $post_data = [];

	public function __construct() {
		// @codingStandardsIgnoreLine
		$this->get_data  = $_GET;
		// @codingStandardsIgnoreLine
		$this->post_data = $_POST;

		// Cleanup data
		$this->post_data = $this->cleanup( $this->post_data );
	}

	public function set_get_data( array $data ) {
		$this->get_data = array_merge( $this->get_data, $data );
	}

	public function set_post_data( array $data ) {
		$this->post_data = array_merge( $this->post_data, $data );
	}

	public function get( string $name, $default = null ) {
		return $this->get_data[ $name ] ?? $default;
	}

	public function post( string $name, $default = null ) {
		return $this->post_data[ $name ] ?? $default;
	}

	public function cleanup( array $data ) {
		$cleanups = $data['swpmb_cleanup'] ?? []; // Array of field ids
		if ( empty( $cleanups ) || ! is_array( $cleanups ) ) {
			return $data;
		}
		
		// Decode the JSON string for each cleanup item
		foreach ( $cleanups as $cleanup ) {
			$cleanup = json_decode( stripslashes( $cleanup ) );

			if ( ! is_array( $cleanup ) ) {
				continue;
			}

			foreach ( $cleanup as $field_id ) {
				// Remove the field from the data
				Arr::remove_first( $data, $field_id );
			}
		}

		return $data;
	}

	/**
	 * Filter a GET parameter.
	 *
	 * @param string $name    Parameter name.
	 * @param int    $filter  FILTER_* constant.
	 * @param mixed  $options Filter options.
	 *
	 * @return mixed
	 */
	public function filter_get( string $name, $filter = FILTER_DEFAULT, $options = [] ) {
		$value = $this->get( $name );
		return filter_var( $value, $filter, $options );
	}

	/**
	 * Filter a POST parameter.
	 *
	 * @param string $name    Parameter name.
	 * @param int    $filter  FILTER_* constant.
	 * @param mixed  $options Filter options.
	 *
	 * @return mixed
	 */
	public function filter_post( string $name, $filter = FILTER_DEFAULT, $options = [] ) {
		$value = $this->post( $name );
		return filter_var( $value, $filter, $options );
	}
}
