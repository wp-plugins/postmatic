<?php

class Prompt_Post_Mailing {

	/** @var array */
	protected static $shortcode_whitelist = array( 'gallery', 'caption', 'wpv-post-body', 'types' );

	/**
	 * Send email notifications for a post.
	 *
	 * Sends up to 25 unsent notifications, and schedules another batch if there are more.
	 *
	 * @param WP_Post|int $post
	 * @param string $signature Optional identifier for this batch.
	 */
	public static function send_notifications( $post, $signature = '' ) {

		$post = get_post( $post );

		$prompt_post = new Prompt_Post( $post );

		$recipient_ids = $prompt_post->unsent_recipient_ids();

		$chunks = array_chunk( $recipient_ids, 25 );

		if ( empty( $chunks[0] ) )
			return;

		$chunk_ids = $chunks[0];

		/**
		 * Filter whether to send new post notifications. Default true.
		 *
		 * @param boolean $send Whether to send notifications.
		 * @param WP_Post $post
		 * @param array $recipient_ids
		 */
		if ( !apply_filters( 'prompt/send_post_notifications', true, $post, $chunk_ids ) )
			return;

		// We will attempt to notifiy these IDs - setting sent early could help lock other processes out
		$prompt_post->add_sent_recipient_ids( $chunk_ids );

		$prompt_site = new Prompt_Site();
		$prompt_author = new Prompt_User( get_userdata( $post->post_author ) );

		self::setup_postdata( $post );

		$featured_image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'prompt-post-featured' );

		if ( Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post->ID ) )
			$featured_image_src = false;

		$emails = array();
		foreach ( $chunk_ids as $user_id ) {
			$user = get_userdata( $user_id );

			if ( !is_email( $user->user_email ) )
				continue;

			$template_data = array(
				'prompt_author' => $prompt_author,
				'recipient' => $user,
				'prompt_post' => $prompt_post,
				'subscribed_object' => $prompt_author->is_subscribed( $user_id ) ? $prompt_author : $prompt_site,
				'featured_image_src' => $featured_image_src,
			);
			/**
			 * Filter new post email template data.
			 *
			 * @param array $template {
			 *      @type Prompt_User $prompt_author
			 *      @type WP_User $recipient
			 *      @type Prompt_Post $prompt_post
			 *      @type Prompt_Interface_Subscribable $subscribed_object
			 *      @type array $featured_image_src url, width, height
			 * }
			 */
			$template_data = apply_filters( 'prompt/post_email/template_data', $template_data );

			$email = self::build_email( $template_data );

			/**
			 * Filter new post email.
			 *
			 * @param Prompt_Email $email
			 * @param array $template see prompt/post_email/template_data
			 * }
			 */
			$emails[] = apply_filters( 'prompt/post_email', $email, $template_data );
		}

		self::reset_postdata();

		$result = Prompt_Factory::make_mailer()->send_many( $emails );

		if ( is_wp_error( $result ) )
			self::send_error_notifications( $post, $result );

		if ( !empty( $chunks[1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/post_mailing/send_notifications',
				array( $post->ID, implode( '', $chunks[1] ) )
			);

		}

	}

	/**
	 * Set up the global environment needed to render a post email.
	 * @var WP_Post $post
	 */
	public static function setup_postdata( $post ) {

		query_posts( array( 'p' => $post->ID, 'post_type' => $post->post_type, 'post_status' => $post->post_status ) );

		the_post();

		remove_filter( 'the_content', 'do_shortcode', 11 );
		remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
		add_filter( 'the_content', array( __CLASS__, 'do_whitelisted_shortcodes' ), 11 );
		add_filter( 'the_content', array( __CLASS__, 'strip_image_height_attributes' ), 11 );
		add_filter( 'the_content', array( __CLASS__, 'strip_incompatible_tags' ), 11 );
		add_filter( 'oembed_dataparse', array( __CLASS__, 'use_original_oembed_url' ), 10, 3 );

	}

	/**
	 * Reset the global environment after rendering post emails.
	 */
	public static function reset_postdata() {

		wp_reset_query();

		remove_filter( 'oembed_dataparse', array( __CLASS__, 'use_original_oembed_url' ), 10, 3 );
		remove_filter( 'the_content', array( __CLASS__, 'strip_incompatible_tags' ), 11 );
		remove_filter( 'the_content', array( __CLASS__, 'strip_image_height_attributes' ), 11 );
		remove_filter( 'the_content', array( __CLASS__, 'do_whitelisted_shortcodes' ), 11 );
		add_filter( 'the_content', 'do_shortcode', 11 );
		add_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );

	}

	/**
	 * Build a single post email
	 * @param array $template_data see prompt/post_email/template_data
	 * @return Prompt_Email the fully rendered email
	 */
	public static function build_email( $template_data ) {

		$template = Prompt_Template::locate( "new-post-email.php" );

		$from_name = get_option( 'blogname' );
		if ( is_a( $template_data['subscribed_object'], 'Prompt_User' ) and $template_data['prompt_author']->id() )
			$from_name .= ' [' . $template_data['prompt_author']->get_wp_user()->display_name . ']';

		$email = new Prompt_Email( array(
			'to_address' => $template_data['recipient']->user_email,
			'subject' => $template_data['prompt_post']->get_wp_post()->post_title,
			'from_name' => $from_name,
			'message' => Prompt_Template::render( $template, $template_data, false ),
		) );

		if ( comments_open( $template_data['prompt_post']->id() ) ) {

			$command = new Prompt_New_Post_Comment_Command();
			$command->set_post_id( $template_data['prompt_post']->id() );
			$command->set_user_id( $template_data['recipient']->ID );
			Prompt_Command_Handling::add_command_metadata( $command, $email );

		} else {

			$email->set_from_address( $template_data['prompt_author']->get_wp_user()->user_email );

		}

		return $email;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function strip_image_height_attributes( $content ) {
		return preg_replace( '/(<img[^>]*?) height=["\']\d*["\']([^>]*?>)/', '$1$2', $content );
	}

	public static function strip_incompatible_tags( $content ) {

		if ( false === strpos( $content, '<iframe' ) and false === strpos( $content, '<object' ) )
			return $content;

		$content = preg_replace_callback(
			'#<(iframe|object)([^>]*)(src|data)=[\'"]([^\'"]*)[\'"][^>]*>.*?<\\/\\1>#',
			array( __CLASS__, 'strip_incompatible_tag' ),
			$content
		);

		return $content;
	}

	public static function strip_incompatible_tag( $m ) {
		$class = $m[1];

		$url_parts = parse_url( $m[4] );

		$url = null;
		if ( $url_parts and isset( $url_parts['host'] ) ) {
			$class = 'embed ' . str_replace( '.', '-', $url_parts['host'] );
			$url = $m[4];
		}

		return self::incompatible_placeholder( $class, $url );
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function do_whitelisted_shortcodes( $content ) {
		global $shortcode_tags;

		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}

		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;

		add_filter( 'shortcode_atts_gallery', array( __CLASS__, 'override_gallery_attributes' ), 10, 3 );

		$pattern = get_shortcode_regex();
		$content = preg_replace_callback( "/$pattern/s", array( __CLASS__, 'do_whitelisted_shortcode_tag' ), $content );

		remove_filter( 'shortcode_atts_gallery', array( __CLASS__, 'override_gallery_attributes' ), 10, 3 );

		return $content;
	}

	/**
	 * @param array $m
	 * @return string
	 */
	public static function do_whitelisted_shortcode_tag( $m ) {

		$tag = $m[2];

		if ( 'wpgist' == $tag )
			return self::override_wp_gist_shortcode_tag( $m );

		if ( in_array( $tag, self::$shortcode_whitelist ) )
			return do_shortcode_tag( $m );

		return $m[1] . self::incompatible_placeholder( $tag ) . $m[6];
	}

	/**
	 * Use the old HTML 4 default gallery tags for better email (gmail) client support.
	 *
	 * @param array $out
	 * @param array $pairs
	 * @param array $atts
	 * @return array Overriden attributes.
	 */
	public static function override_gallery_attributes( $out, $pairs, $atts ) {
		$out['itemtag'] = 'dl';
		$out['icontag'] = 'dt';
		$out['captiontag'] = 'dd';
		return $out;
	}

	/**
	 * Replace constructed provider URL with the original for placeholders.
	 *
	 * @see oembed_dataparse WordPress filter
	 *
	 * @param $return
	 * @param $data
	 * @param $url
	 * @return mixed
	 */
	public static function use_original_oembed_url( $return, $data, $url ) {
		$match_pattern = preg_replace( '#^https?#', 'https?', $data->provider_url );
		return preg_replace( '#' . $match_pattern . '[^"]*#', $url, $return );
	}

	protected static function override_wp_gist_shortcode_tag( $m ) {
		$defaults = array( 'file' => '', 'id' => '', 'url' => '' );

		$atts = shortcode_atts( $defaults, shortcode_parse_atts( $m[3] ) );

		if ( empty( $atts['id'] ) and empty( $atts['url'] ) )
			return '';

		if ( empty( $atts['id'] ) ) {
			$url_parts = parse_url( $atts['url'] );
			$atts['id'] = basename( $url_parts['path'] );
		}

		$api_url = 'https://api.github.com/gists/' . $atts['id'];

		$response = wp_remote_get( $api_url );
		$json = wp_remote_retrieve_body( $response );

		if ( !$json )
			return '';

		$gist = json_decode( $json, $associative_arrays = true );
		$files = $gist['files'];

		if ( empty( $atts['file'] ) or empty( $files[$atts['file'] ] ) ) {
			$file_keys = array_keys( $files );
			$atts['file'] = $file_keys[0];
		}

		$content = $files[$atts['file']]['content'];

		return html( 'pre class="wp-gist"', esc_html( $content ) );
	}

	protected static function incompatible_placeholder( $class = '', $url = null ) {
		$class = 'incompatible' . ( $class ? ' ' . $class : '' );
		$url = $url ? $url : get_permalink();
		return html( 'div',
			array( 'class' => $class ),
			__( 'This content is not compatible with your email client. ', 'Postmatic' ),
			html( 'a',
				array( 'href' => $url ),
			__( 'Click here to view this content in your browser.', 'Postmatic' )
			)
		);
	}
	protected static function send_error_notifications( $post, $error ) {

		$recipient_id = get_current_user_id() ? get_current_user_id() : $post->post_author;

		$recipient = get_userdata( $recipient_id );

		if ( !$recipient or empty( $recipient->user_email ) )
			return;

		$email = new Prompt_Email();
		$email->set_to_address( $recipient->user_email );
		$email->set_from_address( $recipient->display_name );
		$email->set_subject( sprintf( __( 'Delivery issue for %s', 'Postmatic' ), get_option( 'blogname' ) ) );
		$email->set_message(
			sprintf(
				__( 'Delivery of subscription notifications for the post "%s" may have failed.', 'Postmatic' ),
				get_the_title( $post )
			) .
			' ' .
			__( 'A site administrator can report this event to the development team from the Postmatic settings.', 'Postmatic' ) .
			' ' .
			__( 'The error message was: ', 'Postmatic' ) . $error->get_error_message()
		);

		$email->set_content_type( Prompt_Enum_Content_Types::TEXT );
		$email->set_template( '' );

		Prompt_Factory::make_mailer( Prompt_Enum_Email_Transports::LOCAL )->send_one( $email );

	}

}