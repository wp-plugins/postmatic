<?php

class Prompt_Comment_Flood_Controller {

	protected static $last_hour_comment_count_max = 6;
	protected static $flood_meta_key = 'prompt_flood_comment';

	/** @var  object */
	protected $comment;
	/** @var  Prompt_Post */
	protected $prompt_post;

	function __construct( $comment ) {
		$this->comment = $comment;
		$this->prompt_post = new Prompt_Post( $comment->comment_post_ID );
	}

	/**
	 * Trigger flood notification if necessary and return IDs that should
	 * receive a regular comment notification.
	 *
	 * @return array
	 */
	function control_recipient_ids() {
		$site_comments = new Prompt_Site_Comments();
		$site_recipient_ids = $site_comments->subscriber_ids();

		$comment_author_id = $this->comment->user_id;
		if ( !$comment_author_id ) {
			$author = get_user_by( 'email', $this->comment->comment_author_email );
			$comment_author_id = $author ? $author->ID : null;
		}

		$post_author_recipient_ids = array();
		if ( Prompt_Core::$options->get( 'auto_subscribe_authors' ) )
			$post_author_recipient_ids = array( $this->prompt_post->get_wp_post()->post_author );

		$post_recipient_ids =  array_diff( $this->prompt_post->subscriber_ids(), array( $comment_author_id ) );

		if ( $this->is_flood() ) {

			$this->prompt_post->set_flood_control_comment_id( $this->comment->comment_ID );

			$this->unsubscribe( $post_recipient_ids );

			$this->send_notifications( $post_recipient_ids );

			return $this->all_ids_except( $comment_author_id, $site_recipient_ids, $post_author_recipient_ids );
		}

		return $this->all_ids_except(
			$comment_author_id,
			$site_recipient_ids,
			$post_author_recipient_ids,
			$post_recipient_ids
		);
	}

	protected function all_ids_except( $exclude, $array1, $array2 = null, $_ = null ) {
		$args = func_get_args();

		$exclude_id = array_shift( $args );

		$all_ids = array_unique( call_user_func_array( 'array_merge', $args ) );

		return array_diff( $all_ids, array( $exclude_id ) );
	}

	/**
	 * @return bool
	 */
	protected function is_flood() {

		if ( get_comment_count( $this->prompt_post->id() ) < self::$last_hour_comment_count_max )
			return false;

		if ( $this->prompt_post->get_flood_control_comment_id() )
			return false;

		$last_hour_comment_count = get_comments( array(
			'count' => true,
			'post_id' => $this->prompt_post->id(),
			'status' => 'approve',
			'date_query' => array(
				array(
					'column' => 'comment_date',
					'after' => '1 hour ago',
				)
			)
		) );

		if ( $last_hour_comment_count <= self::$last_hour_comment_count_max )
			return false;

		return true;
	}

	/**
	 * Unsubscribe an array of user IDs from the post.
	 * @param $ids
	 */
	protected function unsubscribe( $ids ) {
		foreach( $ids as $id ) {
			$this->prompt_post->unsubscribe( $id );
		}
	}

	protected function send_notifications( $recipient_ids ) {

		$emails = array();

		foreach( $recipient_ids as $recipient_id ) {

			$subscriber = get_userdata( $recipient_id );

			if ( !$subscriber or !$subscriber->user_email )
				continue;

			$template_data = array(
				'subscriber' => $subscriber,
				'post' => $this->prompt_post,
				'subject' => __( 'We\'re pausing comment notices for you.', 'Postmatic' ),
				'comment_header' => true,
			);
			/**
			 * Filter comment email template data.
			 *
			 * @param array $template_data {
			 * @type WP_User $subscriber
			 * @type Prompt_post $post
			 * @type string $subject
			 * @type bool $comment_header
			 * }
			 */
			$template_data = apply_filters( 'prompt/comment_flood_email/template_data', $template_data );

			$html_template = new Prompt_Email_Template( 'comment-flood-email.php' );
			$text_template = new Prompt_Text_Email_Template( 'comment-flood-email-text.php' );

			$email = new Prompt_Email( array(
				'to_address' => $subscriber->user_email,
				'subject' => $template_data['subject'],
				'text' => $text_template->render( $template_data ),
				'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
			) );

			if ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) )
				$email->set_html( $html_template->render( $template_data ) );

			$command = new Prompt_Comment_Flood_Command();
			$command->set_post_id( $this->prompt_post->id() );
			$command->set_user_id( $recipient_id );

			Prompt_Command_Handling::add_command_metadata( $command, $email );

			/**
			 * Filter comment notification email.
			 *
			 * @param Prompt_Email $email
			 * @param array $template_data see prompt/comment_email/template_data
			 */
			$emails[] = apply_filters( 'prompt/comment_flood_email', $email, $template_data );
		}

		if ( empty( $emails ) )
			return;

		Prompt_Factory::make_mailer()->send_many( $emails );

	}

}