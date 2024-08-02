<?php

/**
 * Hosts our Pro features for Pinterest.
 *
 * This class contains a host of methods that at some pretty cool Pinterest
 * related functionality to the plugin.
 *
 * 1. It creates and controls the output for the [pinterest_image] shortcode.
 * 2. It adds Pinterest images to posts via the_content filter.
 * 3. It adds data-pin-description to images dynamically via the_content filter.
 *
 * @since  3.2.0 | 26 JUL 2018 | Created the class.
 *
 */
class SWP_Pro_Pinterest {


	/**
	 * Initialize the hooks and filters.
	 *
	 * @since  3.2.0 | 26 JUL 2018 | Created
	 * @since  3.3.2 | 14 SEP 2018 | Added admin an singular checks.
	 * @param  void
	 * @return void
	 *
	 */
	public function __construct() {
		if ( $this->should_bail() ) {
			return;
		}

		// Admin hooks for editing pinterest-specific content.
		$this->add_admin_actions();

		// Defer to a later hook so `global $post` is defined.
		add_filter( 'template_redirect', array( $this, 'add_frontend_actions' ) );
		add_filter( 'swp_footer_scripts', array( $this, 'pinit_controls_output' ) );
	}


	/**
	 * There are certain conditions under which we should simply exclude all of
	 * the functionality in this class to avoid conflicts.
	 *
	 * @since 3.2.0 | 26 JUL 2018 | Created
	 * @param  void
	 * @return bool True: bail; false: continue
	 *
	 */
	public function should_bail() {

			// Bail if another plugin is causing a conflict with this one.
			if ( Social_Warfare::has_plugin_conflict() ) {
					return true;
			}

			// We don't want these Pinterest features on feeds and archives.
			if( is_feed() || is_archive() ) {
					return true;
			}

			// We don't need Pinterest images on pages delivered via AMP.
			if ( class_exists( 'SWP_AMP' ) && SWP_AMP::is_amp() ) {
				return true;
			}

			return false;
	}


	/**
	 * Add the Pinterest related admin hooks and filters.
	 *
	 * @since  3.2.3 | 17 SEP 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function add_admin_actions() {
			if ( false == is_admin() ) {
				return;
			}

			// Gutenberg does not have the same editor hooks.
			if ( !function_exists( 'is_gutenberg_page' ) ) {
				add_filter( 'image_send_to_editor', array( $this, 'classic_editor_add_pin_description'), 10, 8 );
			}

			add_filter( 'attachment_fields_to_edit', array( $this, 'edit_media_custom_field'), 11, 2 );
			add_filter( 'attachment_fields_to_save', array( $this, 'save_media_custom_field'), 11, 2 );
	}


	/**
	 * Add the Pinterest related frontend hooks and filters.
	 *
	 * This is deferred to the "template_redirect" hook so that we have access
	 * to the necessary conditionals (e.g. is_singular()) in order to control
	 * what gets queued up where and when.
	 *
	 * @since  3.2.3 | 17 SEP 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	public function add_frontend_actions() {

		/**
		 * By using the callback '__return_false', it will cause the shortcode
		 * to be processed but not output anything. This will stop the raw
		 * shortcode text [pinterest_image] from appearing on excerpts.
		 *
		 */
		if ( false == is_singular() ) {
			add_shortcode( 'pinterest_image', '__return_false' );
			return;
		}


		/**
		 * This is the filter that will add the data-pin-description to each
		 * image in the post ensuring that Pinterest and Tailwind browser
		 * extensions will have descriptions to use when sharing.
		 *
		 */
		if ( true === SWP_Utility::get_option( 'pinterest_data_attribute' ) ) {
			add_filter( 'the_content', array( $this, 'content_add_pin_description' ), 1 );
		}


		/**
		 * This is the filter that adds the "no-pin" class to images that the
		 * user has opted out of the Image Hover Pin buttons.
		 *
		 */
		if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) {
			add_filter( 'the_content', array( $this, 'content_maybe_add_no_pin' ) );
		}

		add_shortcode( 'pinterest_image', array( $this, 'pinterest_image_shortcode' ) );
		add_filter( 'the_content', array( $this, 'maybe_insert_pinterest_image') ) ;
	}


	/**
	 * This method will setup the Pinterest images that get inserted into the
	 * content. These may appear at the top or bottom of the post and may be set
	 * to be visible to the user or hidden and only visible to the Pinterest
	 * scrapers.
	 *
	 * Corresponds to the post_meta->swp_pinterst_image,
	 * The fallback in Advanced Pinterest Settings -> Pinterest Image Fallback,
	 * and Advanced Pinterst Settings > Pinterest Image Location.
	 *
	 * The resulting image has all normal attributes in addition to a customized
	 * `data-pin-description`. When Pinterest's official browser extension
	 * and others like Tailwind scrape the page, they will pick up and see the
	 * Pinterest optimized image along with the Pinterest optimized description.
	 *
	 * @since  2.2.4 | 09 MAR 2017 | Created
	 * @since  3.3.0 | 20 AUG 2018 | Refactored the method.
	 * @since  3.3.2 | 13 SEP 2018 | Added check for is_singular()
	 * @since  3.4.0 | 01 NOV 2018 | Added check for global on/off status.
	 * @since  4.0.0 | 01 MAR 2020 | Setup support for multiple Pinterest images.
	 * @param  string $content The post content to filter
	 * @return string $content The filtered content
	 *
	 */
	public function maybe_insert_pinterest_image( $content ) {
		global $post;
		$post_id = $post->ID;

		// Whether or not the Pinterest image is turned on or off for this post.
		$meta_browser_extension = get_post_meta( $post_id, 'swp_pin_browser_extension', true );


		/**
		 * If the option is turned off globally, and the post level option is
		 * set to default, bail out and keep this feature turned off. If the
		 * post level option is set to off, it will get caught below.
		 *
		 */
		if ( false == SWP_Utility::get_option( 'pin_browser_extension' ) && 'default' == $meta_browser_extension ) {
			return $content;
		}

		/**
		 * Bail early if the Pinterest browser image is explicitly turned to the
		 * off position at the post level.
		 *
		 */
		if ( 'off' == $meta_browser_extension ) {
			return $content;
		}


		// Check to see if the image is set, even if the url is not.
		$pinterest_images = get_post_meta( $post_id, 'swp_pinterest_image', false );

		// Bail if we have no Pinterest images assigned.
		if ( false === $pinterest_images ) {
			return $content;
		}

		// Determine the location where the Pinterest image should be output.
		$pinterest_image_location = get_post_meta( $post_id, 'swp_pin_browser_extension_location', true );
		if ( 'default' == $pinterest_image_location ) {
			$pinterest_image_location = SWP_Utility::get_option( 'pinterest_image_location' );
		}

		// Set up the Pinterest username, if it exists.
		$pinterest_username = SWP_Utility::get_option( 'pinterest_id' );
		$pinterest_username = $pinterest_username ? ' via @' . str_replace( '@' , '' , $pinterest_username ) : '';

		/**
		 * Now we'll set up the description as it should appear when the image
		 * is pinned to a user's board. If the user has set one, we'll use that,
		 * if not, we'll use some fallbacks to set one up.
		 *
		 */

		// Check if the user has set up a Pinterest description.
		$pinterest_description = get_post_meta( $post_id , 'swp_pinterest_description' , true );

		// If there is no custom description, use the post Title
		if ( false == $pinterest_description || empty( $pinterest_image_url ) ) {
			$pinterest_description = $post->post_title;
		}

		// Clean and filter the Pinterest description.
		$pinterest_description = addslashes ( SWP_Pinterest::trim_pinterest_description( $pinterest_description ) );

		// Loop through each of the assigned Pinterest images to build the output.
		foreach( $pinterest_images as $pinterest_image_id ) {

			// Fetch the URL of the current Pinterest image.
			$pinterest_image_url = wp_get_attachment_url( $pinterest_image_id, 'full' );

			// If the image is set to be hidden...
			if ( 'hidden' === $pinterest_image_location ) {
				$image_html = '<img class="swp_hidden_pin_image swp-pinterest-image" src="' . $pinterest_image_url .
								'" data-pin-url="' . get_the_permalink() .
								'" data-pin-media="' . $pinterest_image_url .
								'" alt="' . $pinterest_description .
								'" data-pin-description="' . $pinterest_description .
								'" />';

				$content = $content . $image_html;

			// If the image is not set to be hidden...
			} else {

				// Give the image a SWP container for customers to use in selectors.
				$class = "swp-pinterest-image-$pinterest_image_location";
				$image_html = '<div class="swp-pinterest-image-wrapper ' . $class . '">
									<img class="swp-pinterest-image " src="' . $pinterest_image_url .
								'" alt="' . $pinterest_description .
								'" data-pin-url="' . get_the_permalink() .
								'" data-pin-media="' . $pinterest_image_url .
								'" data-pin-description="' . $pinterest_description .
								'" />
								</div>';

				// If it's set to appear at the top of the post.
				if ('top' === $pinterest_image_location) {
					$content = $image_html . $content;
				}

				// If it's set to appear at the bottom of the post.
				if ('bottom' === $pinterest_image_location) {
					$content = $content . $image_html;
				}

			}

		}

		return $content;
	}


	/**
	 * Get the Pinterest description from a post, or the selected fallback.
	 *
	 * Priority of fallback goes to: Alt text,
	 * @param  int $id The Post to check for a pinterest description.
	 * @return string $html Our version of the markup.
	 *
	 */
	public static function get_pin_description( $image_id ) {
		$description = '';
		$description_source = SWP_Utility::get_option( 'pinit_image_description' );

		if ( 'custom' == $description_source ) {
			$description = get_post_meta( $image_id, 'swp_pinterest_description', true );
		}

		else if ( 'alt_text' == $description_source ) {
			$description = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

			// Fallbacks: WP Description, Caption, then Title.
			if ( empty( $description ) ) {
				$image = get_post( $image_id );
				$description = $image->post_content;
			}

			if ( empty( $description ) ) {
				$description = $image->post_excerpt;
			}

			if ( empty ( $description) ) {
				$description = $image->post_title;
			}
		}

		return $description;
	}


	/**
	 * Get the image alignment style for the image wrapper.
	 *
	 * @param  string $alignment One of '', 'center', 'left', 'right'
	 * @return string $style The style declaration for an image wrapper element.
	 *
	 */
	public static function get_alignment_style( $alignment = '' ) {
		switch ( $alignment ) {
			default:
				$style = '';
			case 'left':
				$style = 'style="text-align: left;"';
				break;
			case 'right':
				$style = 'style="text-align: right;"';
				break;
			case 'center':
				$style = 'style="text-align: center;"';
				break;
		}

		return $style;
	}


	/**
	 * Adds a data-pin-description from within the WP Editor.
	 *
	 * This filter callback receives many variables.
	 * $html is the fully rendered HTML that WordPress created.
	 * We are bascially ignoring it and creating our own.
	 *
	 * @since 3.5.2 | 08 MAR 2019 | Copied the $pinterest_description logic. See comment.
	 * @since   4.4 | 02 Feb 2023 | Add default value for $alt.
	 * @return $html Our version of the markup.
	 *
	 */
	public function classic_editor_add_pin_description( $html, $image_id, $caption, $title, $alignment, $url, $alt = "", $size = "" ) {
		$pinterest_description = get_post_meta( $image_id, 'swp_pinterest_description', true );

		if ( empty( $pinterest_description ) ) {
				// We only permastore the pin description when they have specifically set one for this image.
				return $html;
		}

		$width = '';
		$height = '';
		if ( is_string( $size ) ) {
				$size = $this->get_image_size( $size );
				$width = $size['width'];
				$height = $size['height'];
		}

		if ( class_exists( 'DOMDocument' ) ) {
				// DOMDocument works better with an XML delcaration.
				if ( false === strpos( $html, '?xml version' ) ) {
					$xml_statement = '<?xml version="1.0" encoding="UTF-8"?>';
					$html = $xml_statement . $html;
					$added_xml_statement = true;
				}

				// Prevent warnings for 'Invalid Tag' on HTML5 tags.
				libxml_use_internal_errors( true );
				$doc = new DOMDocument();
				$doc->loadHTML( $html );

				libxml_use_internal_errors( false );
				libxml_clear_errors();

				$img = $doc->getElementsByTagName( "img" )[0];

				$replacement = $img->cloneNode();
				$pinterest_description = addslashes( SWP_Pinterest::trim_pinterest_description( $pinterest_description ) );
				$replacement->setAttribute( "data-pin-description", $pinterest_description );

				$img->parentNode->replaceChild( $replacement, $img );
				$html = $doc->saveHTML();

				if ( $added_xml_statement ) {
					$html = str_replace( $xml_statement, '', $html );
				}
		}

		else { // No DOMDocument class.
				$alignment = $this::get_alignment_style( $alignment );
				$pinterest_description = addslashes( SWP_Pinterest::trim_pinterest_description( $pinterest_description ) );

				$html = '<div class="swp-pinterest-image-wrap" ' . $alignment . '>';
					$html .= '<img ';
					$html .= ' src="' . $url . '"';
					$html .= ' width="' . $width . '"';
					$html .= ' height="' . $height . '"';
					$html .= ' class="swp-pinterest-image"';
					$html .= ' data-pin-description="' . $pinterest_description . '"';
					$html .= ' title="' . $title . '"';
					$html .= ' alt="' . $alt . '"';
					$html .= "/>";
				$html .= '</div>';
			}

			return $html;
	}

  /**
	 * Set up the content and get a DOMDocument object.
	 *
	 * @param string $content The content to parse with DOMDocument.
	 * @return object $doc The ready to use instance of DOMDocument, or false on failure.
   *
	 */
	private function prepare_content($content) {
			/**
			  * PHP Helper class for parsing strings into HTML, creating
			  * arrays of "nodes", and accessing each node as an object.
			  *
			  */
			 if ( !class_exists( 'DOMDocument' ) ) {
				 return false;
			 }

			 // Prevent warnings for 'Invalid Tag' on HTML5 tags.
			 libxml_use_internal_errors( true );
			$html = $content;
			$doc = new DOMDocument();
			 // Convert quotation marks and non-Western characters to UTF-8
			 if ( function_exists( 'mb_convert_encoding' ) ) {
				 $html = mb_convert_encoding( $content, 'HTML-ENTITIES', "UTF-8" );
			 }

			 /**
			  * DOMDocument needs a known container for editing HTML.
			  * We'll create an empty div just to load the html, then
			  * make a new $doc that mirrors the original document.
			  *
			  */
			 $doc->loadHTML("<div>$html</div>");
			 $container = $doc->getElementsByTagName('div')->item(0);
			 $container = $container->parentNode->removeChild($container);

			 // Empty out the original, possibly malformed document.
			 while ($doc->firstChild) {
				 $doc->removeChild($doc->firstChild);
			 }

			 // Repopulate with clean nodes.
			 while ($container->firstChild ) {
				 $doc->appendChild($container->firstChild);
			 }

			 return $doc;
	}


	/**
	 * Add data-pin-descriptions to all images that don't have one.
	 *
	 *
	 * Order of Precedence:
	 * 1. The user defined pinterest description for the given image.
	 * 2. The ALT text for the image.
	 * 3. The pinterest description set for the post.
	 * 4. The title and excerpt for the post.
	 *
	 * @since  3.3.2 | 12 SEP 2018 | Refined order of precedence logic
	 * @param  string $the_content String of text for the content.
	 * @return string The modified content text.
	 *
	 */
	public function content_add_pin_description( $the_content ) {
		global $post;


		$doc = $this->prepare_content($the_content);

		if ( false == $doc ) {
				return $the_content;
		}

		// Parse each image and apply a data-pin-description if it DNE yet.
		$imgs = $doc->getElementsByTagName("img");
		$post_pinterest_description = get_post_meta( $post->ID, 'swp_pinterest_description', true );

		foreach( $imgs as $img ) {
			$img = $this->update_image_pin_description( $img, $post_pinterest_description );
			$img->parentNode->replaceChild( $img->cloneNode(), $img );
		}

		$the_content = $doc->saveHTML();

		libxml_use_internal_errors( false );
		libxml_clear_errors();

		return $the_content;
	}

	/**
	 * Checks the image's current description and updates it if it has chagned
	 * based on settings or textfields.
	 *
	 * @param  object $img DOMElement for a wordpress Image.
	 * @return integer $id The image's ID, or false on failure.
	 */
	private function get_wp_image_id( $img ) {
		if ( false !== strpos( $img->getAttribute('class'), 'wp-image-' ) ) {


			/**
			 *  Gutenberg images have their ID stored in CSS class `wp-image-$ID`
			 *  Capture the parenthesized portion of the string with regex.
			 *
			 */
			preg_match( '/wp-image-(\d*)/', $img->getAttribute('class'), $matches );
			if ( isset($matches[1] ) ) {
				return $matches[1];
			}
		}

		// Else try to get an classic Image from the src/guid.
		$src = $img->getAttribute( 'src' );


		/**
		 * This check is added for backwards compatibility purposes. Since this
		 * function was only recently added to core, this will stop it from
		 * throwing any errors if they are on an outdated version of core, and it
		 * will instead gracefully fail by just not doing anything.
		 *
		 */
		if( !method_exists( 'SWP_Utility', 'get_image_id_by_url' ) ) {
			return false;
		}

		$image_id = SWP_Utility::get_image_id_by_url( $src );
		if ( is_numeric( $image_id ) ) {
			return $image_id;
		}

		return false;
	}

	/**
	 * Checks the image's current description and updates it if it has chagned
	 * based on settings or textfields.
	 *
	 * @param  object $img DOMElement for a wordpress Image.
	 * @return object $img DOMElement for a wordpress Image.
	 */
	private function update_image_pin_description( $img, $default_description ) {
		$image_pinterest_description = '';
		$image_id = 0;
		$use_alt_text = ('alt_text' == SWP_Utility::get_option( 'pinit_image_description' ));

		$image_id = $this->get_wp_image_id( $img );

		if ( $image_id ) {
			$image_pinterest_description = get_post_meta( $image_id, 'swp_pinterest_description', true ) ;
		}


		// Let images update their pinterest description.
		if ( $img->hasAttribute("data-pin-description" ) ) {
			$prev_description = $img->getAttribute( "data-pin-description" );

			if ( $use_alt_text && $img->getAttribute( 'alt' ) != $prev_description ) {
				$img->removeAttribute( "data-pin-description" );
			}

			if ( !$use_alt_text && $img->getAttribute( 'alt' ) == $prev_description ) {
				$img->removeAttribute( "data-pin-description" );
			}

		   if ( $image_pinterest_description ) {
			   // they may have added an image description since the post description.
			   if ( $prev_description != $image_pinterest_description || $prev_description != $default_description )  {
				   $img->removeAttribute( 'data-pin-description' );
			   }
		   }

			// The description it had was good, let it be.
			if ( $img->hasAttribute("data-pin-description") ) {
				return $img;
			}
		}


		// The description it already has is a keeper.
		if ( !$use_alt_text && $img->hasAttribute( "data-pin-description" ) ) {
			 return $img;
		}

		// Apply the Image's swp_pinterest_description.
		if ( empty( $pinterest_description ) && $image_pinterest_description  ) {
			$pinterest_description = $image_pinterest_description;
		}

		// Apply the Image's alt text.
		$alt = $img->getAttribute( 'alt' );
		if ( $use_alt_text && !empty( $alt ) ) {
			$pinterest_description = $alt;
		}

		// Apply the Post's swp_pinterest_description.
		if ( empty( $pinterest_description ) && !empty( $default_description ) ) {
			$pinterest_description = $default_description;
		}

		// Generate a description from the post title and permalink.
		if ( empty( $pinterest_description ) )  {
			// Use the post title and excerpt.
			$title = get_the_title();
			$permalink = get_permalink();

			if ( false === $permalink ) {
				 $permalink = '';
			}

			$pinterest_description = $title . ' ' . $permalink;
		}

		$pinterest_description = SWP_Pinterest::trim_pinterest_description( $pinterest_description );
		$img->setAttribute( "data-pin-description", ( $pinterest_description ) );

		return $img;
	}

	/**
	 * Adds the 'no-pin' CSS class to an image for opted-out images.
	 *
	 * @param  string $the_content The post content, passsed by WordPress
	 * @return string $the_content The filtered content, with or without classnames.
	 *
	 */
	public function content_maybe_add_no_pin( $the_content ) {
		global $post;

		if ( !class_exists( 'DOMDocument') ) {
			return $the_content;
		}

		// This will only return images that are "attached" (first published)
		// to this $post.
		$images = get_attached_media( 'image' );

		// Filter images to only include those that opted out of Pin Hover.
		$opt_out_images = array_filter($images, function($image) {
			return true == get_post_meta( $image->ID, 'swp_pin_button_opt_out', true );
		});

		$doc = $this->prepare_content( $the_content );
		$dom_images = $doc->getElementsByTagName( "img" );

		// Replace existing nodes with updated 'no-pin' notes.
		foreach( $dom_images as $img ) {
			$src = $img->getAttribute('src');
			$image_id = $this->get_wp_image_id( $img );
			$class = '';

			if ( $image_id ) {
				// Gutenberg makes IDs easier to get.
				$nopin = get_post_meta( $image_id, 'swp_pin_button_opt_out', true );
				if ($nopin) {
					$class = $img->getAttribute('class');
					$class .= ' no-pin ';
				}
			}
			else {
				// Use the known opt out images as a blacklist.
				foreach( $opt_out_images as $i ) {
					$href = wp_get_attachment_url( $i->ID );
					$guid = $i->guid;
					if ( $href == $src || $guid == $src ) {
						$class = $img->getAttribute('class');
						$class .= ' no-pin ';
					}
				}
			}

			if ( false !== strpos( $class, 'no-pin' ) ) {
				$img->setAttribute('class', $class );
				$image = $img->cloneNode();
				$img->parentNode->replaceChild( $image, $img );
			}
		}

		$the_content = $doc->saveHTML();

		return $the_content;
	}

	/**
	 * Create the [pinterest_image] shortcode.
	 *
	 * @since  3.2.0 | 25 JUL 2018 | Created
	 * @param  array $atts  Shortcode parameters
	 * @return string       The rendered HTML for a Pinterest image.
	 *
	 */
	public function pinterest_image_shortcode( $atts ) {
		global $post;

		/**
		 * This is a list of acceptable arguments that can be passed into
		 * the shortcode allowing the user some level of control as to how
		 * the Pinterest image will be displayed on output. Anything outside
		 * of this list will be filtered out of the passed arguments.
		 *
		 */
		$whitelist = ['id', 'width', 'height', 'class', 'alignment'];
		foreach( $whitelist as $var ) {
			$$var = isset( $atts[$var] ) ? sanitize_text_field( trim ( $atts[$var] ) ) : "";
		}

		/**
		 * If an ID was not passed in as an argument, but we have a valid
		 * $post object, we can use that to find the Pinterest image for
		 * the post in which the shortcode is being used.
		 *
		 */
		if ( empty( $id ) && is_object( $post ) ) {
			$id = get_post_meta( $post->ID, 'swp_pinterest_image', true);
			$src = get_post_meta( $post->ID, 'swp_pinterest_image_url', true );
		}

		/**
		 * If we were not able to find a specific Pinterest image for this
		 * post then we will attempt to see if the post has a featured
		 * image, but we will only do this if the user has set the "featured
		 * image" to be used as the fallback on the options page.
		 *
		 */
		if ( empty( $id ) && 'featured' == SWP_Utility::get_option( 'pinterest_fallback' ) ) {
			$id = get_post_thumbnail_id();
			$src = wp_get_attachment_image_src( $id, 'full' )[0];
		}

		/**
		 * No ID was provided by shortcode attribute OR by setting a
		 * pinterest image in the meta box, OR by looking for a featured
		 * image.
		 *
		 */
		if ( empty( $id ) ) {
			return;
		}

		$image = get_post( $id );
		$pinterest_description = SWP_Pro_Pinterest::get_pin_description( $id );
		// If the user provided width & height attributes.
		if ( !empty( $width ) && !empty( $height ) ) {
			$dimensions = ' width="' . $width . '"';
			$dimensions .= ' height="' . $height . '"';
		} else {
			$dimensions = "";
		}
		// Instantiate a default class regardless of user input.

		if ( empty( $class ) ) {
			$class = "swp-pinterest-image";
		} else {
			$class .= " swp-pinterest-image ";
		}

		// Parse the alignment from user input to inline style declaration.
		$alignment = SWP_Pro_Pinterest::get_alignment_style( $alignment );

		// Display a Pinterest 'Save' button on hover?
		$pin_opt_out = get_post_meta( $image->ID, 'swp_pin_button_opt_out', true );
		$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );

		if ( empty( $alt_text ) ) {
			$alt_text = $pinterest_description;
		}

		if ( true == (bool) $pin_opt_out ) {
			$class .= ' no-pin ';
		}

		$html = '<div class="swp-pinterest-image-wrap" ' . $alignment . '>';
			$html .= '<img src="' . $src . '"';
			$html .= ' alt="' . $alt_text . '"';
			$html .= $alignment;
			$html .= $dimensions;
			$html .= ' class="' . $class . '"';
			$html .= ' data-pin-description="' . $pinterest_description . '"';
			$html .= ' />';
		$html .= '</div>';


		return $html;
	}


	/**
	 * These methods are borrowed from https://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
	 *
	 */

	 /**
		* Get size information for a specific image size.
		*
		* @uses   get_image_sizes()
		* @param  string $size The image size for which to retrieve data.
		* @return bool|array $size Size data about an image size or false if the size doesn't exist.
		*/
	 private function get_image_size( $size ) {
		 $sizes = $this->get_image_sizes();

		 if ( isset( $sizes[ $size ] ) ) {
			 return $sizes[ $size ];
		 }

		 // Return a dummy array of [$width, $height]
		 return array("", "");
	 }


	/**
	 * Get size information for all currently-registered image sizes.
	 *
	 * @global $_wp_additional_image_sizes
	 * @uses   get_intermediate_image_sizes()
	 * @return array $sizes Data for all currently-registered image sizes.
	 *
	 */
	private function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}


	/**
	 * Get the width of a specific image size.
	 *
	 * @uses   get_image_size()
	 * @param  string $size The image size for which to retrieve data.
	 * @return bool|string $size Width of an image size or false if the size doesn't exist.
	 *
	 */
	private function get_image_width( $size ) {
		if ( ! $size = $this->get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['width'] ) ) {
			return $size['width'];
		}

		return false;
	}

	/**
	 * Get the height of a specific image size.
	 *
	 * @uses   get_image_size()
	 * @param  string $size The image size for which to retrieve data.
	 * @return bool|string $size Height of an image size or false if the size doesn't exist.
	 */
	private function get_image_height( $size ) {
		if ( ! $size = $this->get_image_size( $size ) ) {
			return false;
		}

		if ( isset( $size['height'] ) ) {
			return $size['height'];
		}

		return false;
	}

	/**
	 * A function to output the Pin Button option controls
	 *
	 * @since  2.1.4
	 * @since  3.3.0 | 21 AUG 2018 | Moved from main file into SWP_Pro_Pinterest.
	 * @param  array $info An array of footer script information.
	 * @return array $info A modified array of footer script information.
	 *
	 */
	public function pinit_controls_output( $info ) {
		$custom_pin_description = get_post_meta( get_the_ID(), 'swp_pinterest_description', true );
		$custom_pinterest_image = get_post_meta( get_the_ID(), 'swp_pinterest_image_url', true );

		$pin_vars = array(
			'enabled' => false,
		);

		if ( SWP_Utility::get_option( 'pinit_toggle' ) ) {
			$pin_vars['post_title']= '';
			$pin_vars['image_description'] = '';
			$pin_vars['image_source'] = '';
			$pin_vars['enabled']   = true;
			$pin_vars['hLocation'] = SWP_Utility::get_option( 'pinit_location_horizontal' );
			$pin_vars['vLocation'] = SWP_Utility::get_option( 'pinit_location_vertical' );
			$pin_vars['minWidth']  = str_replace( 'px', '', SWP_Utility::get_option( 'pinit_min_width' ) );
			$pin_vars['minHeight'] = str_replace( 'px', '', SWP_Utility::get_option( 'pinit_min_height' ) );
			$pin_vars['disableOnAnchors'] = SWP_Utility::get_option( 'pinit_hide_on_anchors' );

			$pinit_button_size = SWP_Utility::get_option('pinit_button_size');
			if( $pinit_button_size === false ) {
				$pinit_button_size = '1';
			}
			$pin_vars['button_size'] = $pinit_button_size;

			// Set the image source
			if ( 'custom' == SWP_Utility::get_option( 'pinit_image_source' ) && $custom_pinterest_image ) {
				$pin_vars['image_source'] = $custom_pinterest_image;
			}

			// Set the description Source
			if( 'custom' == SWP_Utility::get_option( 'pinit_image_description' ) && $custom_pin_description ) {
				$pin_vars['image_description'] = $custom_pin_description;
			}

			global $post;
			if ( is_singular() && is_object( $post ) ) {
				$pin_vars['post_title'] = $post->post_title;
			}
		}

		$info['footer_output'] .= ' swpPinIt=' . json_encode($pin_vars) . ';';

		return $info;
	}


	/**
	 * Adds the Pinterest Description custom field when editing an image.
	 *
	 * @since  3.2.0 | 07 AUG 2018 | Creatd
	 *
	 * @param  array $form_fields The other fields present in the media editor.
	 * @param  object $post The WP Attachment object.
	 *
	 * @return array $form_fields The filtered form fields, now including our box.
	 *
	 */
	public function edit_media_custom_field( $form_fields, $post ) {
		$form_fields['swp_pinterest_description'] = array(
			'label' => 'Social Warfare Pin Description',
			'input' => 'textarea',
			'value' => get_post_meta( $post->ID, 'swp_pinterest_description', true )
		);

		if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) {
			$bool = get_post_meta( $post->ID, 'swp_pin_button_opt_out', true );
			$checked = (int) $bool === 1 ? 'checked' : '';

			$form_fields['swp_pin_button_opt_out'] = array(
				'label' => 'Hover Pin Opt Out',
				'input' => 'html',
				'html'  => '<input
								 type="checkbox"
								 id="attachments-' . $post->ID . '-swp_pin_button_opt_out"
								 name="attachments[' . $post->ID . '][swp_pin_button_opt_out]"
								 ' . $checked . '
							 />',
			);
		}

		return $form_fields;
	}


	/**
	 * Adds the Pinterest Description custom field when editing an image.
	 *
	 * @since  3.2.0 | 07 AUG 2018 | Creatd
	 * @param  object $post The WP Attachment object.
	 * @param  array  $attachment $key => $value data about $post.
	 * @return array $post The updated post object.
	 *
	 */
	public function save_media_custom_field( $post, $attachment ) {
		$pin_description = str_replace( '"', "'", $attachment['swp_pinterest_description'] );
		update_post_meta( $post['ID'], 'swp_pinterest_description', $pin_description );

		if ( true === SWP_Utility::get_option( 'pinit_toggle' ) ) {
			$checked = isset( $attachment['swp_pin_button_opt_out'] );
			update_post_meta( $post['ID'], 'swp_pin_button_opt_out', $checked );
		}

		return $post;
	}
}
