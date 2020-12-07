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
		$this->post_id = $post_id;
		$this->establish_maximum_scores();
		$this->update_score();
		$this->cache_score();
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
			$this->scores[$field] = $this->get_field_score( $field );

			// Update the total score.
			$this->scores['total'] += $this->scores[$field]['current_score'];
		}

		// Round the total score to the nearest whole number.
		$this->scores['total'] = round($this->scores['total']);
	}


	/**
 	 * The get_score() method allows us to request the current score of the post
 	 * after the SWP_Pro_Social_Optimizer object has been fully initialized. In
 	 * other words, it will return the score of the post being graded by the
 	 * current instantiation of this class.
	 *
	 * Disambiguation:
	 * fetch_score( $post_id ) is a public static method that allows you to
	 *     fetch the score of any post by passing in the post id.
	 * get_score() is a public, non-static method that allows you to get the
	 *     score from the currently instantiated SWP_Pro_Social_Optimizer object.
	 *
	 * @since  4.2.0 | 02 SEP 2020 | Created
	 * @param  void
	 * @return integer  The score of the post.
	 *
	 */
	public function get_score() {

		// If we have a total score, return it.
		if( false === empty( $this->scores['total'] ) ) {
			return $this->scores['total'];
		}

		// If not, return 0.
		return 0;
	}


	/**
	 * The cache_score() method will take the total for this post and store it
	 * in a custom meta field for the post. This will allow us to run custom
	 * queries on the database to find the best and worst optimized posts on a
	 * site.
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function cache_score() {

		// If we don't have a valid total score, just bail out.
		if( false === isset( $this->scores['total'] ) || false === is_numeric( $this->scores['total'] ) ) {
			return;
		}

		// Remove any previous entries and then update the new score into the db.
		delete_post_meta( $this->post_id, '_swp_optimization_score' );
		update_post_meta( $this->post_id, '_swp_optimization_score', $this->scores['total'] );


		/**
		 * This portion of the code will calculate and record the "improvement
		 * potential" for the post. We will take the number of shares and
		 * multiply it by the amount they are short of 100 points. Hence a post
		 * with 50 shares, and an optimization score of 0, will get a priority
		 * score of 5,000 (50 * 100). This post would be a higher priority to
		 * optimize than a post with 10 shares and an optimization score of 10
		 * (900 = 10 * 90 ).
		 *
		 * Priority = shares * (100 - optimization_score)
		 *
		 * By adding it to a meta field, we'll be able to query posts and order
		 * them by this field.
		 *
		 */
		$total_shares = get_post_meta( $this->post_id, '_total_shares', true );
		if( false === $total_shares ) {
			$total_shares = 0;
		}

		// Calculate the post's optimization potential.
		$optimization_potential = (int) $total_shares * ( 100 - (int) $this->scores['total'] );

		// Remove any previous entries and then update the new score into the db.
		delete_post_meta( $this->post_id, '_swp_optimization_potential' );
		update_post_meta( $this->post_id, '_swp_optimization_potential', $optimization_potential );
	}


	/**
	 * The get_individual_score() method will calculate and return the score for
	 * any one of the given fields.
	 *
	 * @since  4.2.0 | 20 AUG 2020 | Created
	 * @param  string $field The name of the field being graded.
	 * @return array  An array of score data.
	 *
	 */
	private function get_field_score( $field ) {

		switch( $this->field_data[$field]['type'] ) {

			// If the field is an image field.
			case 'image':
				$scores = $this->get_image_score( $field );
				break;

			// If the field is an input field.
			case 'input':
				$scores = $this->get_input_score( $field );
				break;
		}

		switch( $field ) {
			case 'swp_custom_tweet':
				$scores = $this->get_custom_tweet_score( $field );
				break;
			case 'swp_og_title':
				$scores = $this->get_og_title_score( $field );
				break;
			case 'swp_pinterest_description':
				$scores = $this->get_pinterest_description_score( $field );
				break;
		}

		return $scores;
	}


	/**
	 * The get_image_score() method will calculate the scores for image fields.
	 *
	 * @since  4.2.0 | 20 AUG 2020 | Created
	 * @param  string $field The name of the field being graded.
	 * @return array  An array of score data.
	 *
	 */
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


		/**
		 * Minimum Size
		 *
		 * This will check if the image meets the minimum size requirements.
		 * Further down, this will be used to zeroize the entire score if this
		 * check fails.
		 *
		 */
		$min_size = 1;
		if( $image['width'] < 200 || $image['height'] < 200 ) {
			return $scores;
		}


		/**
		 *
		 * Aspect Ratio
		 *
		 * This will examine the image's aspect ratio and compare it to the
		 * ideal aspect ratior for this field.
		 *
		 */

		// Fetch the top and bottom fields of the ratio number.
		$numerator   = $this->field_data[$field]['numerator'];
		$denominator = $this->field_data[$field]['denominator'];

		// Calculate the image ratio and the desired image ratio.
		$aspect_ratio         = $image['width'] / $image['height'];
		$desired_aspect_ratio = $numerator / $denominator;

		// Calculate how far from the ideal ratio our image is.
		$ratio_percent = $aspect_ratio / $desired_aspect_ratio;
		if( $aspect_ratio > $desired_aspect_ratio ) {
			$ratio_percent = $desired_aspect_ratio / $aspect_ratio;
		}


		/**
		 *
		 * Height & Width
		 *
		 * This will examine the height and width of the image and compare it to
		 * the ideals for this image.
		 *
		 */
		$width_percent = $height_percent = 0.5;
		if( $image['width'] < $this->field_data[$field]['width'] ) {
			$width_percent = $image['width'] / $this->field_data[$field]['width'] * 0.5;
		}

		if( $image['height'] < $this->field_data[$field]['height'] ) {
			$height_percent = $image['height'] / $this->field_data[$field]['height'] * 0.5;
		}

		$size_percent = $width_percent + $height_percent;


		/**
		 * Minimum Size
		 *
		 * This will check if the image meets the minimum size requirements.
		 * Further down, this will be used to zeroize the entire score if this
		 * check fails.
		 *
		 */
		$min_size = 1;
		if( $image['width'] < 200 || $image['height'] < 200 ) {
			return $scores;
		}


		// The number of factors in this score.
		$factors = 2;

		// Convert the percentages into points.
		$ratio_score = $this->calculate_subscores( $ratio_percent, $factors, $scores['max_score']);
		$size_score  = $this->calculate_subscores( $size_percent, $factors, $scores['max_score']);

		// Populate the scores data with our updated numbers.
		$scores['current_score'] = round( $ratio_score + $size_score );
		$scores['percent']       = round( $scores['current_score'] / $scores['max_score'] );

		// Return the scores data.
		return $scores;
	}


	/**
	 * The get_input_score() will tabulate the score for any fields that are of
	 * the type "input". Most inputs fields will be sent to this method, however,
	 * some fields have specialized attributes, and as such, they will use their
	 * own method for calculations.
	 *
	 * @since  4.2.0 | 20 AUG 2020 | Created
	 * @param  string $field The name of the field
	 * @return array  The array of score data
	 *
	 */
	private function get_input_score( $field ) {

		// Establish our default $scores array.
		$scores = array(
			'percent' => 0,
			'current_score' => 0,
			'max_score' => $this->field_data[$field]['max_grade']
		);

		// Fetch and organize the data used in the calculations.
		$input_text   = $this->get_field( $field );
		$input_length = strlen( $input_text );
		$max_length   = $this->field_data[$field]['max_length'];
		$ideal_length = $this->field_data[$field]['length'];


		/**
		 * This section will check the length of the field and compare it to the
		 * ideal. If it is below 70 characters, we'll divide to length by that
		 * number to come up with a percentage. For example, the user might be
		 * 95% of the way there. If they are over 100, we'll divide the input
		 * length into 100. So for example, if they have 105, they are 5% over.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < $input_length && $input_length < $ideal_length ) {
			$length_percent = $input_length / $ideal_length;
		} elseif( 0 === $input_length ) {
			$length_percent = 0;
		} else {
			$length_percent = $ideal_length / ($ideal_length + (($input_length - $ideal_length) * 2));
		}


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the input and that the input does not exceed the maximum number
		 * of characters for this field.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < $input_length && $input_length < $max_length ) {
			$max_length_percent = 1;
		} else {
			$max_length_percent = 0;
		}


		// The number of factors in this score.
		$factors = 2;

		// Convert the percentages into points.
		$length_score     = $this->calculate_subscores( $length_percent, $factors, $scores['max_score']);
		$max_length_score = $this->calculate_subscores( $max_length_percent, $factors, $scores['max_score']);

		// Populate the scores data with our updated numbers.
		$scores['current_score'] = round( $length_score + $max_length_score );
		$scores['percent']       = round( $scores['current_score'] / $scores['max_score'] );

		// Return the scores data.
		return $scores;
	}


	/**
	 * The get_custom_tweet_score() will calculate the score for the custom
	 * tweet input box. This will use a few factors to obtain this score:
	 *
	 * 1. The ideal length of a tweet is between 71 and 100 characters.
	 * 2. The ideal number of hashtags is 2.
	 * 3. A tweet cannot exceed 280 characters.
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  string $field The name of the field
	 * @return array The scores object for this field.
	 *
	 */
	private function get_custom_tweet_score( $field ) {

		// Establish our default $scores array.
		$scores = array(
			'percent' => 0,
			'current_score' => 0,
			'max_score' => $this->field_data[$field]['max_grade']
		);

		// Fetch and organize the data used in the calculations.
		$input_text   = $this->get_field( $field );
		$input_length = strlen( $input_text );
		$max_length   = $this->field_data[$field]['max_length'];
		$ideal_length = $this->field_data[$field]['length'];


		/**
		 * This section will check the length of the tweet and compare it to the
		 * ideal. If it is below 70 characters, we'll divide to length by that
		 * number to come up with a percentage. For example, the user might be
		 * 95% of the way there. If they are over 100, we'll divide the input
		 * length into 100. So for example, if they have 105, they are 5% over.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		if( 70 < $input_length && $input_length < 100 ) {
			$length_percent = 1;
		} elseif (0 < $input_length && $input_length < 70 ) {
			$length_percent = $input_length / 70;
		} elseif ( $input_length > 70 ) {
			$length_percent = 100 / $input_length;
		} else {
			$length_percent = 0;
		}


		/**
		 * This portion of the code will count the number of hashtags that the
		 * user has included in their tweet. The ideal number is 2 so anything
		 * above or below that will reduce their score as a percentage.
		 *
		 * The resulting variable, hashtag_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		$hashtag_count = substr_count( $input_text, '#' );
		if( $hashtag_count > 2 ) {
			$hashtag_percent = 2 / $hashtag_count;
		} elseif ( $hashtag_count == 0 ) {
			$hashtag_percent = 0;
		} else {
			$hashtag_percent = $hashtag_count / 2;
		}


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 280
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
 		$max_length_percent = 0;
		if( 0 < $input_length && $input_length < 280 ) {
			$max_length_percent = 1;
		}


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		$factors          = 3;
		$max_length_score = $this->calculate_subscores( $max_length_percent, $factors, $scores['max_score'] );
		$length_score     = $this->calculate_subscores( $length_percent, $factors, $scores['max_score'] );
		$hashtag_score    = $this->calculate_subscores( $hashtag_percent, $factors, $scores['max_score'] );

		// Update the scores object with our newly calculated values.
		$scores['current_score'] = round( $length_score + $hashtag_score + $max_length_score );
		$scores['percent']       = round( $scores['current_score'] / $scores['max_score'] * 100 );

		// Return the fully realized scores array.
		return $scores;
	}


	/**
	 * The get_og_title_score() method will calculate the score for the Open
	 * Graph title field on the page. It will use the following ranking factors:
	 *
	 * 1. The ideal number of words is 5.
	 * 2. The ideal number of characters is 55 or fewer.
	 * 3. The maximum number of characters is 95 or fewer.
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  string $field The name of the field.
	 * @return array The scores array.
	 *
	 */
	private function get_og_title_score( $field ) {

		// Establish our default $scores array.
		$scores = array(
			'percent' => 0,
			'current_score' => 0,
			'max_score' => $this->field_data[$field]['max_grade']
		);

		// Fetch and organize the data used in the calculations.
		$input_text   = $this->get_field( $field );
		$input_length = strlen( $input_text );
		$max_length   = $this->field_data[$field]['max_length'];
		$ideal_length = $this->field_data[$field]['length'];


		/**
		 * This section will check the length of the input and compare it to the
		 * ideal. If they are over the ideal, we'll count each character over as
		 * 3 characters and then divide the input length into the ideal. So this
		 * is not just a percentage. Your score actually goes down quite rapidly as
		 * you go past the recommended length.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < $input_length && $input_length <= $ideal_length ) {
			$length_percent = 1;
		} elseif ( $input_length === 0 ) {
			$length_percent = 0;
		} else {
			$length_percent = $ideal_length / ($ideal_length + (($input_length - $ideal_length) * 3));
		}


		/**
		 * This section will check the word count of the field and compare to
		 * the ideal number of words. We will then divide the actual number by
		 * the ideal number to come up with a percentage score.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		$word_count = str_word_count( $input_text );
		if( $input_length == 0 ) {
			$words_percent = 0;
		} elseif( $word_count < 5 ) {
			$words_percent = $word_count / 5;
		} else {
			$words_percent = 5 / $word_count;
		}


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 280
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < $input_length && $input_length < 280 ) {
			$max_length_percent = 1;
		} elseif ( $input_length > 280 ) {
			$max_length_percent = 0;
		} else {
			$max_length_percent = 0;
		}


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		$factors          = 3;
		$max_length_score = $this->calculate_subscores( $max_length_percent, $factors, $scores['max_score'] );
		$length_score     = $this->calculate_subscores( $length_percent, $factors, $scores['max_score'] );
		$words_score      = $this->calculate_subscores( $words_percent, $factors, $scores['max_score'] );

		// Update the scores object with our newly calculated values.
		$scores['current_score'] = round( $length_score + $max_length_score + $words_score );
		$scores['percent']       = round( $scores['current_score'] / $scores['max_score'] * 100 );

		// Return the fully realized scored object.
		return $scores;
	}


	/**
	 * The get_og_description_score() method will calculate the score for the Open
	 * Graph description field on the page. It will use the following ranking factors:
	 *
	 * 1. The ideal number of characters is 60 or fewer.
	 * 2. The maximum number of characters is 200 or fewer.
	 *
	 * @since  4.2.0 | 21 AUG 2020 | Created
	 * @param  string $field The name of the field
	 * @return array The scores array.
	 *
	 */
	private function get_pinterest_description_score( $field ) {

		// Establish our default $scores array.
		$scores = array(
			'percent' => 0,
			'current_score' => 0,
			'max_score' => $this->field_data[$field]['max_grade']
		);

		// Fetch and organize the data used in the calculations.
		$input_text   = $this->get_field( $field );
		$input_length = strlen( $input_text );
		$max_length   = $this->field_data[$field]['max_length'];
		$ideal_length = $this->field_data[$field]['length'];

		/**
		 * This section will check the length of the field and compare it to the
		 * ideal. If it is below 70 characters, we'll divide to length by that
		 * number to come up with a percentage. For example, the user might be
		 * 95% of the way there. If they are over 100, we'll divide the input
		 * length into 100. So for example, if they have 105, they are 5% over.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		if( $input_length < 100 ) {
			$length_percent = $input_length / 100;
		} elseif ( $input_length > 200 ) {
			$length_percent = 200 / $input_length;
		} else {
			$length_percent = 1;
		}


		/**
		 * This portion of the code will count the number of hashtags that the
		 * user has included in their description. The ideal number is 5 - 15 so
		 * anything above or below that will reduce their score as a percentage.
		 *
		 * The resulting variable, hashtag_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		$hashtag_count = substr_count( $input_text, '#' );
		if( $hashtag_count < 5 ) {
			$hashtag_percent = $hashtag_count / 5;
		} elseif ( $hashtag_count > 15 ) {
			$hashtag_percent = 15 / $hashtag_count;
		} else {
			$hashtag_percent = 1;
		}


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 200
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < $input_length && $input_length < $max_length ) {
			$max_length_percent = 1;
		} else {
			$max_length_percent = 0;
		}


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		$factors          = 3;
		$hashtag_score    = $this->calculate_subscores( $hashtag_percent, $factors, $scores['max_score'] );
		$max_length_score = $this->calculate_subscores( $max_length_percent, $factors, $scores['max_score'] );
		$length_score     = $this->calculate_subscores( $length_percent, $factors, $scores['max_score'] );

		// Update the scores object with our newly calculated values.
		$scores['current_score'] = round( $length_score + $max_length_score + $hashtag_score );
		$scores['percent']       = round( $scores['current_score'] / $scores['max_score'] * 100 );

		// Return the fully realized scored object.
		return $scores;

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


	/**
	 * The calculate_subscores() method takes the subscore percent, number of
	 * factors, and the max score for the field, and calculates how many points
	 * this specific factor for the field is worth.
	 *
	 * @since  4.2.0 | 03 SEP 2020 | Created
	 * @param  float   $percent   The percentage score that the factor achieved.
	 * @param  integer $factors   The number of factors in this field's score.
	 * @param  integer $max_score The maximum number of points a field is worth.
	 * @return float              The calculated score.
	 *
	 */
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


	/**
	 * The get_image() method will fetch the image information for the image
	 * associated with one of the fields on the page. It will then return an
	 * associative array containing image data that can be used to calculate
	 * the size and aspect ratio of the image.
	 *
	 * @since  4.2.0 | 03 SEP 2020 | Created
	 * @todo   Make sure it handles fields with multiple images.
	 * @param  string $field The key corresponding to a field.
	 * @return array  An array of image data.
	 *
	 */
	private function get_image( $field ) {

		// Fetch the current value of the field.
		$image_id = $this->get_field($field);

		// If the image is empty, return false.
		if( empty( $image_id ) ) {
			return false;
		}


		// Fetch the image data and convert it into an associative array.
		$temp_image = wp_get_attachment_image_src( $image_id, 'full' );
		$image = array(
			'url'    => $temp_image[0],
			'width'  => $temp_image[1],
			'height' => $temp_image[2]
		);

		// Return the image data.
		return $image;
	}


	/**
	 * The fetch_score() method allows us a publicly accessible method for
	 * retrieving the current score of a post. It will pull the score from the
	 * meta field, but if it does not exist, it will instantiate the optimizer
	 * and grade the post. Either way, this method will always produce a score
	 * that can be returned to the caller.
	 *
	 * Disambiguation:
	 * fetch_score( $post_id ) is a public static method that allows you to
	 *     fetch the score of any post by passing in the post id.
	 * get_score() is a public, non-static method that allows you to get the
	 *     score from the currently instantiated SWP_Pro_Social_Optimizer object.
	 *
	 * @since  4.2.0 | 03 SEP 2020 | Created
	 * @see    $this->get_score()
	 * @param  integer $post_id The ID of the post being scored.
	 * @return integer          The 0 - 100 score of the post.
	 *
	 */
	public static function fetch_score( $post_id ) {

		// Fetch the previously calculated score of the post.
		$score = get_post_meta( $post_id, '_swp_optimization_score', true );

		// If the score is empty or false (doesn't exist), create a score for it.
		if( empty( $score ) || false === $score ) {
			$Optimizer = new SWP_Pro_Social_Optimizer( $post_id );
			$score = $Optimizer->get_score();
		}

		// Return the score to the caller.
		return $score;
	}


	/**
	 * The update_empty_scores() method will seek out any posts on the site that
	 * don't currently have an optimization score and will preemptively
	 * calculate a score for that post.
	 *
	 * @since  4.2.0 | 03 SEP 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public static function update_empty_scores() {
		global $wpdb;

		// Fetch the post id's for all the posts that already have scores.
		$post_ids = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_swp_optimization_potential' AND meta_value != ''" );

		// Fetch the posts that aren't in the list of id's above.
		$args = array(
			'post_type' => 'post',
			'post__not_in' => $post_ids,
			'nopaging' => true
		);

		// Fetch the posts into an array.
		$posts = query_posts( $args );

		// Loop through the array of posts and fetch a score for each one.
		foreach( $posts as $Post ) {
			self::fetch_score( $Post->ID );
		}
	}

	/**
	 * The get_color() method will convert a percentage into a CSS class that
	 * corresponds to a color. The method requires an integer between 0 and 100
	 * that represents the percentage score of some element being graded. This
	 * will in turn result in a color being rendered that is either red, amber,
	 * or green.
	 *
	 * @since  4.1.0 | 09 AUG 2020 | Created
	 * @param  integer percent An integer representing the percentage score being visualized.
	 * @return string          'red', 'amber', or 'green' depending on the score.
	 *
	 */
	public static function get_color( $percent ) {

		if( empty( $percent ) || false == $percent || '?' === $percent ) {
			$percent = 0;
		}

		// If the percent is passed in as a decimal, multiply it by 100.
		if( $percent > 0 && $percent < 1 ) {
			$percent = $percent * 100;
		}

		// Select the right color class based on the number passed in.
		switch(true) {
			case ($percent < 60):
				$color_class = 'red';
				break;
			case ($percent < 80):
				$color_class = 'amber';
				break;
			default:
				$color_class = 'green';
				break;
		}

		// Return the color to the caller.
		return $color_class;
	}

}
