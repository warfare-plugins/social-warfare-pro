<?php
/**
 * Heading field class.
 */
class SWPMB_Toggle_Field extends SWPMB_Field
{
	/**
	 * Enqueue scripts and styles
	 *
	 * @return void
	 */
	static function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'swpmb-heading', SWPMB_CSS_URL . 'heading.css', array(), SWP_VERSION );
	}

	/**
	 * Show begin HTML markup for fields
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function begin_html( $meta, $field )
	{
		return sprintf( '<div><p>%s</p>', $field['desc'] );
	}

	/**
	 * Show end HTML markup for fields
	 *
	 * @param mixed $meta
	 * @param array $field
	 *
	 * @return string
	 */
	static function end_html( $meta, $field )
	{
        $id = $field['id'] ? " id='{$field['id']}'" : '';
        $checked_prop = isset($field['value']) ? 'checked' : '';
        $status = $checked_prop ? 'on' : 'off';
		$value = SWPMB_Toggle_Field::get_value($id);

		if ( !isset( $value ) ) {
			$value = false;
		}

		$label = '<div class="swpmb-label">';
		    $label .= '<label for="' . $field['id'] . '">' . $field['name'] . '</label>';
		$label .='</div>';

        $toggle = "<div class='sw-checkbox-toggle swp-post-editor' status='$status' field='#${field['id']}'>
                       <div class='sw-checkbox-on'>ON</div>
                       <div class='sw-checkbox-off'>OFF</div>
                   </div>";

        $toggle .= "<input $id $checked_prop type='checkbox' style='display: none;'  />";

        //* Close the div opened in begin_html().
        return $label . $toggle . '</div>';
	}

    /**
     * Fetches the stored value for a toggle, if it exists.
	 *
	 * If it DNE, then use a default provided by a map of defaults.
	 *
     * @param  string $key The post meta key to try fetching.
     * @return bool        The value for this key.
     * @since  3.4.0 | 24 OCT 2018 | Created
     *
     */
	static function get_value( $key ) {
		$post_id = (int) $_GET['post'];

		$defaults = array(
			'use_open_graph_twitter'	=> true,
			'swp_force_pin_image'		=> false
		);

		if ( metadata_exists( 'post', $post_id, $key ) ) {
			return get_post_meta($post_id, $key, true);
		}

		if ( isset( $defaults[$key] ) ) {
			return $defaults[$key];
		}

		return null;
	}
}
