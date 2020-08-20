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


	public $scores = array(
		'total' => 0
	);


	public $fields = array();

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
			'name'        => 'Open Graph Image',
			'type'        => 'image',
			'width'       => 1200,
			'height'      => 628,
			'min_width'   => 200,
			'min_height'  => 200,
			'numerator'   => '1.9',
			'denominator' => '1',
		),

		// The Open Graph Title
		'swp_og_title' => array(
			'name'       => 'Open Graph Title',
			'type'       => 'input',
			'length'     => 55,
			'max_length' => 95,
		),

		// The Open Graph Description
		'swp_og_description' => array(
			'name'       => 'Open Graph Description',
			'type'       => 'input',
			'length'     => 60,
			'max_length' => 200,
		),

		// The Twitter Card Title
		'swp_twitter_card_title' => array(
			'name'       => 'Twitter Card Title',
			'type'       => 'input',
			'length'     => 55,
			'max_length' => 95,
		),

		// The Twitter Card Description
		'swp_twitter_card_description' => array(
			'name'       => 'Twitter Card Description',
			'type'       => 'input',
			'length'     => 55,
			'max_length' => 150,
		),

		// The Twitter Card Image
		'swp_twitter_card_image' => array(
			'name'        => 'Twitter Card Image',
			'type'        => 'image',
			'width'       => 1200,
			'height'      => 628,
			'min_width'   => 200,
			'min_height'  => 200,
			'numerator'   => '1.9',
			'denominator' => '1',
		),

		// The Custom Tweet Field
		'swp_custom_tweet' => array(
			'name'       => 'Custom Tweet',
			'type'       => 'input',
			'length'     => 100,
			'max_length' => 240,
		),

		// The Pinterest Image Field
		'swp_pinterest_image' => array(
			'name'        => 'Pinterest Image',
			'type'        => 'image',
			'width'       => 735,
			'height'      => 1102,
			'min_width'   => 238,
			'min_height'  => 356,
			'numerator'   => '2',
			'denominator' => '3',
		),

		// The Pinterest Description Field
		'swp_pinterest_description' => array(
			'name'       => 'Pinterest Description',
			'type'       => 'input',
			'length'     => 500,
			'max_length' => 500,
		)
	);

	public function __construct( $post_id ) {
		if( is_admin() ) {
			return;
		}

		$this->post_id = $post_id;
		$this->establish_maximum_scores();
		$this->update_score();
		var_dump($this);
	}


	/**
	 * The update_score() method will cycle through all of the fields, tabulate
	 * their scores, and add them up to create our total score for the post.
	 *
	 * @since  4.2.0 | 20 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function update_score() {

		// Loop through each of the fields.
		foreach( $this->fields as $field ) {

			// Get the score for this individual field.
			$this->scores[$field] = $this->get_individual_score( $field );

			// Update the total score.
			$this->scores['total'] =+ $this->scores[$field]['current_score'];
		}

		// Round the total score to the nearest whole number.
		$this->scores['total'] = round($this->scores['total']);
	}

	private function cache_score() {

	}


	/**
	 * The establish_maximum_scores() method will provide the baseline for how
	 * many points each field can be worth. These scores need to always add up
	 * to 100 points. As such, if the Twitter card fields are activated we will
	 * have 9 fields adding up to 100 versus 6 fields without them. So this
	 * allows us to check that field and assign those maximum values accordingly.
	 *
	 * @since  4.2.0 | 20 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function establish_maximum_scores() {

		// If the Twitter fields don't exist, we have 6 fields.
		if( true == $this->get_field('swp_twitter_use_open_graph') ) {
			$max_grades = array(
				'swp_og_image' => 20,
				'swp_og_title' => 15,
				'swp_og_description' => 15,
				'swp_custom_tweet' => 15,
				'swp_pinterest_image' => 20,
				'swp_pinterest_description' => 15
			);

		// If they do exist, we have all 9 fields to grade.
		} else {
			$max_grades = array(
				'swp_og_image' => 15,
				'swp_og_title' => 10,
				'swp_og_description' => 10,
				'swp_custom_tweet' => 10,
				'swp_twitter_card_image' => 10,
				'swp_twitter_card_title' => 10,
				'swp_twitter_card_description' => 10,
				'swp_pinterest_image' => 15,
				'swp_pinterest_description' => 10
			);
		}

		// Loop through and add each one to our existing $field_data property.
		foreach( $max_grades as $key => $value ) {
			$this->field_data[$key]['max_grade'] = $value;
			$this->fields[] = $key;
		}
	}

	private function get_individual_score( $field ) {

		switch( $this->field_data[$field]['type'] ) {
			case 'image':
				$scores = $this->get_image_score( $field );
				break;
			case 'input':
				// $scores = $this->get_input_score();
				break;
		}
		return $scores;
	}

	private function get_image_score( $field ) {

		// Establish our default $scores array.
		$scores = array(
			'percent' => 0,
			'current_score' => 0,
			'max_score' => $this->field_data[$field]['max_grade']
		);

		// Fetch the image data.
		$image = $this->get_image($field);

		// Bail with default values (zeroes) if there is no image.
		if( false === $image ) {
			return $scores;
		}

		// Fetch the top and bottom fields of the ratio number.
		$numerator = $this->field_data[$field]['numerator'];
		$denominator = $this->field_data[$field]['denominator'];

		// Calculate the image ratio and the desired image ratio.
		$aspect_ratio = $image['width'] / $image['height'];
		$desired_aspect_ratio = $numerator / $denominator;

		// Calculate how far from the ideal ratio our image is.
		$ratio_percent = $aspect_ratio / $desired_aspect_ratio;
		if( $aspect_ratio > $desired_aspect_ratio ) {
			$ratio_percent = $desired_aspect_ratio / $aspect_ratio;
		}

		$width_percent = $height_percent = 0.5;
		if( $image['width'] < $this->field_data[$field]['width'] ) {
			$width_percent = $image['width'] / $this->field_data[$field]['width'] * 0.5;
		}

		if( $image['height'] < $this->field_data[$field]['height'] ) {
			$height_percent = $image['height'] / $this->field_data[$field]['height'] * 0.5;
		}

		$size_percent = $width_percent + $height_percent;


		$min_size = 1;
		if( $image['width'] < 200 || $image['height'] < 200 ) {
			$min_size = 0;
		}


		$factors     = 2;
		$ratio_score = $this->calculate_subscores( $ratio_percent, $factors, $scores['max_score']);
		$size_score  = $this->calculate_subscores( $size_percent, $factors, $scores['max_score']);

		if( 1 === $min_size ) {
			$scores['current_score'] = round( $ratio_score + $size_score );
			$scores['percent'] = round( $scores['current_score'] / $scores['max_score'] );
		}

		return $scores;

	}

	private function calculate_subscores( $percent, $factors, $max_score ) {
		return $percent * $max_score / $factors;
	}


	/**
	 * The get_field() method is a shortcut method for get_post_meta(). Since
	 * we'll be using the same post id and we'll always only want one field
	 * being returned, that makes the first and third parameters reduntant. This
	 * method eliminates that. Just name the field you want, and it will return it.
	 *
	 * @since  4.2.0 | 20 AUG 2020 | Created
	 * @see    https://developer.wordpress.org/reference/functions/get_post_meta/
	 * @param  string $name The name of the meta field you want.
	 * @return mixed  The value of the meta field from get_post_meta()
	 *
	 */
	private function get_field($name) {
		return get_post_meta( $this->post_id, $name, true );
	}

	private function get_image( $field ) {
		$image_id = $this->get_field($field);

		if( empty( $image_id ) ) {
			return false;
		}

		$temp_image = wp_get_attachment_image_src( $image_id, 'full' );
		$image = array(
			'url' => $temp_image[0],
			'width' => $temp_image[1],
			'height' => $temp_image[2]
		);
		return $image;
	}

}
