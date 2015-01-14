<?php

abstract class Prompt_Option_Subscribable_Object implements Prompt_Interface_Subscribable {

	abstract protected function option_key();

	// These would be abstract, but PHP 5.3 pukes on that
	public function id() {}
	public function subscription_url() {}
	public function subscription_object_label() {}
	public function subscription_description() {}

	public function subscriber_ids() {
		$ids = get_option( $this->option_key() );
		if ( !$ids )
			$ids = array();
		return $ids;
	}

	public function is_subscribed( $user_id ) {
		$subscriber_ids = $this->subscriber_ids( $user_id );
		return in_array( $user_id, $subscriber_ids );
	}

	public function subscribe( $user_id ) {
		$user_id = intval( $user_id );

		if ( $user_id <= 0 ) {
			trigger_error( __( 'Refusing to subscribe an invalid user ID.', 'Postmatic' ), E_USER_NOTICE );
			return $this;
		}

		$subscriber_ids = $this->subscriber_ids();

		if ( !in_array( $user_id, $subscriber_ids ) ) {
			array_push( $subscriber_ids, $user_id );
			update_option( $this->option_key(), $subscriber_ids );
			/**
			 * A new subscription has been added.
			 *
			 * @param int $subscriber_id
			 * @param Prompt_Interface_Subscribable $object The thing subscribed to.
			 */
			do_action( 'prompt/subscribed', $user_id, $this );
		}
		return $this;
	}

	public function unsubscribe( $user_id ) {
		$success = true;

		$subscriber_ids = $this->subscriber_ids();
		if ( in_array( $user_id, $subscriber_ids ) ) {
			$subscriber_ids = array_diff( $subscriber_ids, array( $user_id ) );
			update_option( $this->option_key(), $subscriber_ids );
			/**
			 * A post subscription has been removed.
			 *
			 * @param int $subscriber_id
			 * @param Prompt_Interface_Subscribable $object The thing subscribed to.
			 */
			do_action( 'prompt/unsubscribed', $user_id, $this );
		}

		return $success;
	}


}