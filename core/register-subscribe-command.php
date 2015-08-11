<?php

class Prompt_Register_Subscribe_Command implements Prompt_Interface_Command {
	protected static $user_data_meta_key = 'prompt_user_data';
	protected static $resend_count_meta_key = 'prompt_resend_count';
	protected static $comment_type = 'prompt_pre_reg';

	protected $keys = array( 0 );
	protected $subscribable_object;
	protected $message;

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

		$comment_id = $this->keys[0];
		$comment = get_comment( $comment_id );

		if ( !$comment ) {
			Prompt_Logging::add_error(
				'register_subscribe_comment_invalid',
				__( 'Couldn\'t find the original registration information for a new user.', 'Postmatic' ),
				array( 'keys' => $this->keys, 'message' => $this->message )
			);
			return;
		}

		$subscribable_object_class = $comment->comment_agent;
		if ( !class_exists( $subscribable_object_class ) ) {
			Prompt_Logging::add_error(
				'register_subscribe_object_invalid',
				__( 'Couldn\'t find the original object subscribed to for a new user.', 'Postmatic' ),
				array( 'keys' => $this->keys, 'message' => $this->message )
			);
			return;
		}

		$subscribable_object_id = $comment->comment_parent;
		/** @var Prompt_Interface_Subscribable $subscribable_object */
		$subscribable_object = new $subscribable_object_class( $subscribable_object_id );

		$user_data = get_comment_meta( $comment_id, self::$user_data_meta_key, true );

		$email = $comment->comment_author_email;

		$subscriber = get_user_by( 'email', $email );

		/* translators: this should match "reply with the word 'agree'" translations */
		$agree_command = __( 'agree', 'Postmatic' );
		$agree_pattern = '/(?<!n[o\']t )(' . $agree_command . '|age?ree?)/i';

		if ( !$subscriber and !preg_match( $agree_pattern, $this->get_message_text() ) ) {

			if ( self::stop_resending( $comment ) )
				return;

			Prompt_Subscription_Mailing::send_agreement(
				$subscribable_object,
				$email,
				$user_data,
				$resend_command = $this
			);
			return;
		}

		$subscriber_id = $subscriber ? $subscriber->ID : Prompt_User_Handling::create_from_email( $email );

		if ( is_wp_error( $subscriber_id ) ) {
			Prompt_Logging::add_error(
				'register_subscribe_user_creation_failure',
				__( 'Failed to create a new user from an agreement reply email.', 'Postmatic' ),
				array(
					'keys' => $this->keys,
					'user_data' => $user_data,
					'message' => $this->message,
					'error' => $subscriber_id
				)
			);
			return;
		}

		if ( !$subscriber and $user_data ) {

			$user_data['ID'] = $subscriber_id;

			wp_update_user( $user_data );

			$origin = new Prompt_Subscriber_Origin( array(
				'source_label' => $subscribable_object->subscription_object_label(),
				'source_url' => $subscribable_object->subscription_url(),
				'agreement' => $this->message,
			) );

			$prompt_user = new Prompt_User( $subscriber_id );

			$prompt_user->set_subscriber_origin( $origin );

			do_action( 'prompt/register_subscribe_command/created_user', $prompt_user->get_wp_user() );
		}

		if ( !$subscribable_object->is_subscribed( $subscriber_id ) ){

			$subscribable_object->subscribe( $subscriber_id );
			Prompt_Subscription_Mailing::send_subscription_notification( $subscriber_id, $subscribable_object );

		}

		// TODO: remove our pre registration comment?
	}

	public function save_subscription_data( Prompt_Interface_Subscribable $object, $email, $user_data = array() ) {
		$class = get_class( $object );

		$remote_address = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		$comment_id = wp_insert_comment( array(
			'comment_author_email' => $email,
			'comment_author_IP' => preg_replace( '/[^0-9a-fA-F:., ]/', '',$remote_address ),
			'comment_agent' => $class,
			'comment_parent' => $object->id(),
			'comment_type' => self::$comment_type,
			'comment_approved' => 'Prompt',
		) );

		if ( !empty( $user_data ) )
			add_comment_meta( $comment_id, self::$user_data_meta_key, $user_data );

		$this->keys = array( $comment_id );
	}

	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) != 1 ) {
			Prompt_Logging::add_error(
				'register_subscribe_keys_invalid',
				__( 'Received invalid metadata with a subscription agreement.', 'Postmatic' ),
				array( 'keys' => $this->keys, 'message' => $this->message )
			);
			return false;
		}

		$int_keys = array_filter( $this->keys, 'is_int' );

		if ( $int_keys != $this->keys ) {
			Prompt_Logging::add_error(
				'register_subscribe_keys_invalid',
				__( 'Received invalid metadata with a subscription agreement.', 'Postmatic' ),
				array( 'keys' => $this->keys, 'message' => $this->message )
			);
			return false;
		}

		if ( empty( $this->message ) ) {
			Prompt_Logging::add_error(
				'register_subscribe_message_invalid',
				__( 'Received no message with a subscription agreement.', 'Postmatic' ),
				array( 'keys' => $this->keys, 'message' => $this->message )
			);
			return false;
		}

		return true;
	}

	protected function get_message_text() {
		return $this->message->message;
	}

	protected function stop_resending( $comment ) {

		$resend_count = get_comment_meta( $comment->comment_ID, self::$resend_count_meta_key, true );

		$resend_count += 1;

		update_comment_meta( $comment->comment_ID, self::$resend_count_meta_key, $resend_count );

		return ( $resend_count > 2 );
	}
}