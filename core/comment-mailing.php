<?php

class Prompt_Comment_Mailing {
	protected static $sent_meta_key = 'prompt_sent_ids';

	/**
	 * Send notifications appropriate for a newly published comment.
	 *
	 * Top level comments go to all post subscribers, replies optionally to the replyee.
	 *
	 * @param object|int $comment_id_or_object
	 * @param int $chunk
	 */
	public static function send_notifications( $comment_id_or_object, $chunk = 0 ) {

		$comment = get_comment( $comment_id_or_object );

		self::handle_new_subscriber( $comment );

		self::send_post_subscriber_notifications( $comment, $chunk );
	}

	/**
	 * Send post subscribers a new comment notification.
	 *
	 * @param object $comment
	 * @param int $chunk
	 */
	protected static function send_post_subscriber_notifications( $comment, $chunk = 0 ) {

		$prompt_post = new Prompt_Post( $comment->comment_post_ID );

		$recipient_ids = $prompt_post->subscriber_ids();
		$sent_ids = self::sent_recipient_ids( $comment );
		$unsent_ids = array_diff( $recipient_ids, $sent_ids );

		$chunks = array_chunk( $unsent_ids, 25 );

		if ( empty( $chunks[$chunk] ) )
			return;

		$chunk_ids = $chunks[$chunk];

		/**
		 * Filter whether to send new comment notifications.
		 *
		 * @param boolean $send Default true.
		 * @param object $comment
		 * @param array $recipient_ids
		 */
		if ( !apply_filters( 'prompt/send_comment_notifications', true, $comment, $chunk_ids ) )
			return;

		// We will attempt to notifiy these IDs - setting sent early could help lock other processes out
		self::add_sent_recipient_ids( $comment, $chunk_ids );

		// Turn off native comment notifications
		add_filter( 'pre_option_comments_notify', create_function( '$a', 'return null;' ) );

		$previous_comments = self::get_previous_comments( $comment );

		$comment_author = get_userdata( $comment->user_id );
		$from_name = $comment_author ? $comment_author->display_name : $comment->comment_author;

		$parent_comment = $parent_author = null;
		$template_file = 'new-comment-email.php';

		if ( $comment->comment_parent ) {
			$parent_comment = get_comment( $comment->comment_parent );
			$parent_author = get_userdata( $parent_comment->user_id );
			$template_file = 'comment-reply-email.php';
		}

		$emails = array();
		foreach ( $chunk_ids as $subscriber_id ) {

			if ( $subscriber_id == $comment->user_id )
				continue;

			$subscriber = get_userdata( $subscriber_id );
			if ( !$subscriber )
				continue;

			$template_data = array(
				'comment_author' => $comment_author,
				'subscriber' => $subscriber,
				'comment' => $comment,
				'subscribed_post' => $prompt_post,
				'previous_comments' => $previous_comments,
				'parent_author' => $parent_author,
				'parent_comment' => $parent_comment,
			);
			/**
			 * Filter comment email template data.
			 *
			 * @param array $template_data {
			 * @type WP_User $comment_author
			 * @type WP_User $subscriber
			 * @type object $comment
			 * @type Prompt_post $subscribed_post
			 * }
			 */
			$template_data = apply_filters( 'prompt/comment_email/template_data', $template_data );

			$subject = sprintf( __( 'New reply to "%s"', 'Postmatic' ), $prompt_post->get_wp_post()->post_title );

			$template = Prompt_Template::locate( $template_file );

			$email = new Prompt_Email( array(
				'to_address' => $subscriber->user_email,
				'from_name' => $from_name,
				'subject' => $subject,
				'message' => Prompt_Template::render( $template, $template_data, false ),
			) );

			$command = new Prompt_Comment_Command();
			$command->set_post_id( $prompt_post->id() );
			$command->set_user_id( $subscriber_id );
			$command->set_parent_comment_id( $comment->comment_ID );

			Prompt_Command_Handling::add_command_metadata( $command, $email );

			/**
			 * Filter comment notification email.
			 *
			 * @param Prompt_Email $email
			 * @param array $template_data see prompt/comment_email/template_data
			 */
			$emails[] = apply_filters( 'prompt/comment_email', $email, $template_data );
		}

		if ( empty( $emails ) )
			return;

		Prompt_Factory::make_mailer()->send_many( $emails );

		if ( !empty( $chunks[$chunk + 1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/comment_mailing/send_notifications',
				array( $prompt_post->id(), $chunk + 1 )
			);

		}

	}

	/**
	 * Send a comment a notification when their comment is rejected.
	 *
	 * This could be due to a deleted post, change in post status, or comments being closed.
	 *
	 * @param $user_id
	 * @param $post_id
	 */
	public static function send_rejected_notification( $user_id, $post_id ) {

		$comment_author = get_userdata( $user_id );
		$post = get_post( $post_id );
		$post_title = $post ? $post->post_title : __( 'a deleted post', 'Postmatic' );

		$template_data = compact( 'comment_author', 'post', 'post_title' );
		/**
		 * Filter comment rejected email template data.
		 *
		 * @param array $template_data {
		 *      @type WP_User $comment_author
		 *      @type WP_Post $post
		 *      @type string $post_title Post title or placeholder if post no longer exists
		 * }
		 */
		$template_data = apply_filters( 'prompt/comment_rejected_email/template_data', $template_data );

		$subject = sprintf( __( 'Unable to publish your reply to "%s"', 'Postmatic' ), $post_title );
		$template = Prompt_Template::locate( 'comment-rejected-email.php' );
		$email = new Prompt_Email( array(
			'to_address' => $comment_author->user_email,
			'subject' => $subject,
			'message' => Prompt_Template::render( $template, $template_data, false ),
		) );

		/**
		 * Filter comment rejected email.
		 *
		 * @param Prompt_Email $email
		 * @param array $template_data see prompt/comment_reject_email/template_data
		 */
		$email = apply_filters( 'prompt/comment_rejected_email', $email, $template_data );

		$mailer = Prompt_Factory::make_mailer();
		$mailer->send_one( $email );
	}

	protected static function sent_recipient_ids( $comment ) {
		$sent_ids = get_comment_meta( $comment->comment_ID, self::$sent_meta_key, true );
		if ( !$sent_ids )
			$sent_ids = array();

		return $sent_ids;
	}

	/**
	 * Add the IDs of users who have been sent an email notification for this comment.
	 * @param object $comment
	 * @param array $ids
	 */
	protected static function add_sent_recipient_ids( $comment, $ids ) {
		$sent_ids = array_unique( array_merge( self::sent_recipient_ids( $comment ), $ids ) );
		update_comment_meta( $comment->comment_ID, self::$sent_meta_key, $sent_ids );
	}

	/**
	 * Handle the situation when a moderated comment subscribe request has not yet been fulfilled.
	 * @param $comment
	 */
	protected static function handle_new_subscriber( $comment ) {

		if ( ! Prompt_Comment_Form_Handling::subscription_requested( $comment ) )
			return;

		Prompt_Comment_Form_Handling::subscribe_commenter( $comment );
	}

	/**
	 * Get previous approved comments, including the given one.
	 *
	 * If the comment is a reply, gets ancestor comments.
	 *
	 * If the comment is top level, gets previous top level comments.
	 *
	 * Adds an 'excerpt' property with a 100 word text excerpt.
	 *
	 * @param object $comment
	 * @param int $number
	 * @return array
	 */
	protected static function get_previous_comments( $comment, $number = 3 ) {

		if ( $comment->comment_parent )
			return array();

		$comments = self::get_previous_top_level_comments( $comment, $number );

		foreach ( $comments as $comment ) {
			$comment->excerpt = self::excerpt( $comment );
		}

		return array_reverse( $comments );
	}

	/**
	 * @param object $comment
	 * @param int $number
	 * @return array
	 */
	protected static function get_previous_top_level_comments( $comment, $number = 3 ) {
		$query = array(
			'post_id' => $comment->comment_post_ID,
			'parent' => 0,
			'status' => 'approve',
			'number' => $number,
			'date_query' => array(
				array(
					'before' => $comment->comment_date,
					'inclusive' => true,
				)
			)
		);
		return get_comments( $query );
	}

	/**
	 * @param object $comment
	 * @param int $number
	 * @return array
	 */
	protected static function get_comment_thread( $comment, $number = 3 ) {

		$comments = array( $comment );

		while ( $comment->comment_parent and count( $comments ) <= $number ) {
			$comment = get_comment( $comment->comment_parent );
			$comments[] = $comment;
		}

		return $comments;
	}

	/**
	 * Make a 100 word excerpt of a comment.
	 * @param object $comment
	 * @param int $word_count
	 * @return string
	 */
	protected static function excerpt( $comment, $word_count = 100 ) {

		$comment_text = strip_tags( $comment->comment_content );

		$words = explode( ' ', $comment_text );

		$elipsis = count( $words ) > $word_count ? ' &hellip;' : '';

		return implode( ' ', array_slice( $words, 0, $word_count ) ) . $elipsis;

	}
}