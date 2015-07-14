<?php

class Prompt_Post_Mailing {

	/**
	 * Send email notifications for a post.
	 *
	 * Sends up to 25 unsent notifications, and schedules another batch if there are more.
	 *
	 * @param WP_Post|int $post
	 * @param string $signature Optional identifier for this batch
	 * @param int $retry_wait_seconds Minimum time to wait if a retry is necessary, or null to disable retry
	 */
	public static function send_notifications( $post, $signature = '', $retry_wait_seconds = 60 ) {

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

		// We will attempt to notify these IDs - setting sent early could help lock other processes out
		$prompt_post->add_sent_recipient_ids( $chunk_ids );

		$prompt_site = new Prompt_Site();
		$prompt_author = new Prompt_User( get_userdata( $post->post_author ) );

		$rendering_context = new Prompt_Post_Rendering_Context( $post );
		$is_api_delivery = ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) );
		$will_strip_content = ( !$is_api_delivery and $rendering_context->has_fancy_content() );

		$rendering_context->setup();

		$excerpt_only = Prompt_Admin_Delivery_Metabox::excerpt_only( $post->ID );

		$emails = array();
		foreach ( $chunk_ids as $user_id ) {
			$user = get_userdata( $user_id );

			if ( !is_email( $user->user_email ) )
				continue;

			$unsubscribe_link = new Prompt_Unsubscribe_Link( $user );

			$template_data = array(
				'prompt_author' => $prompt_author,
				'recipient' => $user,
				'prompt_post' => $prompt_post,
				'subscribed_object' => $prompt_author->is_subscribed( $user_id ) ? $prompt_author : $prompt_site,
				'featured_image_src' => $rendering_context->get_the_featured_image_src(),
				'excerpt_only' => $excerpt_only,
				'the_text_content' => $rendering_context->get_the_text_content(),
				'subject' => html_entity_decode( $prompt_post->get_wp_post()->post_title, ENT_QUOTES ),
				'unsubscribe_url' => $unsubscribe_link->url(),
				'alternate_versions_menu' => $rendering_context->alternate_versions_menu(),
				'is_api_delivery' => $is_api_delivery,
				'will_strip_content' => $will_strip_content,
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
			 *      @type bool $excerpt_only whether to include only the post excerpt
			 *      @type string $the_text_content
			 *      @type string $subject
			 *      @type string $unsubscribe_url
			 *      @type bool $is_api_delivery
			 *      @type bool $will_strip_content
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

		$rendering_context->reset();

		$result = Prompt_Factory::make_mailer()->send_many( $emails );

		$rescheduler = new Prompt_Rescheduler( $result, $retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {
			$prompt_post->remove_sent_recipient_ids( $chunk_ids );
			$rescheduler->reschedule( 'prompt/post_mailing/send_notifications', array( $post->ID, $signature ) );
			return;
		}

		if ( is_wp_error( $result ) ) {
			$prompt_post->remove_sent_recipient_ids( $chunk_ids );
			Prompt_Logging::add_error(
				Prompt_Enum_Error_Codes::OUTBOUND,
				__( 'An email sending operation encountered a problem.', 'Postmatic' ),
				compact( 'result' )
			);
			return;
		}

		if ( !empty( $chunks[1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/post_mailing/send_notifications',
				array( $post->ID, implode( '', $chunks[1] ) )
			);

		}

	}

	/**
	 * Build a single post email
	 * @param array $template_data see prompt/post_email/template_data
	 * @return Prompt_Email the fully rendered email
	 */
	public static function build_email( $template_data ) {

		/** @var Prompt_Interface_Subscribable $subscribed_object */
		/** @var Prompt_Post $prompt_post */
		/** @var Prompt_User $prompt_author */
		/** @var WP_User $recipient */
		/** @var string $subject */
		/** @var bool $excerpt_only */
		extract( $template_data );

		$html_template = new Prompt_Email_Template( "new-post-email.php" );
		$text_template = new Prompt_Text_Email_Template( "new-post-email-text.php" );

		$from_name = get_option( 'blogname' );
		if ( is_a( $subscribed_object, 'Prompt_User' ) and $prompt_author->id() )
			$from_name .= ' [' . $prompt_author->get_wp_user()->display_name . ']';

		$email = new Prompt_Email( array(
			'to_address' => $recipient->user_email,
			'subject' => $subject,
			'from_name' => $from_name,
			'text' => $text_template->render( $template_data ),
			'html' => $html_template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::POST,
		) );

		if ( comments_open( $prompt_post->id() ) and ! $excerpt_only ) {

			$command = new Prompt_New_Post_Comment_Command();
			$command->set_post_id( $prompt_post->id() );
			$command->set_user_id( $recipient->ID );
			Prompt_Command_Handling::add_command_metadata( $command, $email );

		} else {

			$email->set_from_address( $prompt_author->get_wp_user()->user_email );

		}

		return $email;
	}

}