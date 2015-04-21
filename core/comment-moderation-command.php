<?php

class Prompt_Comment_Moderation_Command implements Prompt_Interface_Command {

	protected static $approve_method = 'approve';
	protected static $spam_method = 'spam';
	protected static $trash_method = 'trash';

	/** @var array */
	protected $keys = array( 0 );
	/** @var  int */
	protected $comment_id;
	/** @var  int */
	protected $moderator_id;
	/** @var  object */
	protected $message;
	/** @var  string */
	protected $message_text;
	/** @var  Prompt_Email_Text_Cleaner */
	protected $cleaner;

	public function __construct( Prompt_Email_Text_Cleaner $cleaner = null ) {
		$this->cleaner = $cleaner ? $cleaner : new Prompt_Email_Text_Cleaner();
	}

	public function set_keys( $keys ) {
		$this->keys = $keys;
	}

	public function get_keys() {
		return $this->keys;
	}

	public function set_message( $message ) {
		$this->message = $message;
	}

	public function get_message() {
		return $this->message;
	}

	public function execute() {

		if ( !$this->validate() )
			return;

		$text_command = $this->get_text_command();
		if ( $text_command ) {
			$this->$text_command();
			return;
		}

		// Approve this comment AND add a new one
		$this->approve();
		$this->add_comment();
	}

	public function set_comment_id( $id ) {
		$this->comment_id = intval( $id );
		$this->keys[0] = $this->comment_id;
	}

	public function set_moderator_id( $id ) {
		$this->moderator_id = intval( $id );
		$this->keys[1] = $this->moderator_id;
	}

	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) != 2 ) {
			Prompt_Logging::add_error(
				'invalid_comment_moderation_keys',
				__( 'Received a comment moderation command with invalid keys.', 'Postmatic' ),
				array( 'keys' => $this->keys )
			);
			return false;
		}

		$this->comment_id = intval( $this->keys[0] );
		$this->moderator_id = intval( $this->keys[1] );

		if ( !user_can( $this->moderator_id, 'edit_comment', $this->comment_id ) ) {
			Prompt_Logging::add_error(
				'moderator_capability_error',
				__(
					'Received a comment moderation command from a user with insufficient capabilities.',
					'Postmatic'
				),
				array( 'keys' => $this->keys )
			);
			return false;
		}

		return true;
	}

	protected function get_message_text() {
		if ( !$this->message_text )
			$this->message_text = $this->cleaner->strip( $this->message->message );

		return $this->message_text;
	}

	/**
	 * Get text command from the message, if any.
	 *
	 * A blank message is treated as a publish command.
	 *
	 * @return string Text command if found, otherwise empty.
	 */
	protected function get_text_command() {

		$message_text = $this->get_message_text();

		if ( preg_match( '/^\s*$/i', $message_text, $matches ) )
			return self::$approve_method;

		if ( preg_match( '/^\s*(approve|spam|trash)\s*$/i', $message_text, $matches ) )
			return trim( $matches[1] );

		if ( preg_match( '/^\s*(ap[pr]..ve|ap..ve)\s*$/i', $message_text, $matches ) )
			return self::$approve_method;

		if ( preg_match( '/^\s*(p.[bp]..[sc]h|p.b..[sh]|p.b..hs|p.bls.h)\s*$/i', $message_text, $matches ) )
			return self::$approve_method;

		if ( preg_match( '/^\s*(sp[am]m?m?|sam)\s*$/i', $message_text, $matches ) )
			return self::$spam_method;

		if ( preg_match( '/^\s*(tr..[sc]h|tr.[hsc][hs]|t[ar][ars][hs])\s*$/i', $message_text, $matches ) )
			return self::$trash_method;

		return '';
	}

	protected function approve() {

		wp_set_comment_status( $this->comment_id, 'approve' );

	}

	protected function spam() {

		wp_spam_comment( $this->comment_id );

	}

	protected function trash() {

		wp_trash_comment( $this->comment_id );

	}

	protected function add_comment() {

		$text = $this->get_message_text();

		$parent_comment = get_comment( $this->comment_id );

		$post_id = $parent_comment->comment_post_ID;

		$post = get_post( $post_id );

		if ( !$post ) {
			trigger_error(
				sprintf( __( 'rejected comment on unqualified post %s', 'Postmatic' ), $post_id ),
				E_USER_NOTICE
			);
			Prompt_Comment_Mailing::send_rejected_notification( $this->moderator_id, $post_id );
			return;
		}

		$user = get_userdata( $this->moderator_id );
		$comment_data = array(
			'user_id' => $user->ID,
			'comment_post_ID' => $post_id,
			'comment_content' => $text,
			'comment_agent' => __CLASS__,
			'comment_author' => $user->display_name,
			'comment_author_IP' => '',
			'comment_author_url' => $user->user_url,
			'comment_author_email' => $user->user_email,
			'comment_parent' => $parent_comment->comment_ID,
			'comment_approved' => 1,
		);

		$comment_data = apply_filters( 'prompt_preprocess_comment', $comment_data );
		$comment_data = wp_filter_comment( $comment_data );

		wp_insert_comment( $comment_data );
	}
}
