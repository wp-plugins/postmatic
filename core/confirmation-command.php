<?php

/**
 * Comment command for a subscription confirmation reply
 *
 * If a post ID was included, can comment on that, otherwise only
 * used to unsubscribe.
 */
class Prompt_Confirmation_Command extends Prompt_Comment_Command {

	/** @var array */
	protected $keys = array( 0, 0, 0, '', 0 );
	/** @var  string */
	protected $object_type;
	/** @var  int */
	protected $object_id;

	public function get_keys() {
		return parent::get_keys();
	}

	public function set_object_type( $type ) {
		$this->object_type = $type;
		$this->keys[3] = $type;
	}

	public function set_object_id( $id ) {
		$this->object_id = $id;
		$this->keys[4] = $id;
	}

	/**
	 * Another subscribe response to the subscription confirmation does nothing.
	 */
	protected function subscribe( $notify = false ) {
		return;
	}

	/**
	 * Unsubscribe from whatever the confirmation was for.
	 */
	protected function unsubscribe() {
		/** @var Prompt_Interface_Subscribable $object */
		$object = new $this->object_type( $this->object_id );

		$object->unsubscribe( $this->user_id );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $object );
	}

	/**
	 * Only add comments when a post ID was supplied.
	 */
	protected function add_comment() {

		if ( $this->post_id )
			parent::add_comment();
	}

	protected function validate() {

		if ( !is_array( $this->keys ) or count( $this->keys ) < 5 ) {
			trigger_error( __( 'Invalid confirmation keys', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		if ( empty( $this->message ) ) {
			trigger_error( __( 'Invalid message', 'Postmatic' ), E_USER_WARNING );
			return false;
		}

		$this->post_id = $this->keys[0];
		$this->user_id = $this->keys[1];
		$this->parent_comment_id = $this->keys[2];
		$this->object_type = $this->keys[3];
		$this->object_id = $this->keys[4];

		return true;
	}

}

