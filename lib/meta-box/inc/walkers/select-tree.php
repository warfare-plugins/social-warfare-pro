<?php
defined( 'ABSPATH' ) || die;

/**
 * Select tree walker for cascading select fields.
 */
class SWPMB_Walker_Select_Tree {
	/**
	 * Field settings.
	 *
	 * @var array
	 */
	public $field;

	/**
	 * Field meta value.
	 *
	 * @var array
	 */
	public $meta;

	/**
	 * Constructor.
	 *
	 * @param array $field Field parameters.
	 * @param mixed $meta  Meta value.
	 */
	public function __construct( $field, $meta ) {
		$this->field = $field;
		$this->meta  = (array) $meta;
	}

	/**
	 * Display array of elements hierarchically.
	 *
	 * @param array $options An array of options.
	 *
	 * @return string
	 */
	public function walk( $options ) {
		$children = [];

		foreach ( $options as $option ) {
			$parent                = $option->parent ?? 0;
			$children[ $parent ][] = $option;
		}

		$top_level = isset( $children[0] ) ? 0 : $options[0]->parent;
		return $this->display_level( $children, $top_level, true );
	}

	/**
	 * Display a hierarchy level.
	 *
	 * @param array $options   An array of options.
	 * @param int   $parent_id Parent item ID.
	 * @param bool  $active    Whether to show or hide.
	 *
	 * @return string
	 */
	public function display_level( $options, $parent_id = 0, $active = false ) {
		static $output_required = false;

		$field      = $this->field;
		$walker     = new SWPMB_Walker_Select( $field, $this->meta );
		$attributes = SWPMB_Field::call( 'get_attributes', $field, $this->meta );

		if ( $output_required ) {
			unset( $attributes[ 'required' ] );
		}

		if ( ! empty( $attributes['required'] ) ) {
			$output_required = true;
		}

		$children = $options[ $parent_id ];
		$output   = sprintf(
			'<div class="swpmb-select-tree %s" data-parent-id="%s"><select %s>',
			$active ? '' : 'hidden',
			esc_attr( $parent_id ),
			SWPMB_Field::render_attributes( $attributes )
		);
		$output  .= $field['placeholder'] ? "<option value=''>{$field['placeholder']}</option>" : '<option></option>';
		$output  .= $walker->walk( $children, - 1 );
		$output  .= '</select>';

		foreach ( $children as $child ) {
			if ( isset( $options[ $child->value ] ) ) {
				$output .= $this->display_level( $options, $child->value, in_array( $child->value, $this->meta ) && $active );
			}
		}

		$output .= '</div>';
		return $output;
	}
}
