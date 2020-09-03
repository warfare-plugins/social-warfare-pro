<?php


/**
 * The SWP_Pro_Analytics_Columns will add columns to the posts listing for our
 * analytics features such as our Social Scores.
 *
 * @since 4.2.0 | 21 AUG 2020 | Created
 *
 */
class SWP_Pro_Analytics_Columns {


	/**
	 * The constructor will queue up our methods to run on the appropriate
	 * hooks and filters.
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {

		// Create the social shares column
		add_filter( 'manage_post_posts_columns', array($this, 'create_optimization_score_column' ) );
		add_filter( 'manage_page_posts_columns', array($this, 'create_optimization_score_column' ) );

		// Populate the social shares column with data
        add_action( 'manage_posts_custom_column', array( $this, 'populate_optimization_score_column' ), 10, 2 );
    	add_action( 'manage_page_posts_custom_column', array( $this, 'populate_optimization_score_column' ), 10, 2 );

		// Make the social shares column sortable
    	add_filter( 'manage_edit-post_sortable_columns', array( $this, 'make_optimization_score_sortable' ) );
    	add_filter( 'manage_edit-page_sortable_columns', array( $this, 'make_optimization_score_sortable' ) );

		// Sort the output of the posts according to the sortable option created above
        add_action( 'pre_get_posts', array( $this, 'optimization_orderby' ) );
	}


	/**
	 * This will add the column to the available columns. This specifically adds
	 * the social optimization score column.
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  array $defaults The default columns registered with WordPress.
	 * @return array           The array modified with our new column.
	 *
	 */
	public function create_optimization_score_column( $defaults ) {
		$defaults['swp_optimization_score'] = 'Social Score';
		return $defaults;
	}


	/**
	 * Populate the new column with the social score from the meta field
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  string $column_name The name of the column to be modified.
	 * @param  int    $post_ID     The Post ID
	 * @return void                The number is echoed to the screen.
	 *
	 */
	public function populate_optimization_score_column( $column_name, $post_id ) {

		// Exit if we're not processing our own column.
		if ( $column_name !== 'swp_optimization_score' ) {
			return;
		}

		// Get the post score and its corresponding color code.
		$score      = SWP_Pro_Social_Optimizer::fetch_score( $post_id );
		$color_code = SWP_Pro_Social_Optimizer::get_color( $score );

		// Echo out the color-coded score bubble.
		echo '<div class="swp_score ' . $color_code . '">' . number_format( $score ) . '</div>';
	}


	/**
	 * Make the column sortable
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  array The array of registered columns.
	 * @return array The array modified columns.
	 *
	 */
    public function make_optimization_score_sortable( $columns ) {
    	$columns['swp_optimization_score'] = array('Social Score', 'desc');
    	return $columns;
    }


    /**
    * Sort the column by share count.
    *
    * @since  4.2.0 | 21 AUG 2020 | Created
    * @param  object $query The WordPress query object.
    * @return void
    *
    */
	public function optimization_orderby( $query ) {

		// Bail if we're not even in the admin area.
		if ( !is_admin() ) {
	 		return;
	 	}

		// Bail if we're not supposed to be ordering by social scores.
		if ( 'Social Score' !== $query->get( 'orderby' ) ) {
			return;
		}

		// Order by the _total_shares using a numeric interpretation of the value.
 		// $query->set( 'meta_key', '_swp_optimization_score' );
 		// $query->set( 'orderby', 'meta_value_num' );

		$query->set( 'meta_query', array(
		    // Note: Here the 'relation' defaults to AND.

		    // Clause 1, unnamed.
		    array(
		        'relation'             => 'OR',

		        // Sub-clause 1, named my_column_exists:
		        // Query posts that do have the metadata _my_column.
		        '_swp_optimization_score_exists'     => array(
		            'key'     => '_swp_optimization_score', // meta key
		            'compare' => 'EXISTS',
		            'type'    => 'NUMERIC',
		        ),

		        // Sub-clause 2, named my_column_not_exists:
		        // OR that do NOT have the metadata.
		        '_swp_optimization_score_not_exists' => array(
		            'key'     => '_swp_optimization_score', // meta key
		            'compare' => 'NOT EXISTS',
		            'type'    => 'NUMERIC',
		        ),
		    ),

		    // Clause 2, unnamed.
		    // Include the existing meta queries.
		    (array) $query->get( 'meta_query' ),
		) );

		$order = $query->get('order');
		$query->set( 'orderby', array( '_swp_optimization_score_not_exists' => $order) );
	}
}
