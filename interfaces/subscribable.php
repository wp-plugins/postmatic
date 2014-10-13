<?php

interface Prompt_Interface_Subscribable extends Prompt_Interface_Identifiable {

	/**
	 * Get the user IDs of all subscribers to this object.
	 * @return array
	 */
	function subscriber_ids();

	/**
	 * Determine whether a user is subscribed to this object.
	 * @param $user_id
	 * @return mixed
	 */
	function is_subscribed( $user_id );

	/**
	 * Ensure that a user is subscribed to this object.
	 *
	 * Do nothing if the user is already subscribed.
	 *
	 * @param int $user_id
	 * @return Prompt_Interface_Subscribable A reference to this object.
	 */
	function subscribe( $user_id );

	/**
	 * Unsubscribe a user from this object.
	 *
	 * @param int $user_id
	 * @return Prompt_Interface_Subscribable A reference to this object.
	 */
	function unsubscribe( $user_id );

	function subscription_url();

	function subscription_object_label();

	function subscription_description();

	/**
	 * Get the IDs of all objects a user is subscribed to.
	 * @param $user_id
	 * @return mixed
	 */
	static function subscribed_object_ids( $user_id );
}