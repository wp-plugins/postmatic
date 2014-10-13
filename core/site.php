<?php

class Prompt_Site implements Prompt_Interface_Subscribable {

	const OPTION_KEY = 'prompt_subscribed_user_ids';

	public function id() {
		return get_current_blog_id();
	}

	public function subscriber_ids() {
		$ids = get_option( self::OPTION_KEY );
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
			trigger_error( __( 'Refusing to subscribe an invalid user ID.', 'Prompt_Core' ), E_USER_NOTICE );
			return $this;
		}

		$subscriber_ids = $this->subscriber_ids();

		if ( !in_array( $user_id, $subscriber_ids ) ) {
			array_push( $subscriber_ids, $user_id );
			update_option( self::OPTION_KEY, $subscriber_ids );
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
			update_option( self::OPTION_KEY, $subscriber_ids );
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

	public function subscription_url() {
		return get_home_url();
	}

	public function subscription_object_label() {
		return get_option( 'blogname' );
	}

	public function subscription_description() {
		return sprintf(
			__( 'You have successfully subscribed to %s and will receive new posts as soon as they are published.', 'Prompt_Core' ),
			get_option( 'blogname' )
		);
	}

	public static function subscribed_object_ids( $user_id ) {
		$ids = array();
		$site = new Prompt_Site;
		if ( $site->is_subscribed( $user_id ) )
			$ids[] = $site->id();
		return $ids;
	}
}