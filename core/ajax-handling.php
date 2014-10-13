<?php

/**
 * Handle Ajax Requests
 */
class Prompt_Ajax_Handling {
	const AJAX_NONCE = 'prompt_subscribe';

	/**
	 * Handle subscription ajax requests from the subscribe widget.
	 */
	static public function action_wp_ajax_prompt_subscribe() {

		$validity = self::validate_subscribe_request();
		if ( $validity !== true )
			wp_die( $validity );

		$subscriber = wp_get_current_user();

		$object_id = intval( $_POST['object_id'] );
		$object_type = sanitize_text_field( $_POST['object_type'] );
		$email = isset( $_POST['subscribe_email'] ) ? sanitize_email( $_POST['subscribe_email'] ) : null;
		$name = isset( $_POST['subscribe_name'] ) ? sanitize_text_field( $_POST['subscribe_name'] ) : null;
		$confirm_unsubscribe = isset( $_POST['confirm_unsubscribe'] ) ? true : null;

		/** @var Prompt_Interface_Subscribable $object */
		$object = new $object_type( $object_id );

		$found_by_email = false;

		if ( !$subscriber->exists() and $email ) {
			self::set_subscriber_cookies( $email, $name );
			$subscriber = get_user_by( 'email', $email );
			$found_by_email = (bool)$subscriber;
		}

		if ( !$subscriber and !$found_by_email and $confirm_unsubscribe ) {
			printf( __( '%s is not subscribed to %s.', 'Prompt_Core' ), $email, $object->subscription_object_label() );
			wp_die();
		}

		if ( !$found_by_email and $email and !$confirm_unsubscribe ) {
			echo self::verify_new_subscriber( $object, $email, $name );
			wp_die();
		}

		if ( $object->is_subscribed( $subscriber->ID ) and $found_by_email and !$confirm_unsubscribe ) {
			echo self::confirm_unsubscribe( $email );
			wp_die();
		}

		if ( $object->is_subscribed( $subscriber->ID ) ) {
			echo self::unsubscribe( $object, $subscriber, $found_by_email );
			wp_die();
		}

		if ( $confirm_unsubscribe ) {
			printf( __( '%s is not subscribed to %s.', 'Prompt_Core' ), $email, $object->subscription_object_label() );
			wp_die();
		}

		echo self::subscribe( $object, $subscriber );
		wp_die();
	}

	/**
	 * Handle commenter requests from the invite settings tab
	 */
	static public function action_wp_ajax_prompt_get_commenters() {
		/** @var WPDB $wpdb */
		global $wpdb;

		$query = "SELECT MAX( c.comment_author ) as name, " .
			"c.comment_author_email as address, " .
			"MAX( c.comment_date ) as date, " .
			"COUNT( c.comment_author_email ) as count " .
			"FROM {$wpdb->comments} c " .
			"WHERE c.user_id = 0 " .
			"AND c.comment_type = '' " .
			"AND c.comment_approved = 1 " .
			"AND c.comment_author_email <> '' " .
			"AND NOT EXISTS( SELECT 1 FROM {$wpdb->users} WHERE user_email = c.comment_author_email )" .
			"AND NOT EXISTS( " .
				"SELECT 1 FROM {$wpdb->comments} pc " .
				"WHERE pc.comment_author_email = c.comment_author_email AND pc.comment_type = 'prompt_pre_reg' )" .
			"GROUP BY c.comment_author_email ";

		$results = $wpdb->get_results( $query );

		wp_send_json( $results );
	}

	/**
	 * Handle unsubscribe requests from the comment form.
	 */
	public static function action_wp_ajax_prompt_comment_unsubscribe() {

		if ( !wp_verify_nonce( $_POST['nonce'], self::AJAX_NONCE ) )
			wp_die( -1 );

		$post_id = absint( $_POST['post_id'] );

		if ( !$post_id )
			wp_die( 0 );

		$current_user = Prompt_User_Handling::current_user();

		$prompt_post = new Prompt_Post( $post_id );

		if ( !$current_user or !$prompt_post->is_subscribed( $current_user->ID ) )
			wp_die( 0 );

		$prompt_post->unsubscribe( $current_user->ID );

		_e( 'You have unsubscribed.', 'Prompt_Core' );

		wp_die();
	}

	/**
	 * Handle post editor delivery status requests.
	 */
	public static function action_wp_ajax_prompt_post_delivery_status() {

		$post_id = absint( $_GET['post_id'] );

		if ( !$post_id )
			wp_die( 0 );

		$prompt_post = new Prompt_Post( $post_id );

		$recipient_count = count( $prompt_post->recipient_ids() );
		$sent_count = count( $prompt_post->sent_recipient_ids() );

		if ( $recipient_count == 0 ) {

			$description = __( 'No emails will be sent for this post.', 'Prompt_Core' );

		} else if ( $sent_count == 0 and 'publish' != $prompt_post->get_wp_post()->post_status ) {

			$description = sprintf(
				__( 'This post will be sent to %d subscribers.', 'Prompt_Core' ),
				$recipient_count
			);

		} else if ( $sent_count == 0 ) {

			$description = __( 'No emails have been sent for this post.', 'Prompt_Core' );

		} else {

			$description = sprintf(
				__( 'This post has been sent to %d subscribers.', 'Prompt_Core' ),
				$sent_count
			);

		}

		wp_send_json( compact( 'description', 'sent_count', 'recipient_count' ) );
	}

	/**
	 * Handle post editor preview email requests.
	 */
	public static function action_wp_ajax_prompt_post_delivery_preview() {
		$post_id = absint( $_GET['post_id'] );

		if ( !$post_id )
			wp_die( 0 );

		$post = get_post( $post_id );

		if ( Prompt_Admin_Delivery_Metabox::suppress_featured_image() ) {
			$featured_image_src = false;
		} else {
			$featured_image = image_get_intermediate_size( get_post_thumbnail_id( $post_id ), 'prompt-post-featured' );
			$featured_image_src = array( $featured_image['url'], $featured_image['width'], $featured_image['height'] );
		}

		// Set up global post data for use in the email template
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		$email = Prompt_Post_Mailing::build_email( array(
			'prompt_author' => new Prompt_User( $post->post_author ),
			'recipient' => wp_get_current_user(),
			'prompt_post' => new Prompt_Post( $post ),
			'subscribed_object' => new Prompt_Site(),
			'featured_image_src' => $featured_image_src,
		) );

		Prompt_Factory::make_mailer()->send_one( $email );

		wp_send_json( array( 'message' => __( 'Preview email sent.', 'Prompt_Core' ) ) );
	}

	/**
	 * @param string $email
	 * @param string $name
	 */
	protected static function set_subscriber_cookies( $email, $name ) {
		$commenter = wp_get_current_commenter();

		$comment = new stdClass();
		$comment->comment_author = $name;
		$comment->comment_author_email = $email;
		$comment->comment_author_url = $commenter['comment_author_url'];

		wp_set_comment_cookies( $comment, wp_get_current_user() );
	}

	/**
	 * @return bool|int|string True if valid, a message if correctable, otherwise -1.
	 */
	protected static function validate_subscribe_request() {

		if ( !wp_verify_nonce( $_POST['subscribe_nonce'], self::AJAX_NONCE ) ) {
			$message = sprintf(
				'Postmatic subscribe bad nonce request %s post data %s.',
				json_encode( $_SERVER ),
				json_encode( $_POST )
			);
			trigger_error( $message, E_USER_NOTICE );
			return -1;
		}

		if ( !isset( $_POST['subscribe_topic'] ) or !empty( $_POST['subscribe_topic'] ) ) {
			$message = sprintf(
				'Postmatic subscribe bad topic request %s post data %s.',
				json_encode( $_SERVER ),
				json_encode( $_POST )
			);
			trigger_error( $message, E_USER_NOTICE );
			return -1;
		}

		if ( isset( $_POST['subscribe_email'] ) and is_email( $_POST['subscribe_email'] ) === false ) {
			return html( 'div class="error"', __( 'Sorry, that email address is not valid.', 'Prompt_Core' ) );
		}

		return true;
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @param string $email
	 * @param string $name
	 * @return string
	 */
	protected static function verify_new_subscriber( $object, $email, $name ) {

		$display_name = sanitize_text_field( $name );
		$name_words = explode( ' ', trim( $name ) );
		$first_name = array_shift( $name_words );
		$last_name = empty( $name_words ) ? '' : implode( ' ', $name_words );

		$user_data = compact( 'first_name', 'last_name', 'display_name' );

		Prompt_Subscription_Mailing::send_agreement( $object, $email, $user_data );

		$message = html( 'strong',
			__( 'Almost done - you\'ll receive an email with instructions to complete your subscription.', 'Prompt_Core' ),
			' '
		)  ;

		/**
		 * Filter the account created Ajax message.
		 *
		 * @param string $message
		 * @param string $email
		 * @param array $user_data
		 */
		return apply_filters( 'prompt/ajax/subscription_verification_message', $message, $email, $user_data );
	}

	protected static function confirm_unsubscribe( $email ) {
		return sprintf( __( 'Are you sure you want to unsubscribe %s?', 'Prompt_Core' ), $email ) .
			'<br />' .
			html( 'a href="."', html( 'small', __( 'Not you? Click here to start over', 'Prompt_Core' ) ) ) .
			html( 'input', array( 'type' => 'hidden', 'name' => 'confirm_unsubscribe', 'value' => 'Unsubscribe', ) ) .
			html( 'input', array( 'type' => 'submit', 'value' => __( 'Unsubscribe', 'Prompt_Core' ) ) );
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @param WP_User $subscriber
	 * @param boolean $found_by_email
	 * @return string Step response message
	 */
	protected static function unsubscribe( $object, $subscriber, $found_by_email ) {

		$object->unsubscribe( $subscriber->ID );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $subscriber->ID, $object );

		return __( 'You have unsubscribed.', 'Prompt_Core' );
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @param WP_User $subscriber
	 * @return string Response
	 */
	protected static function subscribe( $object, $subscriber ) {

		$object->subscribe( $subscriber->ID );

		Prompt_Subscription_Mailing::send_subscription_notification( $subscriber->ID, $object );

		return __( '<strong>Confirmation email sent. Please check your email for further instructions.</strong>', 'Prompt_Core' );
	}

}