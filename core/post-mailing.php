<?php

class Prompt_Post_Mailing {

	/** @var array */
	protected static $shortcode_whitelist = array( 'gallery', 'caption', 'wpv-post-body', 'types' );

	/**
	 * Send email notifications for a post.
	 * @param WP_Post|int $post
	 * @param int $chunk
	 */
	public static function send_notifications( $post, $chunk = 0 ) {

		$post = get_post( $post );

		$prompt_post = new Prompt_Post( $post );

		$recipient_ids = $prompt_post->unsent_recipient_ids();

		$chunks = array_chunk( $recipient_ids, 25 );

		if ( empty( $chunks[$chunk] ) )
			return;

		$chunk_ids = $chunks[$chunk];

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

		// Set up global post data for use in the email template
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		$featured_image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'prompt-post-featured' );

		if ( Prompt_Admin_Delivery_Metabox::suppress_featured_image( $post->ID ) )
			$featured_image_src = false;

		remove_filter( 'the_content', 'do_shortcode', 11 );
		add_filter( 'the_content', array( __CLASS__, 'do_whitelisted_shortcodes' ), 11 );

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

		wp_reset_postdata();

		remove_filter( 'the_content', array( __CLASS__, 'do_whitelisted_shortcodes' ), 11 );
		add_filter( 'the_content', 'do_shortcode', 11 );

		$result = Prompt_Factory::make_mailer()->send_many( $emails );

		if ( is_wp_error( $result ) )
			self::send_error_notifications( $post, $result );

		if ( !empty( $chunks[$chunk + 1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/post_mailing/send_notifications',
				array( $post->ID, $chunk + 1 )
			);

		}

	}

	/**
	 * Build a single post email
	 * @param array $template_data see prompt/post_email/template_data
	 * @return Prompt_Email the fully rendered email
	 */
	public static function build_email( $template_data ) {

		$template = Prompt_Template::locate( "new-post-email.php" );

		$command = new Prompt_New_Post_Comment_Command();
		$command->set_post_id( $template_data['prompt_post']->id() );
		$command->set_user_id( $template_data['recipient']->ID );

		$from_name = get_option( 'blogname' );
		if ( is_a( $template_data['subscribed_object'], 'Prompt_User' ) and $template_data['prompt_author']->id() )
			$from_name .= ' [' . $template_data['prompt_author']->get_wp_user()->display_name . ']';

		$email = new Prompt_Email( array(
			'to_address' => $template_data['recipient']->user_email,
			'subject' => $template_data['prompt_post']->get_wp_post()->post_title,
			'from_name' => $from_name,
			'message' => Prompt_Template::render( $template, $template_data, false ),
		) );

		Prompt_Command_Handling::add_command_metadata( $command, $email );

		return $email;
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

		if ( in_array( $tag, self::$shortcode_whitelist ) )
			return do_shortcode_tag( $m );

		$link = html( 'div class="incompatible"',
			__( 'This content is not compatible with your email client.', 'Postmatic' ),
			html( 'a',
				array( 'href' => get_permalink() ),
			__( 'Click here to view this post in your browser.', 'Postmatic' )
			)
		);

		return $m[1] . $link . $m[6];
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