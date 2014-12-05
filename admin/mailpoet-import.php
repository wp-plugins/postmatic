<?php

class Prompt_Admin_Mailpoet_Import {

	/** @var  WYSIJA_model_user */
	protected $user_model;
	/** @var  array */
	protected $list_ids;
	/** @var  array */
	protected $subscribers;
	/** @var int */
	protected $already_subscribed_count = 0;
	/** @var int */
	protected $imported_count = 0;
	/** @var  array */
	protected $rejects;

	public static function is_ready() {
		return class_exists( 'WYSIJA' );
	}


	public static function make( $list_ids ) {
		return new Prompt_Admin_Mailpoet_Import( WYSIJA::get( 'user', 'model' ), $list_ids );
	}

	public function __construct( $user_model, $list_ids ) {
		$this->user_model = $user_model;
		$this->list_ids = $list_ids;
		$this->rejects = array();
	}

	public function get_error() {
		return null;
	}

	public function get_subscriber_count() {
		$this->ensure_subscribers();
		return count( $this->subscribers );
	}

	public function get_imported_count() {
		return $this->imported_count;
	}

	public function get_already_subscribed_count() {
		return $this->already_subscribed_count;
	}

	public function get_rejected_subscribers() {
		return $this->rejects;
	}

	public function execute() {
		$this->ensure_subscribers();

		$prompt_site = new Prompt_Site();

		foreach ( $this->subscribers as $subscriber ) {
			$this->import( $subscriber, $prompt_site );
		}
	}

	protected function ensure_subscribers() {
		if ( isset( $this->subscribers ) )
			return;

		$this->subscribers = array();

		// Enable the model to return more than 10 records. Could be fragile.
		$this->user_model->limit_pp = 1000000;

		$list_subscribers = $this->user_model->get_subscribers(
			array( 'A.email', 'A.firstname', 'A.lastname', 'A.last_opened', 'A.last_clicked', 'A.created_at' ),
			array( 'lists' => $this->list_ids )
		);

		foreach ( $list_subscribers as $list_subscriber ) {
			$this->add_source_subscriber( $list_subscriber );
		}
	}

	protected function add_source_subscriber( $subscriber ) {

		if ( $this->is_valid_subscriber( $subscriber ) )
			$this->subscribers[] = $subscriber;
		else
			$this->rejects[] = $subscriber;

	}

	protected function is_valid_subscriber( $subscriber ) {
		if ( empty( $subscriber['created_at'] ) or empty( $subscriber['last_clicked'] ) )
			return false;

		return $subscriber['last_clicked'] > $subscriber['created_at'];
	}

	/**
	 * @param array $subscriber
	 * @param Prompt_Interface_Subscribable $object
	 */
	protected function import( $subscriber, $object ) {

		$existing_user = get_user_by( 'email', $subscriber['email'] );

		if ( $existing_user and $object->is_subscribed( $existing_user->ID ) ) {
			$this->already_subscribed_count++;
			return;
		}

		if ( !$existing_user ) {
			$subscriber_id = Prompt_User_Handling::create_from_email( $subscriber['email'] );
			wp_update_user( array(
				'ID' => $subscriber_id,
				'first_name' => $subscriber['firstname'],
				'last_name' => $subscriber['lastname'],
			) );
		} else {
			$subscriber_id = $existing_user->ID;
		}

		$object->subscribe( $subscriber_id );

		$prompt_user = new Prompt_User( $subscriber_id );

		$origin = new Prompt_Subscriber_Origin( array(
			'source_label' => 'Mailpoet Import',
			'source_url' => scbUtil::get_current_url(),
		) );

		$prompt_user->set_subscriber_origin( $origin );

		$this->imported_count++;
	}
}