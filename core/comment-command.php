<?php

class Prompt_Comment_Command implements Prompt_Interface_Command {

	protected static $subscribe_method = 'subscribe';
	protected static $unsubscribe_method = 'unsubscribe';

	/** @var array */
	protected $keys = array( 0 );
	/** @var  int */
	protected $post_id;
	/** @var  int */
	protected $user_id;
	/** @var  object */
	protected $message;
	/** @var  int */
	protected $parent_comment_id;
	/** @var  Prompt_Email_Text_Cleaner */
	protected $cleaner;
	/** @var  string */
	protected $message_text;

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
			$this->$text_command( $notify = true );
			return;
		}

		$this->add_comment();
	}

	public function set_post_id( $id ) {
		$this->post_id = intval( $id );
		$this->keys[0] = $this->post_id;
	}

	public function set_user_id( $id ) {
		$this->user_id = intval( $id );
		$this->keys[1] = $this->user_id;
	}

	public function set_parent_comment_id( $id ) {
		$this->parent_comment_id = intval( $id );
		$this->keys[2] = $this->parent_comment_id;
	}

	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) < 2 ) {
			trigger_error( __( 'Invalid comment keys', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		// Ensure back compatibility with beta versions that did not include parent comment ID
		if ( count( $this->keys ) == 2 )
			$this->keys[2] = 0;

		if ( empty( $this->message ) ) {
			trigger_error( __( 'Invalid message', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		$this->post_id = $this->keys[0];
		$this->user_id = $this->keys[1];
		$this->parent_comment_id = $this->keys[2];

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
	 * A blank message is treated as a subscribe command.
	 *
	 * @return string Text command if found, otherwise empty.
	 */
	protected function get_text_command() {

		$stripped_text = $this->get_message_text();

		if ( preg_match( '/^\s*$/', $stripped_text, $matches ) )
			return self::$subscribe_method;

		/* translators: this word should match "reply with the word 'subscribe'" translations */
		$subscribe_command = __( 'subscribe', 'Postmatic' );
		$subscribe_pattern = '/^[\s\*\_]*(' . $subscribe_command .
			'|usbscribe|s..scribe|suscribe|susribe?|susrib)[\s\*\_]*/i';

		if ( preg_match( $subscribe_pattern, $stripped_text, $matches ) )
			return self::$subscribe_method;

		/* translators: this word should match "reply with the word 'unsubscribe'" translations */
		$unsubscribe_command = __( 'unsubscribe', 'Postmatic' );
		$unsubscribe_pattern = '/^[\s\*\_]*(' . $unsubscribe_command .
			'|un..bscribe?|sunsubscribe|unsusbscribe|un..scribe|unsusribe?|unsubcribe)[\s\*\_]*/i';

		if ( preg_match( $unsubscribe_pattern, $stripped_text, $matches ) )
			return self::$unsubscribe_method;

		return '';
	}

	protected function subscribe( $notify = false ) {

		$prompt_post = new Prompt_Post( $this->post_id );

		if ( $prompt_post->is_subscribed( $this->user_id ) )
			return;

		if (
			Prompt_Core::$options->get( 'auto_subscribe_authors' ) and
			$this->user_id == $prompt_post->get_wp_post()->post_author
		)
			return;

		$prompt_post->subscribe( $this->user_id );

		if ( $notify )
			Prompt_Subscription_Mailing::send_subscription_notification( $this->user_id, $prompt_post );
	}

	protected function unsubscribe() {

		$prompt_post = new Prompt_Post( $this->post_id );

		if ( !$prompt_post->is_subscribed( $this->user_id ) )
			return array( 'status' => 'OK' );

		$prompt_post->unsubscribe( $this->user_id );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $prompt_post );

		return array( 'status' => 'OK' );
	}

	protected function add_comment() {

		$text = $this->get_message_text();

		$post = get_post( $this->post_id );

		if ( !$post or 'publish' != $post->post_status or !comments_open( $this->post_id ) ) {
			trigger_error(
				sprintf( __( 'rejected comment on unqualified post %s', 'Postmatic' ), $this->post_id ),
				E_USER_NOTICE
			);
			Prompt_Comment_Mailing::send_rejected_notification( $this->user_id, $this->post_id );
			return;
		}

		if ( $this->comment_exists( $text ) ) {
			trigger_error(
				sprintf( __( 'rejected duplicate comment on %s', 'Postmatic' ), $this->post_id ),
				E_USER_NOTICE
			);
			return;
		}

		$this->subscribe( $notify = false );

		$user = get_userdata( $this->user_id );
		$comment_data = array(
			'user_id' => $this->user_id,
			'comment_post_ID' => $this->post_id,
			'comment_content' => $text,
			'comment_agent' => __CLASS__,
			'comment_author' => $user->display_name,
			'comment_author_IP' => '',
			'comment_author_url' => $user->user_url,
			'comment_author_email' => $user->user_email,
			'comment_parent' => $this->parent_comment_id,
		);

		$comment_data['comment_approved'] = get_option( 'comment_moderation' ) ? 0 : 1;

		$comment_data = apply_filters( 'prompt_preprocess_comment', $comment_data );
		$comment_data = wp_filter_comment( $comment_data );

		$comment_id = wp_insert_comment( $comment_data );

		if ( 0 == $comment_data['comment_approved'] )
			wp_notify_moderator( $comment_id );

	}

	protected function comment_exists( $text ) {
		$exists = false;
		$check_comments = get_comments( array(
			'user_id' => $this->user_id,
			'post_ID' => $this->post_id,
		) );
		foreach ( $check_comments as $comment ) {
			if ( $comment->comment_content == $text )
				$exists = true;
		}
		return $exists;
	}

}