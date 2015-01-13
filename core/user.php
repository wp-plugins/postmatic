<?php

/**
 * Prompt behavior specific to a user.
 *
 * Encapsulates a WordPress user, since WordPress doesn't allow extension.
 */
class Prompt_User extends Prompt_Meta_Subscribable_Object {

	/** @var  int user ID */
	protected $id;
	/** @var WP_User user object */
	protected $wp_user;
	/** @var Prompt_Subscriber_Origin */
	protected $origin;
	/** @var string */
	protected $origin_meta_key = 'prompt_subscriber_origin';

	/**
	 * Create an Prompt_Core user.
	 *
	 * @param int|WP_User $user_id_or_object
	 */
	public function __construct( $user_id_or_object ) {

		$this->meta_type = 'user';

		if ( is_a( $user_id_or_object, 'WP_User' ) ) {
			$this->wp_user = $user_id_or_object;
			$this->id = $this->wp_user->ID;
		} else {
			$this->id = intval( $user_id_or_object );
		}
	}

	/**x
	 * Get the WordPress user ID.
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Get the underlying user.
	 * @return null|WP_User
	 */
	public function get_wp_user() {
		if ( !isset( $this->wp_user ) )
			$this->wp_user = get_userdata( $this->id );
		return $this->wp_user;
	}

	/**
	 * @return null|Prompt_Subscriber_Origin
	 */
	public function get_subscriber_origin() {
		if ( !isset( $this->origin ) ) {
			$origin = get_user_meta( $this->id, $this->origin_meta_key, true );
			$this->origin = $origin ? $origin : null;
		}
		return $this->origin;
	}

	/**
	 * @param Prompt_Subscriber_Origin $origin
	 * @return Prompt_User $this
	 */
	public function set_subscriber_origin( Prompt_Subscriber_Origin $origin ) {
		$this->origin = $origin;
		update_user_meta( $this->id, $this->origin_meta_key, $this->origin );
		return $this;
	}

	/**
	 * Get option form elements for a user.
	 * @return string User options HTML.
	 */
	public function profile_options() {
		return html( 'div class="prompt-profile-options"',
			html( 'h2', __( 'Postmatic Subscription Information', 'Postmatic' ) ),
			$this->profile_subscribers(),
			$this->profile_site_subscriptions(),
			$this->profile_author_subscriptions(),
			$this->profile_post_subscriptions()
		);
	}

	/**
	 * Save changes made to profile options.
	 * @param array $options
	 */
	public function update_profile_options( $options ) {
		$site = new Prompt_Site;

		if ( empty( $options['prompt_site_subscribed'] ) )
			$site->unsubscribe( $this->id );
		elseif ( $this->is_current_user() )
			$site->subscribe( $this->id );

		$site_comments = new Prompt_Site_Comments();

		if ( empty( $options['prompt_site_comments_subscribed'] ) )
			$site_comments->unsubscribe( $this->id );
		elseif ( $this->is_current_user() )
			$site_comments->subscribe( $this->id );
	}

	public function subscription_url() {
		return get_author_posts_url( $this->id );
	}

	public function subscription_object_label() {
		return sprintf(
			__( 'posts by %s', 'Postmatic' ),
			$this->get_wp_user()->display_name
		);
	}

	public function subscription_description() {
		return sprintf(
			__( 'You have successfully subscribed and will receive posts by %s directly in your inbox.', 'Postmatic' ),
			$this->get_wp_user()->display_name
		);
	}

	public function delete_all_subscriptions() {
		$subscribables = Prompt_Subscribing::get_subscribable_classes();

		foreach ( $subscribables as $subscribable ) {
			$object_ids = call_user_func( array( $subscribable, 'subscribed_object_ids' ), $this->id );
			foreach ( $object_ids as $object_id ) {
				$object = new $subscribable( $object_id );
				$object->unsubscribe( $this->id );
			}
		}
	}

	protected function profile_subscribers() {
		$subscriber_ids = $this->subscriber_ids();

		if ( empty( $subscriber_ids ) )
			return '';

		$subscriber_items = '';
		foreach( $subscriber_ids as $user_id ) {
			$user = get_userdata( $user_id );
			$subscriber_items .= html( 'li', $user->display_name );
		}

		return html(
			'div class="prompt-author-subscriptions"',
			html( 'h4', __( 'People that subscribe to you:', 'Postmatic' ) ),
			html( 'ul', $subscriber_items )
		);
	}

	protected function profile_site_subscriptions() {
		$site = new Prompt_Site();
		$site_comments = new Prompt_Site_Comments();
		return html(
			'div id="prompt-site-subscription"',
			html( 'h4', __( 'Site Subscriptions:', 'Postmatic' ) ),
			scbForms::input(
				array(
					'name' => 'prompt_site_subscribed',
					'type' => 'checkbox',
					'desc' => __( 'Please send all new posts to me by email.', 'Postmatic' ),
					'extra' => array( 'disabled' => !$site->is_subscribed( $this->id ) and !$this->is_current_user() )
				),
				array( 'prompt_site_subscribed' => $site->is_subscribed( $this->id ) )
			),
			'<br/>',
			scbForms::input(
				array(
					'name' => 'prompt_site_comments_subscribed',
					'type' => 'checkbox',
					'desc' => __( 'Please send me all new comments by email. Even ones on posts to which I have not subscribed.', 'Postmatic' ),
					'extra' => array( 'disabled' => !$site_comments->is_subscribed( $this->id ) and !$this->is_current_user() )
				),
				array( 'prompt_site_comments_subscribed' => $site_comments->is_subscribed( $this->id ) )
			)
		);
	}

	protected function profile_author_subscriptions() {
		$subscribed_author_ids = self::subscribed_object_ids( $this->id );

		if ( empty( $subscribed_author_ids ) )
			return '';


		$author_items = '';
		foreach( $subscribed_author_ids as $author_id ) {
			$author = get_userdata( $author_id );
			$author_items .= html(
				'li',
				html( 'a', array( 'href' => get_author_posts_url( $author_id ) ), $author->display_name )
			);
		}

		return html(
			'div id="prompt-author-subscriptions"',
			html( 'h4', __( 'Authors you subscribe to:', 'Postmatic' ) ),
			html( 'ul', $author_items )
		);
	}

	protected function profile_post_subscriptions() {
		$subscribed_post_ids = Prompt_Post::subscribed_object_ids( $this->id );
		
		if ( empty( $subscribed_post_ids ) )
			return '';
		
		$post_items = '';
		foreach ( $subscribed_post_ids as $post_id ) {
			$post = get_post( $post_id );
			$post_items .= html(
				'li',
				html( 'a', array( 'href' => get_permalink( $post_id ) ), $post->post_title )
			);
		}

		return html(
			'div id="prompt-post-subscriptions"',
			html( 'h4', __( 'Discussions you are subscribed to:', 'Postmatic' ) ),

			html( 'ul', $post_items )
		);
	}

	/**
	 * Determine whether this user is currently logged in.
	 * @return bool
	 */
	protected function is_current_user() {
		return $this->id == get_current_user_id();
	}

	/**
	 * Get all the author IDs a user is subscribed to.
	 *
	 * @param $user_id
	 * @return mixed|void
	 */
	public static function subscribed_object_ids( $user_id ) {

		// Using a "fake" object for PHP 5.2, which doesn't have static method inheritance
		$user = new Prompt_User( 0 );

		return $user->_subscribed_object_ids( $user_id );
	}

	public static function all_subscriber_ids() {

		// Using a "fake" object for PHP 5.2, which doesn't have static method inheritance
		$user = new Prompt_User( 0 );

		return $user->_all_subscriber_ids();
	}

}