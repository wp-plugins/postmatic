<?php

/**
 * A facade for documented integration points.
 */
class Prompt_Api {
	const INVALID_EMAIL = 'invalid_email';
	const ALREADY_SUBSCRIBED = 'already_subscribed';
	const CONFIRMATION_SENT = 'confirmation_sent';
	const OPT_IN_SENT = 'opt_in_sent';
	const NEVER_SUBSCRIBED = 'never_subscribed';
	const ALREADY_UNSUBSCRIBED = 'already_unsubscribed';

	/**
	 * Subscribe an email address to a list.
	 *
	 * @param array $subscriber_data {
	 *     User data fields, use any from @see wp_insert_user, most likely of interest:
	 *
	 *     @type string $email_address Required.
	 *     @type string $first_name
	 *     @type string $last_name
	 *     @type string $display_name
	 *   }
	 * @param Prompt_Interface_Subscribable $list Optional, default is site-wide new posts.
	 * @return string The resulting status, one of:
	 *   Prompt_Api::INVALID_EMAIL
	 *   Prompt_Api::ALREADY_SUBSCRIBED
	 *   Prompt_Api::CONFIRMATION_SENT      for existing user email addresses
	 *   Prompt_Api::OPT_IN_SENT            for unrecognized email addresses
	 */
	public static function subscribe( $subscriber_data, Prompt_Interface_Subscribable $list = null ) {

		if ( ! is_array( $subscriber_data ) )
			$subscriber_data = array( 'user_email' => $subscriber_data );

		// Translate the friendlier email_address to user_email
		if ( isset( $subscriber_data['email_address'] ) ) {
			$subscriber_data['user_email'] = $subscriber_data['email_address'];
			unset( $subscriber_data['email_address'] );
		}

		$subscriber_data['user_email'] = sanitize_email( $subscriber_data['user_email'] );

		$email_address = $subscriber_data['user_email'];

		if ( !is_email( $email_address ) )
			return self::INVALID_EMAIL;

		$list = $list ? $list : new Prompt_Site();

		$user = get_user_by( 'email', $email_address );

		if ( ! $user ) {

			self::ensure_display_name( $subscriber_data );

			Prompt_Subscription_Mailing::send_agreement( $list, $email_address, $subscriber_data );

			return self::OPT_IN_SENT;
		}

		if ( $list->is_subscribed( $user->ID ) )
			return self::ALREADY_SUBSCRIBED;

		$list->subscribe( $user->ID );

		Prompt_Subscription_Mailing::send_subscription_notification( $user->ID, $list );

		return self::CONFIRMATION_SENT;
	}

	/**
	 * Unsubscribe an email address from a list.
	 *
	 * @param string $email_address The address to unsubscribe
	 * @param Prompt_Interface_Subscribable $list Optional, default is site-wide new posts.
	 * @return string The resulting status, one of:
	 *   Prompt_Api::NEVER_SUBSCRIBED       we don't recognize the email address
	 *   Prompt_Api::ALREADY_UNSUBSCRIBED
	 *   Prompt_Api::CONFIRMATION_SENT
	 */
	public static function unsubscribe( $email_address, Prompt_Interface_Subscribable $list = null ) {

		$user = get_user_by( 'email', $email_address );

		if ( ! $user )
			return self::NEVER_SUBSCRIBED;

		$list = $list ? $list : new Prompt_Site();

		if ( ! $list->is_subscribed( $user->ID ) )
			return self::ALREADY_UNSUBSCRIBED;

		$list->unsubscribe( $user->ID );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $user->ID, $list );

		return self::CONFIRMATION_SENT;
	}

	protected static function ensure_display_name( &$data ) {

		$name = isset( $data['display_name'] ) ? $data['display_name'] : '';

		if ( $name )
			return $name;

		$names = array();

		if ( isset( $data['first_name'] ) )
			$names[] = $data['first_name'];

		if ( isset( $data['last_name'] ) )
			$names[] = $data['last_name'];

		$data['display_name'] = implode( ' ', $names );

		return $data['display_name'];
	}
}
