<?php

/**
 * The Social Optimizer class will do the same thing as the Gutenberg sidebar,
 * except this one will run the scoring on the server side allowing us to go
 * back through old posts and generate optimization scores for them. This will
 * also make it easier for us to store the grades in the database. We will then
 * use these numbers to make recommendations for the user to go back and optimize
 * their highest performing posts with the least amount of optimizations.
 *
 * @since 4.2.0 | 20 AUG 2020 | Created
 *
 */
class SWP_Pro_Social_Optimizer {


	/**
	 * The local $post_id property will obviously store the post ID of the post
	 * that is currently being graded. The entire class will shut down and bail
	 * out if a proper post ID is not provided.
	 *
	 * @var integer
	 *
	 */
	public $post_id = 0;


	/**
	 * The local $field_data property will contain all of the data for each of
	 * the fields that are being graded. This contains things like the maximum
	 * length for input fields, optimim length, minimum length, image ratios, etc.
	 *
	 * @var array
	 *
	 */
	public $field_data = array(

		// The Open Graph Image
		'swp_og_image' => array(
			'name' => 'Open Graph Image',
			'type' => 'image',
			'width' => 1200,
			'height' => 628,
			'min_width' => 200,
			'min_height' => 200,
			'numerator' => '1.9',
			'denominator' => '1',
		),

		// The Open Graph Title
		'swp_og_title' => array(
			'name' => 'Open Graph Title',
			'type' => 'input',
			'length' => 55,
			'max_length' => 95,
		),

		// The Open Graph Description
		'swp_og_description' => array(
			'name' => 'Open Graph Description',
			'type' => 'input',
			'length' => 60,
			'max_length' => 200,
		),

		// The Twitter Card Title
		'swp_twitter_card_title' => array(
			'name' => 'Twitter Card Title',
			'type' => 'input',
			'length' => 55,
			'max_length' => 95,
		),

		// The Twitter Card Description
		'swp_twitter_card_description' => array(
			'name' => 'Twitter Card Description',
			'type' => 'input',
			'length' => 55,
			'max_length' => 150,
		),

		// The Twitter Card Image
		'swp_twitter_card_image' => array(
			'name' => 'Twitter Card Image',
			'type' => 'image',
			'width' => 1200,
			'height' => 628,
			'min_width' => 200,
			'min_height' => 200,
			'numerator' => '1.9',
			'denominator' => '1',
		),

		// The Custom Tweet Field
		'swp_custom_tweet' => array(
			'name' => 'Custom Tweet',
			'type' => 'input',
			'length' => 100,
			'max_length' => 240,
		),

		// The Pinterest Image Field
		'swp_pinterest_image' => array(
			'name' => 'Pinterest Image',
			'type' => 'image',
			'width' => 735,
			'height' => 1102,
			'min_width' => 238,
			'min_height' => 356,
			'numerator' => '2',
			'denominator' => '3',
		),

		// The Pinterest Description Field
		'swp_pinterest_description' => array(
			'name' => 'Pinterest Description',
			'type' => 'input',
			'length' => 500,
			'max_length' => 500,
		)
	);

	public function __construct( $post_id ) {
		$this->post_id = $post_id;

		$this->establish_maximum_scores();
		var_dump($this);
	}

	public function calculate_score() {

	}

	public function cache_score() {

	}

	public function establish_maximum_scores() {

	}
}
