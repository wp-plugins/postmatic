<?php

class Prompt_Moderation_Mailing {

	public static function send_notifications( $comment_id_or_object, $recipient_addresses ) {

		$comment = get_comment( $comment_id_or_object );

		// Auto-approve comments from moderators
		if ( in_array( $comment->comment_author_email, $recipient_addresses ) ) {
			wp_set_comment_status( $comment->comment_ID, 'approve' );
			return;
		}

		$type = empty( $comment->comment_type ) ? 'comment' : $comment->comment_type;

		$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);

		$comment_author = get_user_by( 'email', $comment->comment_author_email );
		$commenter_name = $comment_author ? $comment_author->display_name : $comment->comment_author;
		$commenter_name = $commenter_name ? $commenter_name : __( 'Anonymous' );

		$post = get_post( $comment->comment_post_ID );

		$subject = sprintf( __( 'Please moderate "%s"', 'Postmatic' ), $post->post_title );

		$comment_header = true;

		$template_data = compact(
			'comment',
			'type',
			'post',
			'comment_author_domain',
			'commenter_name',
			'subject',
			'comment_header'
		);

		/**
		 * Filter comment moderation email template data.
		 *
		 * @param array $template_data {
		 * @type object $comment
		 * @type string $type 'comment', 'pingback', 'trackback', etc.
		 * @type WP_Post $post
		 * @type string $comment_author_domain
		 * @type string $commenter_name
		 * @type string $subject
		 * @type bool $comment_header
		 * }
		 */
		$template_data = apply_filters( 'prompt/comment_moderation_email/template_data', $template_data );

		$html_template = new Prompt_Email_Template( 'comment-moderation-email.php' );

		$text_template = new Prompt_Text_Email_Template( 'comment-moderation-email-text.php' );

		$emails = array();

		foreach ( $recipient_addresses as $recipient_address ) {

			$moderator = get_user_by( 'email', $recipient_address );

			if ( !$moderator )
				continue;

			$email = new Prompt_Email( array(
				'to_address' => $recipient_address,
				'subject' => $subject,
				'text' => $text_template->render( $template_data ),
				'html' => $html_template->render( $template_data ),
				'message_type' => Prompt_Enum_Message_Types::COMMENT_MODERATION,
			) );

			$command = new Prompt_Comment_Moderation_Command();
			$command->set_comment_id( $comment->comment_ID );
			$command->set_moderator_id( $moderator->ID );
			Prompt_Command_Handling::add_command_metadata( $command, $email );

			/**
			 * Filter comment moderation email.
			 *
			 * @param Prompt_Email $email
			 * @param array $template_data see prompt/comment_email/template_data
			 */
			$emails[] = apply_filters( 'prompt/comment_moderation_email', $email, $template_data );

		}

		if ( empty( $emails ) )
			return;

		Prompt_Factory::make_mailer()->send_many( $emails );
	}

}