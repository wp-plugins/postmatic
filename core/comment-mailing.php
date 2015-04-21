<?php

class Prompt_Comment_Mailing {

	/**
	 * Send notifications appropriate for a newly published comment.
	 *
	 * Top level comments go to all post subscribers, replies optionally to the replyee.
	 *
	 * @param object|int $comment_id_or_object
	 * @param string $signature Optional identifier for this batch. Just distinguishes cron jobs, ignored here.
	 * @param Prompt_Comment_Mailer $mailer Optional object to use for sending notifications.
	 */
	public static function send_notifications( $comment_id_or_object, $signature = '', $mailer = null ) {

		$comment = get_comment( $comment_id_or_object );

		self::handle_new_subscriber( $comment );

		$mailer = $mailer ? $mailer : new Prompt_Comment_Mailer( $comment );

		$mailer->send_notifications();
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
		$template = new Prompt_Template( 'comment-rejected-email.php' );
		$email = new Prompt_Email( array(
			'to_address' => $comment_author->user_email,
			'subject' => $subject,
			'html' => $template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
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

	/**
	 * Handle the situation when a moderated comment subscribe request has not yet been fulfilled.
	 * @param $comment
	 */
	protected static function handle_new_subscriber( $comment ) {

		if ( ! Prompt_Comment_Form_Handling::subscription_requested( $comment ) )
			return;

		Prompt_Comment_Form_Handling::subscribe_commenter( $comment );
	}

}