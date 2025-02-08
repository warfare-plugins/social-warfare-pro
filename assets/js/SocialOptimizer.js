/**
 *
 * The Optimizer Class will control the interface regarding the Social Media
 * Optimization on the post editor in the post meta area and in the sidebar.
 *
 * This will provide the frontend/client-side features that the user will be
 * interacting with while they are optimizing their post for social.
 *
 * @since  4.1.0 | 08 AUG 2020 | Created
 *
 */
class SocialOptimizer {


	/**
	 * The constructor will initialize our SocialOptimizer class. While doing so
	 * it will also declare and define our class properties along the way. It
	 * will then call for an initial scanning of the page to assess the score
	 * and populate the scoring UI for the user.
	 *
	 * @since  4.1.0
	 * @param  void
	 * @return void
	 *
	 */
	constructor() {


		/**
		 * The local field_data property will contain all of the data about each
		 * of the fields on the page. This is the data that will be used to guage
		 * how well the user's input aligns with the ideal input. This will
		 * contain things like the ideal aspect ratio of an image or the ideal
		 * length of a title or description.
		 *
		 */
		this.field_data = {};


		/**
		 * The set_field_data() method will populate the local field_data property
		 * with all of the "ideal" stats for each field. We'll compare what the
		 * user inputs to those ideals to generate the scores for each field.
		 *
		 */
		this.set_field_data();


		/**
		 * The public scores property will house the tabulated scores for each
		 * social media input field on the page.
		 *
		 */
		this.scores = {
			total: 0
		};


		/**
		 * The grades property determines the cutoff scores for which we will
		 * change the colors that are presented to the users. An 'A' grade will
		 * always be presented in green, a 'B' grade will be presented in amber,
		 * and a grade below that will be presented in red.
		 *
		 * Defining them here allows us to be able to change those cutoffs
		 * throughout the entirety of the system in one single, central location.
		 *
		 */
		this.grades = {
			a: 80,
			b: 60
		}


		/**
		 * This will ensure that all of the preview images have been rendered to
		 * the screen prior to us beginning our run through of assessing and
		 * scoring everything. Those images seem to render on a delay, so checking
		 * for them at an interval solves this more effectively than the various
		 * ready() methods that exist.
		 *
		 * The scope of 'this' changes inside the anoynmous setInterval function
		 * so we'll need to store it inside a different variable so that we can
		 * still access it.
		 *
		 */
		var self = this;
		var image_check = setInterval( function() {

			// If the images have not fully rendered, just bail out.
			if( false === self.are_preview_images_rendered() ) {
				return;
			}

			// If the images are rendered, clear this interval and begin running the upate.
			clearInterval( image_check );
			self.update_scores();
			self.initialize_sidebar();
		}, 100 );


		/**
		 * This will activate our event listeners on the fields that are being
		 * graded by the system.
		 *
		 */
		this.activate_listeners();
	}


	/**
	 * The activate_listeners() method will create event listeners on the inputs
	 * and textareas in the form. Whenever the user makes changes to these fields,
	 * we'll update our local 'scores' property with the updated data. The
	 * React components can then see that updated data and re-render themselves
	 * accordingly.
	 *
	 * @since  4.1.0 | 10 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	activate_listeners() {
		self = this;

		/**
		 * Whenever a user types into the inputs, this will detect it and
		 * update the scores in real-time.
		 *
		 */
		jQuery('#social_warfare').on('input', 'input, textarea', function() {
			self.update_scores();
		});


		/**
		 * This is designed to detect a change in the input for the image fields.
		 * Whenever a user adds or removes an image, it will wait for the image
		 * preview to reflect the changes, and then it will render a new set of scores.
		 *
		 */
		jQuery('#social_warfare').on('change', 'input, textarea', function() {
			var image_check = setInterval( function() {

				// If the images have not fully rendered, just bail out.
				if( false === self.are_preview_images_rendered() ) {
					return;
				}

				// If the images are rendered, clear this interval and begin running the upate.
				clearInterval( image_check );
				self.update_scores();
			}, 50 );
		});


		jQuery(document).on('click', '.social_score_wrapper', function() {
			jQuery('.swp_sidebar_button').parent('button').click();
		});

	}


	/**
	 * The are_preview_images_rendered() method exists because the images for
	 * the social media image, pinterest image, and twitter card image are not
	 * rendered to the screen right away. This will check to see if they have
	 * been rendered and can be checked on an interval to allow us to execute
	 * our scoring procedures on them as soon as they become available.
	 *
	 * @since  4.1.0 | 08 AUG 2020 | Created
	 * @param  void
	 * @return boolean True if the images are rendered; false if not.
	 *
	 */
	are_preview_images_rendered() {

		// Generate a list of image fields that we'll be looping through to check.
		var image_fields_keys = [
			'swp_og_image',
			'swp_pinterest_image',
			'swp_twitter_card_image'
		];

		for(const field of image_fields_keys ) {

			// Fetch the image id of the user input image for that field.
			var input = jQuery('input[name="'+field+'[]"]');

			// If there is no ID, that means that no image has been uploaded.
			if( input.val() !== '' && input.is(':visible') ) {

				// Fetch the image element for the preview element.
				var image = jQuery('input[name="'+field+'[]"]').siblings('.swpmb-file-icon').find('img');

				// Check if it has a real height or is still at 0.
				if( image.height() == 0 ) {

					// Return false if it's still at 0.
					return false;
				}
			}

			// If there is no ID, that means that no image has been uploaded.
			if( input.val() === '' && input.is(':visible') ) {

				// Fetch the image element for the preview element.
				var image = jQuery('input[name="'+field+'[]"]').siblings('.swpmb-file-icon').find('img');

				// Check if it has a real height it hasn't been removed yet.
				if( image.height() > 0 ) {

					// Return false if it's still at 0.
					return false;
				}
			}
		}

		// Return true if we haven't returned false up above.
		return true;
	}


	/**
	 * The update_scores() method is designed to update all of the scores for
	 * every field on the page so that we can refresh any of the UI indicators
	 * for the user.
	 *
	 * @since  4.1.0 | 08 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	update_scores() {

		this.scores = {};
		this.scores['total'] = 0;
		this.set_field_data();

		/**
		 * Loop through each registers field and fetch the score for that
		 * individual field. We'll then add them all together into the total.
		 *
		 */
		for(const field of this.get_fields() ) {

			// This fetches the score for this individual field.
			this.scores[field] = this.get_individual_score(field);

			// This adds that score to the running total.
			this.scores['total'] = +this.scores['total'] + +this.scores[field]['current_score'];
		}

		// Just round the score to the next highest whole number.
		this.scores['total'] = Math.round( this.scores['total'] );

		// Update the visual UI's that the user can see.
		this.update_total_score_badge();

		// Trigger an event letting the rest of the application know about the update.
		jQuery(document).trigger('scores_updated');
	}


	/**
	 * The update total scores badge will update the html for the large badge at
	 * the top of the custom fields area in the post editor. This badge will
	 * display their current score as a numerator over 100 (e.g. 5/100) and be
	 * colored either red, amber, or green just like a traffic light.
	 *
	 * @since  4.1.0 | 09 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	update_total_score_badge() {

		// This will set the color of the badge based on their score.
		let color_class = this.get_alert_color(this.scores['total']);

		// This will update the badge number and color.
		jQuery('.social_score_wrapper .score_rating')
			.removeClass(['red','amber','green'])
			.addClass(color_class)
			.find('.score_rating_top')
			.text( this.scores['total'] );
	}


	/**
	 * The get_individual_score() fetch the score data for any one of the
	 * individual fields on the page. With the 'field' parameter passed in, it
	 * behaves as more of a router, passing the request along to the actual
	 * method that will be handling the computations for this data.
	 *
	 * @since  4.1.0 | 10 AUG 2020 | Created
	 * @param  string field The unique key corresponding to one of the fields.
	 * @return {object} The scores object for requested field.
	 *
	 */
	get_individual_score(field) {

		// Legacy: These route to the generic 'type' methods.
		switch(this.field_data[field]['type']) {
			case 'image':
				var scores = this.get_image_score(field);
				break;
			case 'input':
				var scores = this.get_input_score(field);
				break;
		}

		// New (But not finished): These route to the specific methods created for each field.
		switch(field) {
			case 'swp_custom_tweet':
				var scores = this.get_custom_tweet_score();
				break;
			case 'swp_og_title':
				var scores = this.get_og_title_score();
				break;
			case 'swp_pinterest_description':
				var scores = this.get_pinterest_description_score();
				break;
		}
		return scores;
	}


	/**
	 * The get_og_title_score() method will calculate the score for the Open
	 * Graph title field on the page. It will use the following ranking factors:
	 *
	 * 1. The ideal number of words is 5.
	 * 2. The ideal number of characters is 55 or fewer.
	 * 3. The maximum number of characters is 95 or fewer.
	 *
	 * @since  4.1.0 | 11 AUG 2020 | Created
	 * @param  void
	 * @return {object} The scores object.
	 *
	 */
	get_og_title_score() {


		/**
		 * This will setup the default scores object with some filler data. This
		 * data will be updated as we move throughout this method and then it
		 * will eventually be returned at the end.
		 *
		 * @type object
		 */
		var scores = {
			percent: 0,
			current_score: 0,
			max_score: this.field_data['swp_custom_tweet']['max_score'],
			messages: []
		}

		// These variables will be gathered from the field and will be used below.
		let key          = 'swp_og_title';
		var input_text   = jQuery('#'+key).val();
		var input_length = input_text.length;
		var max_length   = this.field_data[key]['max_length'];
		var ideal_length = this.field_data[key]['length'];


		/**
		 * This section will check the length of the input and compare it to the
		 * ideal. If they are over 55, we'll count each character over as
		 * 3 characters and then divide the input length into 55. So this is not
		 * just a percentage. Your score actually goes down quite rapidly as
		 * you go past the recommended length.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */

		// Greater than zero but less than the ideal length.
		if( 0 < input_length && input_length <= ideal_length ) {
			var length_percent = 1;

		// No input at all.
		} else if ( input_length === 0 ) {
			var length_percent = 0;

		// Input is too long.
		} else {
			var length_percent = ideal_length / (ideal_length + ((input_length - ideal_length) * 3));
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(length_percent * 100),
			priority: 1,
			label: 'The recommended length of the Open Graph title is 55 characters or fewer. Yours is ' + input_length + '.'
		});


		/**
		 * This section will check the word count of the field and compare to
		 * the ideal number of words. We will then divide the actual number by
		 * the ideal number to come up with a percentage score.
		 *
		 * The resulting variable, length_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		var word_count = input_text.split(" ").length;
		if( input_length == 0 ) {
			var words_percent = 0;
		} else if( word_count < 5 ) {
			var words_percent = word_count / 5;
		} else {
			var words_percent = 5 / word_count;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(words_percent * 100),
			priority: 1,
			label: 'The most engaging number of words for a title is 5. Yours is ' + word_count + '. Short and concise works best.'
		});


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 280
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < input_length && input_length < 280 ) {
			var max_length_percent = 1;
		} else if (input_length > 280 ) {
			var max_length_percent = 0;
		} else {
			var max_length_percent = 0;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(max_length_percent * 100),
			priority: 10,
			label: 'The maximum absolute length for this field is 95 characters. Your title is ' + input_length + ' characters.'
		})


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		let factors          = 3;
		let max_length_score = this.calculate_subscores( max_length_percent, factors, scores.max_score );
		let length_score     = this.calculate_subscores( length_percent, factors, scores.max_score );
		let words_score      = this.calculate_subscores( words_percent, factors, scores.max_score );

		// Update the scores object with our newly calculated values.
		scores.current_score = Math.round( +length_score + +max_length_score + +words_score );
		scores.percent       = Math.round( scores.current_score / scores.max_score * 100 );
		scores.messages      = this.sort_messages(scores.messages);

		// Return the fully realized scored object.
		return scores;
	}


	/**
	 * The get_og_description_score() method will calculate the score for the Open
	 * Graph description field on the page. It will use the following ranking factors:
	 *
	 * 1. The ideal number of characters is 60 or fewer.
	 * 2. The maximum number of characters is 200 or fewer.
	 *
	 * @since  4.1.0 | 11 AUG 2020 | Created
	 * @param  void
	 * @return {object} The scores object.
	 *
	 */
	get_og_description_score() {


		/**
		 * This will setup the default scores object with some filler data. This
		 * data will be updated as we move throughout this method and then it
		 * will eventually be returned at the end.
		 *
		 * @type object
		 */
		var scores = {
			percent: 0,
			current_score: 0,
			max_score: this.field_data['swp_custom_tweet']['max_score'],
			messages: []
		}

		// These variables will be gathered from the field and will be used below.
		let key          = 'swp_og_description';
		var input_text   = jQuery('#'+key).val();
		var input_length = input_text.length;
		var max_length   = this.field_data[key]['max_length'];
		var ideal_length = this.field_data[key]['length'];


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
		if( 0 < input_length && input_length < ideal_length ) {
			var length_percent = 1;
		} else if( 0 === input_length ) {
			var length_percent = 0;
		} else {
			var length_percent = ideal_length / (ideal_length + ((input_length - ideal_length) * 2));
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(length_percent * 100),
			priority: 1,
			label: 'The recommended length of the Open Graph description is '+ideal_length+' characters or fewer. Yours is ' + input_length + '.'
		});


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 200
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < input_length && input_length < max_length ) {
			var max_length_percent = 1;
		} else if (input_length > max_length ) {
			var max_length_percent = 0;
		} else {
			var max_length_percent = 0;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(max_length_percent * 100),
			priority: 10,
			label: 'The maximum absolute length for this field is '+max_length+' characters. Your title is ' + input_length + ' characters.'
		})


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		let factors          = 2;
		let max_length_score = this.calculate_subscores( max_length_percent, factors, scores.max_score );
		let length_score     = this.calculate_subscores( length_percent, factors, scores.max_score );

		// Update the scores object with our newly calculated values.
		scores.current_score = Math.round( +length_score + +max_length_score );
		scores.percent       = Math.round( scores.current_score / scores.max_score * 100 );
		scores.messages      = this.sort_messages(scores.messages);

		// Return the fully realized scored object.
		return scores;
	}


	/**
	 * The get_custom_tweet_score() will calculate the score for the custom
	 * tweet input box. This will use a few factors to obtain this score:
	 *
	 * 1. The ideal length of a tweet is between 71 and 100 characters.
	 * 2. The ideal number of hashtags is 2.
	 * 3. A tweet cannot exceed 280 characters.
	 *
	 * @since  4.1.0 | 11 AUG 2020 | Created
	 * @param  void
	 * @return {object} The scores object for this field.
	 *
	 */
	get_custom_tweet_score() {


		/**
		 * This will setup the default scores object with some filler data. This
		 * data will be updated as we move throughout this method and then it
		 * will eventually be returned at the end.
		 *
		 * @type {Object}
		 */
		var scores = {
			percent: 0,
			current_score: 0,
			max_score: this.field_data['swp_custom_tweet']['max_score'],
			messages: []
		}

		// These variables will be gathered from the field and will be used below.
		let factors      = 3;
		var tweet_text   = jQuery('#swp_custom_tweet').val();
		var input_length = tweet_text.length;
		var max_length   = this.field_data['swp_custom_tweet']['max_length'];
		var ideal_length = this.field_data['swp_custom_tweet']['length'];


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
		if( 70 < input_length && input_length < 100 ) {
			var length_percent = 1;
		} else if (0 < input_length && input_length < 70 ) {
			var length_percent = input_length / 70;
		} else if ( input_length > 70 ) {
			var length_percent = 100 / input_length;
		} else {
			var length_percent = 0;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(length_percent * 100),
			priority: 1,
			label: 'The ideal length of a tweet is between 71 and 100 characters. Yours is ' + input_length + '.'
		})


		/**
		 * This portion of the code will count the number of hashtags that the
		 * user has included in their tweet. The ideal number is 2 so anything
		 * above or below that will reduce their score as a percentage.
		 *
		 * The resulting variable, hashtag_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		let hashtag_count = (tweet_text.split('#').length - 1);
		if( hashtag_count > 2 ) {
			var hashtag_percent = 2 / hashtag_count;
		} else if ( hashtag_count == 0 ) {
			var hashtag_percent = 0;
		} else {
			var hashtag_percent = hashtag_count / 2;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(hashtag_percent * 100),
			priority: 2,
			label: 'For best performance, you should include 2 hashtags in your custom tweet. You have '+hashtag_count+'.'
		})


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 280
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < input_length && input_length < 280 ) {
			var max_length_percent = 1;
		} else if (input_length > 280 ) {
			var max_length_percent = 0;
		} else {
			var max_length_percent = 0;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(max_length_percent * 100),
			priority: 10,
			label: 'The maximum length for this field is 280 characters. Your custom tweet is ' + input_length + ' characters.'
		})


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		let max_length_score = this.calculate_subscores( max_length_percent, factors, scores.max_score );
		let length_score     = this.calculate_subscores( length_percent, factors, scores.max_score );
		let hashtag_score    = this.calculate_subscores( hashtag_percent, factors, scores.max_score );

		// Update the scores object with our newly calculated values.
		scores.current_score = Math.round( +length_score + +hashtag_score + +max_length_score );
		scores.percent       = Math.round( scores.current_score / scores.max_score * 100 );
		scores.messages      = this.sort_messages(scores.messages);

		// Return the fully realized scored object.
		return scores;
	}

	get_pinterest_description_score() {
		var key = 'swp_pinterest_description';

		/**
		 * This will setup the default scores object with some filler data. This
		 * data will be updated as we move throughout this method and then it
		 * will eventually be returned at the end.
		 *
		 * @type object
		 */
		var scores = {
			percent: 0,
			current_score: 0,
			max_score: this.field_data[key]['max_score'],
			messages: []
		}

		// These variables will be gathered from the field and will be used below.
		var input_text   = jQuery('#'+key).val();
		var input_length = input_text.length;
		var max_length   = this.field_data[key]['max_length'];
		var ideal_length = this.field_data[key]['length'];
		var name = this.field_data[key]['name'];

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
		if( input_length < 100 ) {
			var length_percent = input_length / 100;
		} else if (input_length > 200 ) {
			var length_percent = 200 / input_length;
		} else {
			var length_percent = 1;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(length_percent * 100),
			priority: 1,
			label: 'The recommended length of the '+name+' is 100 - 200 characters. Yours is ' + input_length + '.'
		});


		/**
		 * This portion of the code will count the number of hashtags that the
		 * user has included in their description. The ideal number is 5 - 15 so
		 * anything above or below that will reduce their score as a percentage.
		 *
		 * The resulting variable, hashtag_percent, will be used below as one of
		 * the factors to come up with our total score for this field.
		 *
		 */
		let hashtag_count = (input_text.split('#').length - 1);
		if( hashtag_count < 5 ) {
			var hashtag_percent = hashtag_count / 5;
		} else if ( hashtag_count > 15 ) {
			var hashtag_percent = 15 / hashtag_count;
		} else {
			var hashtag_percent = 1;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(hashtag_percent * 100),
			priority: 2,
			label: 'For maximum visibility, you should include 5 to 15 hashtags in your pinterest description. You have '+hashtag_count+'.'
		})


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 200
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < input_length && input_length < max_length ) {
			var max_length_percent = 1;
		} else if (input_length > max_length ) {
			var max_length_percent = 0;
		} else {
			var max_length_percent = 0;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(max_length_percent * 100),
			priority: 10,
			label: 'The absolute maximum length for this field is '+max_length+' characters. Yours is ' + input_length + ' characters.'
		})


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		let factors          = 3;
		let hashtag_score    = this.calculate_subscores( hashtag_percent, factors, scores.max_score );
		let max_length_score = this.calculate_subscores( max_length_percent, factors, scores.max_score );
		let length_score     = this.calculate_subscores( length_percent, factors, scores.max_score );

		// Update the scores object with our newly calculated values.
		scores.current_score = Math.round( +length_score + +max_length_score + +hashtag_score );
		scores.percent       = Math.round( scores.current_score / scores.max_score * 100 );
		scores.messages      = this.sort_messages(scores.messages);

		// Return the fully realized scored object.
		return scores;
	}

	get_input_score(key) {


		/**
		 * This will setup the default scores object with some filler data. This
		 * data will be updated as we move throughout this method and then it
		 * will eventually be returned at the end.
		 *
		 * @type object
		 */
		var scores = {
			percent: 0,
			current_score: 0,
			max_score: this.field_data[key]['max_score'],
			messages: []
		}

		// These variables will be gathered from the field and will be used below.
		var input_text   = jQuery('#'+key).val();
		var input_length = input_text.length;
		var max_length   = this.field_data[key]['max_length'];
		var ideal_length = this.field_data[key]['length'];
		var name = this.field_data[key]['name'];

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
		if( 0 < input_length && input_length < ideal_length ) {
			var length_percent = input_length / ideal_length;
		} else if( 0 === input_length ) {
			var length_percent = 0;
		} else {
			var length_percent = ideal_length / (ideal_length + ((input_length - ideal_length) * 2));
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(length_percent * 100),
			priority: 1,
			label: 'The recommended length of the '+name+' is '+ideal_length+' characters or fewer. Yours is ' + input_length + '.'
		});


		/**
		 * This portion of the code will check to ensure that the user has filled
		 * out the tweet and that the tweet does not exceed the maximum of 200
		 * allowable characters.
		 *
		 * The resulting variable, max_length_percent, will be used below as one
		 * of the factors to come up with our total score for this field.
		 *
		 */
		if( 0 < input_length && input_length < max_length ) {
			var max_length_percent = 1;
		} else if (input_length > max_length ) {
			var max_length_percent = 0;
		} else {
			var max_length_percent = 0;
		}

		// Generate the message associated with this ranking factor.
		scores.messages.push({
			code: this.get_alert_color(max_length_percent * 100),
			priority: 10,
			label: 'The maximum absolute length for this field is '+max_length+' characters. Yours is ' + input_length + ' characters.'
		})


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		let factors          = 2;
		let max_length_score = this.calculate_subscores( max_length_percent, factors, scores.max_score );
		let length_score     = this.calculate_subscores( length_percent, factors, scores.max_score );

		// Update the scores object with our newly calculated values.
		scores.current_score = Math.round( +length_score + +max_length_score );
		scores.percent       = Math.round( scores.current_score / scores.max_score * 100 );
		scores.messages      = this.sort_messages(scores.messages);

		// Return the fully realized scored object.
		return scores;
	}


	/**
	 * The get_image_score() method is used to grade all of the image fields on
	 * the form. It will looks at things like aspect ratio, minimum absolute
	 * dimensions to be displayed, and minimum recommended dimensions to be
	 * displayed well.
	 *
	 * @since  4.1.0 | 11 AUG 2020 | Created
	 * @param  {string} field The unique key corresponding to a field on the page.
	 * @return {object} scores The scores object.
	 *
	 */
	get_image_score(field) {


		/**
		 * This will setup the default scores object with some filler data. This
		 * data will be updated as we move throughout this method and then it
		 * will eventually be returned at the end.
		 *
		 * @type {Object}
		 */
		var scores = {
			percent: 0,
			current_score: 0,
			max_score:  this.field_data[field]['max_score'],
			messages: []
		};

		// Collect some information about our field so we can use this below.
		var image  = jQuery('input[name="'+field+'[]"]').siblings('.swpmb-file-icon').find('img');

		// If there is no image, generate a message and then bail.
		if( image.length === 0 ) {
			scores['messages'].push({
				code: 'red',
				priority: 1,
				label: 'You have not provided an optimized image.'
			});

		// If there is an image provided, then proceed with the calculations.
		} else {

			var width  = image[0].naturalWidth;
			var height = image[0].naturalHeight;


			/**
			 * This will analyze the aspect ratio of the image and compare it to
			 * the ideal aspect ratio possible. It will then create a percentage
			 * score based on how much it deviates from the ideal.
			 *
			 */

			// These are used to compare it to the ideal ratio.
			var numerator   = this.field_data[field]['numerator'];
			var denominator = this.field_data[field]['denominator'];

			// The aspect ratio (ar) and desired aspect ratio (dar)
			var ar = width / height;
			var dar = numerator/denominator;

			// Compare how close they are and return a percentage.
			if( ar > dar ) {
				var ratio_percent = dar / ar;
			} else {
				var ratio_percent = ar / dar;
			}

			// Generate the message associated with this ranking factor.
			scores['messages'].push({
				code: this.get_alert_color(ratio_percent * 100),
				priority: 1,
				label: 'The ideal aspect ratio is '+numerator+':'+denominator+'. Your image ratio is ' + parseFloat((ar*denominator).toFixed(1)) + ':'+denominator+'.'
			});


			/**
			 * This section will compare the height and width of the image to
			 * the recommended minimums. Anything below this will result
			 * in a reduction of score, but not a total loss.
			 *
			 * Each value is multiplied by 0.5 as it will be used to provide
			 * half of the score. Both the width and the height will be added
			 * together to come up with one complete score in this category.
			 *
			 */
 			var width_percent = 0.5, height_percent = 0.5;
			if( width < this.field_data[field]['width'] ) {
				var width_percent = width / this.field_data[field]['width'] * 0.5;
			}

			if( height < this.field_data[field]['height'] ) {
				var height_percent = height / this.field_data[field]['height'] * 0.5;
			}

			// Add the two halves of the score back together.
			var size_percent = width_percent + height_percent;

			// Generate the message associated with this ranking factor.
			scores['messages'].push({
				code: this.get_alert_color(size_percent * 100),
				priority: 2,
				label: 'The recommended size for this image is at least '+this.number_format(this.field_data[field]['width'])+'px by '+this.number_format(this.field_data[field]['height'])+'px. Your image is ' + this.number_format(width) + 'px by ' + this.number_format(height) +'px.'
			});


			/**
			 * This section will compare the height and width of the image to
			 * the absolute bear minimums. Anything below this will result in
			 * the image not displaying on Facebook at all. It's a complete
			 * deal breaker.
			 *
			 */
 			var min_size = 1;
			if( width < 200 || height < 200 ) {
				var min_size = 0;
			}

			// Generate the message associated with this ranking factor.
			scores['messages'].push({
				code: this.get_alert_color(min_size * 100),
				priority: 10,
				label: 'The absolute minimum size for this image is '+this.number_format(this.field_data[field]['min_width'])+'px by '+this.number_format(this.field_data[field]['min_height'])+'px. Your image is ' + this.number_format(width) + 'px by ' + this.number_format(height) +'px.'
			});
		}


		/**
		 * This will convert each of the raw percentages (0.3) into an integer
		 * equal to a part of the maximum score for this field. So, if their are
		 * 3 factors and the maximum score is 15, then each factor can be worth
		 * as maximum of 5 points. If the user got a 50%, that will make that
		 * factor worth 2.5 points.
		 *
		 */
		let factors     = 2;
		let ratio_score = this.calculate_subscores( ratio_percent, factors, scores.max_score );
		let size_score  = this.calculate_subscores( size_percent, factors, scores.max_score );


		/**
		 * The the image failed the minimum size check, then all scores will be
		 * returned as zero. It is 100% not-optimized for social if the image is
		 * not even going to be displayed on social. Therefore it becomes a
		 * total kill if it fails.
		 *
		 * Otherwise, we'll add up the ranking factors and send the data down
		 * the pipeline to the UI.
		 *
		 */
		if( 1 === min_size ) {
			scores.current_score = Math.round( +ratio_score + +size_score );
			scores.percent       = Math.round( scores.current_score / scores.max_score * 100 );
		} else {
			scores.current_score = scores.percent = 0;
		}

		// Sort the generated messages by code and priority.
		scores.messages = this.sort_messages(scores.messages);

		// Return the scores object to the caller.
		return scores;
	}


	/**
	 * The create_subscores() method is used to determine partial scores for
	 * each field. Many fields are graded on a multitude of factors. This will
	 * take the score of just one of those factors, and make it equal to the part
	 * it needs to play when combined with all other factors.
	 *
	 * For example, if a field has 3 factors and is worth a maximum of 15 points,
	 * that means each factor is worth up to 5 points. If a factor scores an 80%,
	 * it will be worth 80% of 5 point, or 4 points.
	 *
	 * @since  4.1.0 | 11 AUG 2020 | Created
	 * @param  {integer} percent   The percentage score of the factor.
	 * @param  {integer} factors   The number of factors that are part of the total grade.
	 * @param  {integer} max_score The maximum grade for the field's total grade.
	 * @return {integer}           The number of points.
	 *
	 */
	calculate_subscores( percent, factors, max_score ) {
		return percent * max_score / factors;
	}


	/**
	 * The sort_messages() method is a helper method that will ensure that our
	 * messages/tips that are displayed to the user are always sorted in the most
	 * logical and easy to consume manner.
	 *
	 * This method uses to factors to determine which messages appear first:
	 * 1. Red appears before amber, amber appears before green. This means that
	 *    your worst grades come right to the top for your attention.
	 * 2. A priority property exists for each message. In the event that two
	 *    messages are the same color, the priority property is used. Lower
	 *    priorities appear first (1 appears before 2).
	 *
	 * @since  4.1.0 | 11 AUG 2020 | Created
	 * @param  {array} messages An array of message objects.
	 * @return {array}          The modified (sorted) array of message objects.
	 *
	 */
	sort_messages(messages) {
		messages.sort( function( a, b ) {

			// (a) Create a priority based on the color code of this message.
			switch(a.code) {
				case 'red':
					var first = 1;
					break;
				case 'amber':
					var first = 2;
					break;
				case 'green':
					var first = 3;
					break;
			}

			// (b) Create a priority based on the color code of this message.
			switch(b.code) {
				case 'red':
					var second = 1;
					break;
				case 'amber':
					var second = 2;
					break;
				case 'green':
					var second = 3;
					break;
			}

			// Compare the priorities created above.
			if( first > second ) {
				return 1;
			} else if ( second > first ) {
				return -1;
			}

			// In a tie, use the actual priority field, lowest priority first.
			return a.priority < b.priority;

		});

		// Return the modified messages array.
		return messages;
	}

	set_field_data() {
		var field_data = {
			swp_og_image: {
				name: 'Open Graph Image',
				type: 'image',
				width: 1200,
				height: 628,
				min_width: 200,
				min_height: 200,
				numerator: '1.9',
				denominator: '1',
				max_score: this.get_max_scores('swp_og_image')
			},
			swp_og_title: {
				name: 'Open Graph Title',
				type: 'input',
				length: 55,
				max_length: 95,
				max_score: this.get_max_scores('swp_og_title')
			},
			swp_og_description: {
				name: 'Open Graph Description',
				type: 'input',
				length: 60,
				max_length: 200,
				max_score: this.get_max_scores('swp_og_description')
			},
			swp_twitter_card_title: {
				name: 'Twitter Card Title',
				type: 'input',
				length: 55,
				max_length: 95,
				max_score: this.get_max_scores('swp_twitter_card_title')
			},
			swp_twitter_card_description: {
				name: 'Twitter Card Description',
				type: 'input',
				length: 55,
				max_length: 150,
				max_score: this.get_max_scores('swp_twitter_card_description')
			},
			swp_twitter_card_image: {
				name: 'Twitter Card Image',
				type: 'image',
				width: 1200,
				height: 628,
				min_width:200,
				min_height:200,
				numerator: '1.9',
				denominator: '1',
				max_score: this.get_max_scores('swp_twitter_card_image')
			},
			swp_custom_tweet: {
				name: 'Custom Tweet',
				type: 'input',
				length: 100,
				max_length: 240,
				max_score: this.get_max_scores('swp_custom_tweet')
			},
			swp_pinterest_image: {
				name: 'Pinterest Image',
				type: 'image',
				width: 735,
				height: 1102,
				min_width:238,
				min_height:356,
				numerator: '2',
				denominator: '3',
				max_score: this.get_max_scores('swp_pinterest_image')
			},
			swp_pinterest_description: {
				name: 'Pinterest Description',
				type: 'input',
				length: 500,
				max_length: 500,
				max_score: this.get_max_scores('swp_pinterest_description')
			}
		}

		const fields = this.get_fields();

		for( const field of fields ) {
			this.field_data[field] = field_data[field];
		}

	}


	/**
	 * The get_max_scores() method will return the maximum allowable score for
	 * each individual field on the page. The scores should always add up to a
	 * total of 100 points.
	 *
	 * This will also check if Twitter Cards are set to copy the open graph fields.
	 * They are set to copy them, those fields are invisible and we don't need
	 * to grade them. If they are not being copied, they are visible and we
	 * need to provide a score for them.
	 *
	 * @since  4.1.0 | 08 AUG 2020 | Created
	 * @param  string field (optional) The unique key for the field being graded.
	 * @return integer/array An array of max scores or an individual score.
	 *
	 */
	get_max_scores(field = 0) {

		if(jQuery('#swp_twitter_use_open_graph').is(':checked')) {
			var max_grades = {
				swp_og_image: 20,
				swp_og_title: 15,
				swp_og_description: 15,
				swp_custom_tweet: 15,
				swp_pinterest_image: 20,
				swp_pinterest_description: 15
			};
		} else {
			var max_grades = {
				swp_og_image: 15,
				swp_og_title: 10,
				swp_og_description: 10,
				swp_custom_tweet: 10,
				swp_twitter_card_image: 10,
				swp_twitter_card_title: 10,
				swp_twitter_card_description: 10,
				swp_pinterest_image: 15,
				swp_pinterest_description: 10
			};
		}

		// If a field was passed in, return only that field.
		if( 0 !== field ) {
			return max_grades[field];
		}

		// Otherwise return all fields as an object.
		return max_grades;
	}

	get_fields() {
		return Object.keys( this.get_max_scores() );
	}

	initialize_sidebar() {
		var self = this;

		// Bail out if we don't have the Gutenberg objects we need.
		if('undefined' === typeof wp.plugins ) {
			return;
		}
		// this.rax_generate_sidebar();
		this.plugin = wp.plugins.registerPlugin( 'social-warfare-pro-sidebar', {
		    render: function() {
				return React.createElement( wp.editPost.PluginSidebar,
					{
						name: 'social-warfare-pro-sidebar',
						className: 'social-warfare-sidebar',
						icon: self.rax_generate_icon(),
						title: 'Optimize for Social'
					},
					[
						self.rax_generate_total_score_section(),
						wp.element.createElement(SWPSidebar)
					]
				);
			}
		} );

	}

	// Deprecated
	rax_generate_sidebar_sections() {
		let fields = ['swp_og_image'];
		let elements = [];

		for( const field_key of Object.keys( this.scores ) ) {
			if( field_key === 'total' ) continue;
			let element = wp.element.createElement( SWPSidebarSection, { field_key: field_key, messages: this.scores[field_key].messages } );
			elements.push(element);
		}
		return elements;

	}


	/**
	 * The rax_generate_total_score_section() will render the html for the
	 * upper most section of the sidebar. This section will contain the Social
	 * Warfare logo, followed by the title of the sidebar, the color-coded grade
	 * and a call to action to engage the user.
	 *
	 * @since  4.1.0 | 09 AUG 2020 | Created
	 * @param  void
	 * @return object The result of wp.element.createElement / A react object.
	 *
	 */
	rax_generate_total_score_section() {

		return wp.element.createElement( 'div', { className: 'sidebar_score_wrapper' }, [
			// wp.element.createElement('img', {className: 'swp-logo', src: '/wp-content/plugins/social-warfare/assets/images/admin-options-page/social-warfare-pro-light.png'} ),
			wp.element.createElement('div', {className:'score_title'}, 'Optimize for Social' ),
			wp.element.createElement(SWPScoreBadge, {section:'total', score: this.scores.total, max_score: 100 }),
			wp.element.createElement('div', {className: 'swp_clearfix'} ),
			wp.element.createElement('p', {className: 'pro-tip'}, 'Follow the tips below to maximize your reach on social media.')
		]);
	}


	/**
	 * The rax_generate_icon() method will use the wp.element/react createElement
	 * method to create the icon for the top of the gutenberg dashboard. This
	 * function will use the SVG data of the social warfare icon to make the icon
	 * use our branded logo.
	 *
	 * @since  4.1.0 | 09 AUG 2020 | Created
	 * @param  void
	 * @return object The result of wp.element.createElement / a react component.
	 *
	 */
	rax_generate_icon() {
		return wp.element.createElement('svg', { height: 24, width: 24, viewBox: '0 -2 24 24', className: 'swp_sidebar_button' },
		  wp.element.createElement('path', { d: "M12 1.5q-3.75 0-6.375 2.625t-2.625 6.375 2.625 6.375 6.375 2.625 6.375-2.625 2.625-6.375-2.625-6.375-6.375-2.625zM11.906 17.391q-2.578 0-3.68-1.266t-1.195-2.766q0-0.469 0.563-0.609l2.063-0.469q0.609-0.094 0.609 0.563 0.141 1.406 1.641 1.406 0.703 0 1.148-0.328t0.352-0.703q-0.141-0.516-1.102-0.961t-2.063-0.82-1.992-1.359-0.891-2.438q0-0.375 0.141-0.891t0.516-1.266 1.383-1.242 2.508-0.492q2.625 0 3.727 1.242t1.148 2.648q0 0.469-0.516 0.609l-2.156 0.469q-0.609 0.094-0.609-0.469-0.234-1.266-1.641-1.266-0.563 0-0.891 0.258t-0.234 0.633q0.094 0.469 1.102 0.938t2.133 0.867 2.063 1.43 0.938 2.531q0 0.141-0.070 0.516t-0.398 0.961-0.867 1.078-1.5 0.844-2.227 0.352z" } )
		);
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
	get_alert_color(percent) {

		switch(true) {
			case (percent < this.grades.b):
				var color_class = 'red';
				break;
			case (percent < this.grades.a):
				var color_class = 'amber';
				break;
			default:
				var color_class = 'green';
				break;
		}
		return color_class;
	}


	/**
	 * The number_format() method will add commas as thousand separators.
	 *
	 * @since  4.1.0 | 13 AUG 2020 | Created
	 * @param  {number} num The number to be formatted.
	 * @return {string}     The formatted number
	 */
	number_format(num) {
		return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
	}

}


/**
 * The SWPSidebar class is a React Component class that will control the
 * overall layout of the sidebar. It's primary purposes are to render the sidebar
 * html and to listen for certain updates to the forms so that it can then
 * trigger a new render as needed.
 *
 * @since   4.1.0 | 14 AUG 2020 | Created
 * @extends React.Component
 *
 */
class SWPSidebar extends React.Component {


	/**
	 * The magic constructor will run when the class is instantiated. When it
	 * does, it will run the React.Component parent constructor which will import
	 * all of the props that have been passed into it. It will then set up
	 * some event listeners so that it can rerender the sidebar if certain things
	 * change.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  {object} props The object props
	 * @return void
	 *
	 */
	constructor(props) {

		// Call the parent constructor
		super(props);

		// Here we bind "this" to make it accessible within our methods.
		this.update_sidebar = this.update_sidebar.bind(this);

		// If the Twitter toggle gets changed, we update the entire sidebar since
		// it will be switching between 6 and 9 fields to grade.
		let toggle = document.getElementById('swp_twitter_use_open_graph');
		if( null !== toggle ) {
			toggle.onchange = this.update_sidebar;
		}
	}


	/**
	 * The update_sidebar() method will update the scores and then rerender the
	 * entire sidebar. Right now this only runs with the Twitter toggle changes
	 * because we will switch between 6 and 9 fields that make up the 100% grade.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	update_sidebar() {
		socialWarfare.SocialOptimizer.update_scores();
		this.forceUpdate();
	}


	/**
	 * The render() method will return a set of React elements to be rendered to
	 * the screen. It defines what this element will look like. This is the
	 * mandatory method for these React.Component subclasses.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  void
	 * @return {object} The react elements that comprise this element's html output.
	 *
	 */
	render() {

		// We'll be creating an array of section elements to return to the caller.
		let sections = [];

		// Loop through every field for which we have a score.
		for( const field_key of Object.keys( self.scores ) ) {

			// Skip the total, that is a different element.
			if( field_key === 'total' ) continue;

			// Render the sidebar "section" associated with this field.
			let section = wp.element.createElement( SWPSidebarSection, { field_key: field_key, messages: self.scores[field_key].messages } );

			// Add this to our array of sections.
			sections.push(section);
		}

		// Return the array of section elements.
		return sections;
	}
}


/**
 * The SWPSidebarSection class is React.Component that will define how each
 * Sidebar section is rendered. Each portion of the sidebar that corresponds to
 * a specific field being graded is called a "section".
 *
 * The overall sidebar is made up of sections.
 * Each section contains a header element and then "messages".
 *
 * @since   4.1.0 | 14 AUG 2020 | Created
 * @extends React.Component
 *
 */
class SWPSidebarSection extends React.Component {


	/**
	 * The magic constructor will run when the class is instantiated. When it
	 * does, it will run the React.Component parent constructor which will import
	 * all of the props that have been passed into it. It will then set up
	 * some event listeners so that it can rerender the sidebar if certain things
	 * change.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  {object} props The object props
	 * @return void
	 *
	 */
	constructor(props) {

		// Run the React.Component parent constructor.
		super(props);

		// Set the default state of our 'visible' prop.
		this.setState({
			visible: props.visible || 'hidden',
			scores: socialWarfare.SocialOptimizer.scores[props.field_key]
		});

		// Here we bind 'this' so that it is accessible within our methods.
		this.focus_toggle = this.focus_toggle.bind(this);
		this.focus = this.focus.bind(this);

		// This will trigger our methods whenever the inputs are focused or changed.
		jQuery(document).on('focus', '#social_warfare textarea, #social_warfare input', this.focus);
		jQuery(document).on('change', '#social_warfare textarea, #social_warfare input', this.focus );
	}


	/**
	 * The focus() method will run whenever one of our fields is interacted with.
	 * If the field belongs to this section, this section will become visible.
	 * Otherwise, this section will hide.
	 *
	 * This creates the effect of all of the sections closing except for the
	 * section associated with the field the user is working in. Hence, it focuses
	 * the entire sidebar on the section that is relevent to what they are working on.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  {object} event The event that triggered this method.
	 * @return void
	 *
	 */
	focus(event) {

		// Update the 'visible' and 'messages' props
		this.props.visible  = 'hidden';
		this.props.messages = socialWarfare.SocialOptimizer.scores[this.props.field_key].messages;

		// If the event was triggered by this section's field, show it.
		if(jQuery(event.target).is('#'+this.props.field_key) || jQuery(event.target).is('[name="'+this.props.field_key+'"]')) {

			// Update the 'visible' prop
			this.props.visible  = 'visible';
		}

		// Setting the state will trigger a rerender of the element.
		this.setState({
			visible:this.props.visible,
			messages: this.props.messages
		});
	}


	/**
	 * The focus_toggle() method will make this section toggle the visibility
	 * on or off whenever the header section is clicked on.
	 *
	 * @since  4.1.0 | 15 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	focus_toggle() {

		// Whatever visibility state is in, switch it to the other.
		if( 'visible' === this.props.visible ) {
			this.props.visible = 'hidden';
		} else {
			this.props.visible = 'visible';
		}

		// Update the messages prop so that we don't revert to a previous state.
		this.props.messages = socialWarfare.SocialOptimizer.scores[this.props.field_key].messages;

		// Update the state so that it forces the render() method to run.
		this.setState({
			visible:this.props.visible,
			messages: socialWarfare.SocialOptimizer.scores[this.props.field_key].messages
		});
	}


	/**
	 * The render() method will return a set of React elements to be rendered to
	 * the screen. It defines what this element will look like. This is the
	 * mandatory method for these React.Component subclasses.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  void
	 * @return {object} The react elements that comprise this element's html output.
	 *
	 */
	render() {
		let element =
		wp.element.createElement( 'div', { className: 'sidebar_section_wrapper ' + this.props.field_key }, [
			wp.element.createElement('div', { className: 'section-title-wrapper', onClick: this.focus_toggle }, [
				wp.element.createElement('div', {className:'score_title ' + this.props.field_key}, socialWarfare.SocialOptimizer.field_data[this.props.field_key].name ),
				wp.element.createElement(SWPScoreBadge, {
					section:this.props.field_key,
					score: socialWarfare.SocialOptimizer.scores[this.props.field_key].current_score,
					max_score: socialWarfare.SocialOptimizer.scores[this.props.field_key].max_score
				}),
			]),
			wp.element.createElement('div', {className: 'section_messages_wrapper ' + this.props.visible} ,
				wp.element.createElement(SWPScoreMessages, { section:this.props.field_key, messages: this.props.messages })
			)
		]);
		return element;
	}
}


/**
 * The SWPScoreBadge class is a child class of React.Component that controls the
 * rendering and updating of the colored and numbered score badges throughout
 * the sidebar. This will generate it in the right color and with the correct
 * score listed in it.
 *
 * @since   4.1.0 | 17 AUG 2020 | Created
 * @extends React.Component
 *
 */
class SWPScoreBadge extends React.Component {


	/**
	 * The magic constructor will run when the class is instantiated. When it
	 * does, it will run the React.Component parent constructor which will import
	 * all of the props that have been passed into it. It will then set up
	 * some event listeners so that it can rerender the sidebar if certain things
	 * change.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  {object} props The object props
	 * @return void
	 *
	 */
	constructor(props) {

		// Call the parent constructor
		super(props);

		// Bind 'this' to our methods.
		this.update = this.update.bind(this);

		// Setup the initial score state.
		this.setState({score: this.props.score});

		// Whenever the scores are updated, trigger our update method.
		jQuery(document).on('scores_updated', this.update );
	}


	/**
	 * The update method will run whenever the score changes and will ultimately
	 * result in the element being rerendered.
	 *
	 * @since  4.1.0 | 17 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	update() {

		// If the scores property for this section doesn't exist, bail early.
		if('undefined' === typeof socialWarfare.SocialOptimizer.scores[this.props.section] ) {
			return;
		}

		// Pull in the score for this section.
		if( this.props.section == 'total' ) {
			this.props.score = socialWarfare.SocialOptimizer.scores.total;
		} else {
			this.props.score = socialWarfare.SocialOptimizer.scores[this.props.section].current_score;
		}

		// Updating the state will trigger a rerender.
		this.setState({score: this.props.score});
	}


	/**
	 * The render() method will return a set of React elements to be rendered to
	 * the screen. It defines what this element will look like. This is the
	 * mandatory method for these React.Component subclasses.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  void
	 * @return {object} The react elements that comprise this element's html output.
	 *
	 */
	render() {
		return wp.element.createElement('div', {className:'score_rating ' + self.get_alert_color(this.props.score/this.props.max_score*100) }, [
			wp.element.createElement('div', {className:'score_rating_top'}, this.props.score ),
			wp.element.createElement('div', {className:'score_rating_bottom'}, this.props.max_score )
		]);
	}
}

class SWPScoreMessages extends React.Component {


	/**
	 * The magic constructor will run when the class is instantiated. When it
	 * does, it will run the React.Component parent constructor which will import
	 * all of the props that have been passed into it. It will then set up
	 * some event listeners so that it can rerender the sidebar if certain things
	 * change.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  {object} props The object props
	 * @return void
	 *
	 */
	constructor(props) {

		// Call the parent constructor
		super(props);

		// Bind 'this' to our methods.
		this.update = this.update.bind(this);

		// Setup the initial messages state.
		this.setState({messages: this.props.messages});

		// Whenever the scores are updated, trigger our update method.
		jQuery(document).on('scores_updated', this.update );
	}


	/**
	 * The update method will run whenever the score changes and will ultimately
	 * result in the element being rerendered.
	 *
	 * @since  4.1.0 | 17 AUG 2020 | Created
	 * @param  void
	 * @return void
	 *
	 */
	update() {

		// If the scores data for this section doesn't exist, bail out.
		if('undefined' === typeof socialWarfare.SocialOptimizer.scores[this.props.section] ) {
			return;
		}

		// Update the messages prop with the updated array of messages.
		this.props.messages = socialWarfare.SocialOptimizer.scores[this.props.section].messages;

		// Setting the state will force a rerender of the element.
		this.setState({score: socialWarfare.SocialOptimizer.scores.messages});
	}


	/**
	 * The render() method will return a set of React elements to be rendered to
	 * the screen. It defines what this element will look like. This is the
	 * mandatory method for these React.Component subclasses.
	 *
	 * @since  4.1.0 | 14 AUG 2020 | Created
	 * @param  void
	 * @return {object} The react elements that comprise this element's html output.
	 *
	 */
	render() {

		// If for some reason, we don't have the messages prop, bail out early.
		if('undefined' == typeof this.props.messages ) {
			return null;
		}

		// Loop through and create a paragraph React.Component for each message.
		let elements = [];
		this.props.messages.forEach(function(message) {
			let element = wp.element.createElement('p', {className: 'individual-tip ' + message.code}, message.label);
			elements.push(element);
		});
		return elements;
	}
}

// Fire up the whole thing, right here.
jQuery(document).ready( function() {
	window.socialWarfare = window.socialWarfare || {};
	socialWarfare.SocialOptimizer = new SocialOptimizer();
});
