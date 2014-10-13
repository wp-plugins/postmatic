<?php

/**
 * Prompt behavior specific to a post.
 *
 * Encapsulates a WordPress post, since WordPress doesn't allow extension.
 */
class Prompt_Post extends Prompt_Meta_Subscribable_Object {

	/** @var  int user ID */
	protected $id;
	/** @var WP_Post post object */
	protected $wp_post;
	/** @var string */
	protected $sent_meta_key = 'prompt_sent_ids';

	/**
	 * Create a Prompt post.
	 *
	 * @param int|WP_Post $post_id_or_object
	 */
	public function __construct( $post_id_or_object ) {

		$this->meta_type = 'post';

		if ( is_a( $post_id_or_object, 'WP_Post' ) ) {
			$this->wp_post = $post_id_or_object;
			$this->id = $this->wp_post->ID;
		} else {
			$this->id = intval( $post_id_or_object );
		}
	}

	/**
	 * Get the WordPress user ID.
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get the underlying post.
	 * @return null|WP_Post
	 */
	public function get_wp_post() {
		if ( !isset( $this->wp_post ) )
			$this->wp_post = get_post( $this->id );
		return $this->wp_post;
	}

	public function subscription_url() {
		return get_permalink( $this->id );
	}

	public function subscription_object_label() {
		return sprintf(
			__( 'discussion of <em>%s</em>', 'Prompt_Core' ),
			$this->get_wp_post()->post_title
		);
	}

	public function subscription_description() {
		return sprintf(
			__( 'You have successfully subscribed and will receive an email when there is a new comment on <em>%s</em>.', 'Prompt_Core' ),
			$this->get_wp_post()->post_title
		);
	}

	/**
	 * Get the IDs of users who should receive an email when this post is published.
	 *
	 * This includes both subscribers to the author and to the site.
	 *
	 * Post types not enabled in the options will have no recipients.
	 *
	 * @return array An array of user IDs.
	 */
	public function recipient_ids() {
		$recipient_ids = array();

		$post = $this->get_wp_post();

		if ( in_array( $post->post_type, Prompt_Core::$options->get( 'site_subscription_post_types' ) ) ) {

			$prompt_site = new Prompt_Site;
			$recipient_ids = $prompt_site->subscriber_ids();

		}

		$prompt_author = new Prompt_User( $post->post_author );
		$recipient_ids = array_unique(
			array_merge( $recipient_ids, $prompt_author->subscriber_ids() )
		);

		/**
		 * Filter the recipient ids of notifications for a post.
		 *
		 * @param array $recipient_ids
		 * @param WP_Post $post
		 */
		$recipient_ids = apply_filters( 'prompt/recipient_ids/post', $recipient_ids, $post );

		return $recipient_ids;
	}

	/**
	 * Get the IDs of users who have been sent an email notification for this post.
	 * @return array
	 */
	public function sent_recipient_ids() {
		$sent_ids = get_post_meta( $this->id, $this->sent_meta_key, true );

		if ( !$sent_ids )
			$sent_ids = array();

		return $sent_ids;
	}

	/**
	 * Add the IDs of users who have been sent an email notification for this post.
	 * @param array $ids
	 * @return $this
	 */
	public function add_sent_recipient_ids( $ids ) {
		$sent_ids = array_unique( array_merge( $this->sent_recipient_ids(), $ids ) );
		update_post_meta( $this->id, $this->sent_meta_key, $sent_ids );
		return $this;
	}

	/**
	 * Get the IDs of users who have been NOT yet been sent an email notification for this post.
	 * @return array
	 */
	public function unsent_recipient_ids() {
		return array_diff( $this->recipient_ids(), $this->sent_recipient_ids() );
	}

	/**
	 * Get all the posts a user is subscribed to.
	 *
	 * @param $user_id
	 * @return mixed|void
	 */
	public static function subscribed_object_ids( $user_id ) {

		// Using a "fake" post object for PHP 5.2, which doesn't have static method inheritance
		$post = new Prompt_Post( 0 );

		return $post->_subscribed_object_ids( $user_id );
	}

}