<?php

class Prompt_Comment_Mailer {
	protected static $recipient_ids_meta_key = 'prompt_recipient_ids';
	protected static $sent_meta_key = 'prompt_sent_ids';

	/** @var  object */
	protected $comment;
	/** @var  Prompt_Comment_Flood_Controller */
	protected $flood_controller;
	/** @var  int */
	protected $retry_wait_seconds;

	public function __construct( $comment, $flood_controller = null, $retry_wait_seconds = null ) {
		$this->comment = $comment;

		$this->retry_wait_seconds = $retry_wait_seconds;

		if ( $flood_controller )
			$this->flood_controller = $flood_controller;
		else
			$this->flood_controller = new Prompt_Comment_Flood_Controller( $comment );

	}

	/**
	 * Send new comment notifications.
	 *
	 * Sends up to 25 unsent notifications, and schedules another batch if there are more.
	 */
	public function send_notifications() {

		$unsent_ids = array_diff( $this->flood_controlled_recipient_ids(), $this->sent_recipient_ids() );

		$chunks = array_chunk( $unsent_ids, 25 );

		if ( empty( $chunks[0] ) )
			return;

		$chunk_ids = $chunks[0];

		/**
		 * Filter whether to send new comment notifications.
		 *
		 * @param boolean $send Default true.
		 * @param object $comment
		 * @param array $recipient_ids
		 */
		if ( !apply_filters( 'prompt/send_comment_notifications', true, $this->comment, $chunk_ids ) )
			return;

		// We will attempt to notifiy these IDs - setting sent early could help lock other processes out
		$this->add_sent_recipient_ids( $chunk_ids );

		// Turn off native comment notifications
		add_filter( 'pre_option_comments_notify', create_function( '$a', 'return null;' ) );

		$comment_author = $this->comment_author_user();

		$is_api_delivery = ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) );

		$parent_comment = $parent_author = null;
		$parent_author_name = '';
		$template_file = 'new-comment-email.php';

		if ( $this->comment->comment_parent ) {
			$parent_comment = get_comment( $this->comment->comment_parent );
			$parent_author = get_userdata( $parent_comment->user_id );

			$parent_author_name = $parent_author ? $parent_author->display_name : $parent_comment->comment_author;
			$parent_author_name = $parent_author_name ? $parent_author_name : __( 'Anonymous', 'Postmatic' );

			$template_file = $is_api_delivery ? 'comment-reply-email.php' : $template_file;
		}

		$commenter_name = $comment_author ? $comment_author->display_name : $this->comment->comment_author;
		$commenter_name = $commenter_name ? $commenter_name : __( 'Anonymous', 'Postmatic' );

		$prompt_post = new Prompt_Post( $this->comment->comment_post_ID );
		$post_permalink_html = html( 'a',
			array( 'href' => get_permalink( $prompt_post->id() ) ),
			get_the_title( $prompt_post->id() )
		);
		$post_author = get_userdata( $prompt_post->get_wp_post()->post_author );
		$post_author_name = $post_author ? $post_author->display_name : __( 'Anonymous', 'Postmatic' );

		$emails = array();
		foreach ( $chunk_ids as $subscriber_id ) {

			$subscriber = get_userdata( $subscriber_id );

			if ( !$subscriber or !$subscriber->user_email )
				continue;

			$template_data = array(
				'comment_author' => $comment_author,
				'subscriber' => $subscriber,
				'comment' => $this->comment,
				'commenter_name' => $commenter_name,
				'subscribed_post' => $prompt_post,
				'subscribed_post_author_name' => $post_author_name,
				'subscribed_post_title_link' => $post_permalink_html,
				'previous_comments' => $this->previous_comments(),
				'parent_author' => $parent_author,
				'parent_author_name' => $parent_author_name,
				'parent_comment' => $parent_comment,
				'comment_header' => true,
				'is_api_delivery' => $is_api_delivery,
			);

			$template_data['subject'] = $this->subject( $template_data );

			/**
			 * Filter comment email template data.
			 *
			 * @param array $template_data {
			 * @type WP_User $comment_author
			 * @type WP_User $subscriber
			 * @type object $comment
			 * @type Prompt_post $subscribed_post
			 * @type string $subscribed_post_author_name
			 * @type string $subscribed_post_title_link
			 * @type array $previous_comments
			 * @type WP_User $parent_author
			 * @type string $parent_author_name
			 * @type object $parent_comment
			 * @type bool $comment_header
			 * @type string $subject
			 * @type bool $is_api_delivery
			 * }
			 */
			$template_data = apply_filters( 'prompt/comment_email/template_data', $template_data );

			$html_template = new Prompt_Email_Template( $template_file );
			$text_template = new Prompt_Text_Email_Template( str_replace( '.php', '-text.php', $template_file ) );

			$email = new Prompt_Email( array(
				'to_address' => $subscriber->user_email,
				'from_name' => $commenter_name,
				'subject' => $template_data['subject'],
				'text' => $text_template->render( $template_data ),
				'html' => $html_template->render( $template_data ),
				'message_type' => Prompt_Enum_Message_Types::COMMENT,
			) );

			$command = new Prompt_Comment_Command();
			$command->set_post_id( $prompt_post->id() );
			$command->set_user_id( $subscriber_id );
			$command->set_parent_comment_id( $this->comment->comment_ID );

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

		$response = Prompt_Factory::make_mailer()->send_many( $emails );

		$rescheduler = new Prompt_Rescheduler( $response, $this->retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {

			$this->remove_sent_recipient_ids( $chunk_ids );

			$rescheduler->reschedule(
				'prompt/comment_mailing/send_notifications',
				array( $this->comment->comment_ID, implode( '', $chunk_ids ), null )
			);

			return;
		}

		if ( is_wp_error( $response ) ) {

			$this->remove_sent_recipient_ids( $chunk_ids );

			Prompt_Logging::add_error(
				Prompt_Enum_Error_Codes::OUTBOUND,
				__( 'An email sending operation encountered a problem.', 'Postmatic' ),
				compact( 'response' )
			);

			return;
		}

		if ( !empty( $chunks[1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/comment_mailing/send_notifications',
				array( $this->comment->comment_ID, implode( '', $chunks[1] ) )
			);

		}

	}

	/**
	 * Find recipients after flood control.
	 *
	 * @return array IDs of users who should receive a comment notification
	 */
	protected function flood_controlled_recipient_ids() {

		// We currently only mail standard WP comments
		if ( !empty( $this->comment->comment_type ) )
			return array();

		$recipient_ids = get_comment_meta( $this->comment->comment_ID, self::$recipient_ids_meta_key, true );

		if ( ! $recipient_ids ) {

			$recipient_ids = $this->flood_controller->control_recipient_ids();
			/**
			 * Filter the recipient ids of notifications for a comment.
			 *
			 * @param array $recipient_ids
			 * @param WP_Post $post
			 */
			$recipient_ids = apply_filters( 'prompt/recipient_ids/comment', $recipient_ids, $this->comment );

			update_comment_meta( $this->comment->comment_ID, self::$recipient_ids_meta_key, $recipient_ids );

		}

		return $recipient_ids;
	}

	/**
	 * @return array
	 */
	protected function sent_recipient_ids() {
		$sent_ids = get_comment_meta( $this->comment->comment_ID, self::$sent_meta_key, true );
		if ( !$sent_ids )
			$sent_ids = array();

		return $sent_ids;
	}

	/**
	 * Add the IDs of users who have been sent an email notification for this comment.
	 * @param array $ids
	 */
	protected function add_sent_recipient_ids( $ids ) {
		$sent_ids = array_unique( array_merge( $this->sent_recipient_ids(), $ids ) );
		update_comment_meta( $this->comment->comment_ID, self::$sent_meta_key, $sent_ids );
	}

	/**
	 * Remove the IDs of users who were not sent an email notification for this comment.
	 * @param array $ids
	 */
	protected function remove_sent_recipient_ids( $ids ) {
		$sent_ids = array_diff( $this->sent_recipient_ids(), $ids );
		update_comment_meta( $this->comment->comment_ID, self::$sent_meta_key, $sent_ids );
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
	 * @param int $number
	 * @return array
	 */
	protected function previous_comments( $number = 3 ) {

		if ( $this->comment->comment_parent )
			return $this->comment_thread();

		$comments = $this->previous_top_level_comments( $number );

		foreach ( $comments as $comment ) {
			$comment->excerpt = $this->excerpt( $comment );
		}

		return array_reverse( $comments );
	}

	/**
	 * @param int $number
	 * @return array
	 */
	protected function previous_top_level_comments( $number = 3 ) {
		$query = array(
			'post_id' => $this->comment->comment_post_ID,
			'parent' => 0,
			'status' => 'approve',
			'number' => $number,
			'date_query' => array(
				array(
					'before' => $this->comment->comment_date,
					'inclusive' => true,
				)
			)
		);
		return get_comments( $query );
	}

	/**
	 * @return array
	 */
	protected function comment_thread() {

		$comment = $this->comment;
		$comments = array( $comment );

		while ( $comment->comment_parent ) {
			$comment = get_comment( $comment->comment_parent );
			$comments[] = $comment;
		}

		return $comments;
	}

	/**
	 * @param $comment_id
	 * @return array
	 */
	protected function comment_children( $comment_id ) {
		$children = get_comments( array(
			'parent' => $comment_id,
			'status' => 'approve',
		) );

		if ( ! $children )
			return array();

		foreach ( $children as $child ) {
			$children = array_merge( $children, $this->comment_children( $child->comment_ID ) );
		}

		return $children;
	}

	/**
	 * Make a 100 word excerpt of a comment.
	 * @param object $comment
	 * @param int $word_count
	 * @return string
	 */
	protected function excerpt( $comment, $word_count = 100 ) {

		$comment_text = strip_tags( $comment->comment_content );

		$words = explode( ' ', $comment_text );

		$elipsis = count( $words ) > $word_count ? ' &hellip;' : '';

		return implode( ' ', array_slice( $words, 0, $word_count ) ) . $elipsis;
	}

	/**
	 * Get the comment author user if there is one.
	 * @return bool|WP_User
	 */
	protected function comment_author_user() {

		$comment_author = get_user_by( 'id', $this->comment->user_id );
		if ( !$comment_author )
			$comment_author = get_user_by( 'email', $this->comment->comment_author_email );

		return $comment_author;
	}

	/**
	 * @param array $template_data
	 * @return string
	 */
	protected function subject( $template_data ) {

		/** @var WP_User|null $parent_author */
		/** @var WP_User $subscriber */
		/** @var string $commenter_name */
		/** @var Prompt_Post $subscribed_post */
		/** @var object $parent_comment */
		/** @var string $parent_author_name */
		extract( $template_data );

		if ( $parent_author and $parent_author->ID == $subscriber->ID )
			return sprintf(
				__( '%s replied to your comment on %s.', 'Postmatic' ),
				$commenter_name,
				$subscribed_post->get_wp_post()->post_title
			);

		if ( $parent_comment )
			return sprintf(
				__( '%s replied to %s on %s', 'Postmatic' ),
				$commenter_name,
				$parent_author_name,
				$subscribed_post->get_wp_post()->post_title
			);

		return sprintf(
			__( '%s commented on %s', 'Postmatic' ),
			$commenter_name,
			$subscribed_post->get_wp_post()->post_title
		);
	}

}