<?php
class Prompt_Subscription_Mailing {

	protected static $delivery_option = 'prompt_agreement_delivery';
	/** @var  array */
	protected static $delivery_index;

	/**
	 * Send an email to verify a subscription from a non-existent user.
	 *
	 * @param Prompt_Interface_Subscribable $object
	 * @param string $email_address
	 * @param array $user_data
	 * @param Prompt_Register_Subscribe_Command $resend_command
	 */
	public static function send_agreement( $object, $email_address, $user_data, $resend_command = null ) {

		$user_data['user_email'] = $email_address;

		$email = self::make_agreement_email( $object, $user_data, $resend_command );

		Prompt_Factory::make_mailer()->send_one( $email );

	}

	/**
	 * Send emails to verify subscriptions from non-existent users.
	 *
	 * Try to be idempotent, so only the first of repeated calls sends mail.
	 *
	 * @param Prompt_Interface_Subscribable $object
	 * @param array $users_data An array of user_data arrays that include the user_email field.
	 * @param array $template_data An array of data to provide to the subscription agreement template.
	 * @param int $chunk
	 * @param int $retry_wait_seconds Minimum time to wait if a retry is necessary, or null to disable retry
	 */
	public static function send_agreements(
		$object,
		$users_data,
		$template_data = array(),
		$chunk = 0,
		$retry_wait_seconds = 60
	) {

		// Bail if we've already sent this chunk
		if ( $chunk <= self::get_delivered_chunk( $users_data ) )
			return;

		// Block other processes from sending this chunk
		self::set_delivered_chunk( $users_data, $chunk );

		$chunks = array_chunk( $users_data, 30 );

		$emails = array();

		foreach ( $chunks[$chunk] as $user_data ) {
			$emails[] = self::make_agreement_email( $object, $user_data, null, $template_data );
		}

		$result = Prompt_Factory::make_mailer()->send_many( $emails );

		$rescheduler = new Prompt_Rescheduler( $result, $retry_wait_seconds );

		if ( $rescheduler->found_temporary_error() ) {

			self::set_delivered_chunk( $users_data, $chunk - 1 );

			$rescheduler->reschedule(
				'prompt/post_mailing/send_notifications',
				array( $object, $users_data, $template_data, $chunk )
			);

			return;
		}

		if ( is_wp_error( $result ) ) {

			self::set_delivered_chunk( $users_data, $chunk - 1 );

			Prompt_Logging::add_error(
				Prompt_Enum_Error_Codes::OUTBOUND,
				__( 'An email sending operation encountered a problem.', 'Postmatic' ),
				$result->get_error_data()
			);

			return;
		}

		if ( ! empty( $chunks[$chunk + 1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/subscription_mailing/send_agreements',
				array( $object, $users_data, $template_data, $chunk + 1 )
			);

		}
	}

	/**
	 * @since 1.3.0
	 *
	 * @param array $users_data
	 * @return int
	 */
	protected static function get_delivered_chunk( $users_data ) {

		if ( ! self::$delivery_index )
			self::$delivery_index = get_option( self::$delivery_option, array() );

		$key = md5( serialize( $users_data ) );

		return empty( $delivery[$key] ) ? -1 : $delivery[$key];
	}

	/**
	 * @since 1.3.0
	 *
	 * @param array $users_data
	 * @param int $chunk
	 */
	protected static function set_delivered_chunk( $users_data, $chunk ) {

		if ( ! self::$delivery_index )
			self::$delivery_index = get_option( self::$delivery_option, array() );

		$key = md5( serialize( $users_data ) );

		self::$delivery_index[$key] = $chunk;

		update_option( self::$delivery_option, self::$delivery_index, $autoload = false );
	}

	/**
	 * Schedule a batch of agreements to be sent.
	 *
	 * @since 1.3.0
	 *
	 * @param Prompt_Interface_Subscribable $object
	 * @param array $users_data An array of user_data arrays that include the user_email field.
	 * @param array $template_data An array of data to provide to the subscription agreement template.
	 */
	public static function schedule_agreements( $object, $users_data, $template_data = array() ) {

		$key = md5( serialize( $users_data ) );

		$delivery = get_option( self::$delivery_option, array() );

		// set to less than first chunk, 0
		$delivery[$key] = -1;

		update_option( self::$delivery_option, $delivery, $autoload = false );

		wp_schedule_single_event(
			time(),
			'prompt/subscription_mailing/send_agreements',
			array( $object, $users_data, $template_data, $chunk = 0 )
		);
	}

	protected static function make_agreement_email( $object, $user_data, $resend_command = null, $message_data = array() ) {
		$command = $resend_command;
		$resending = true;
		$email_address = $user_data['user_email'];

		if ( !$resend_command ) {
			$command = new Prompt_Register_Subscribe_Command();
			$resending = false;
			$command->save_subscription_data( $object, $email_address, $user_data );
		}

		$html_template = new Prompt_Email_Template( 'subscription-agreement-email.php' );
		$text_template = new Prompt_Text_Email_Template( 'subscription-agreement-email-text.php' );

		$message_data = array_merge( compact( 'email_address', 'object', 'user_data', 'resending' ), $message_data );
		/**
		 * Filter new user subscription verification email template data.
		 * @param array $message_data {
		 * @type Prompt_Interface_Subscribable $object The object being subscribed to
		 * @type string $email_address
		 * @type boolean $resending
		 * }
		 */
		$message_data = apply_filters( 'prompt/subscription_agreement_email/template_data', $message_data );

		$email = new Prompt_Email( array(
			'to_address' => $email_address,
			'subject' => sprintf(
				__( 'Please verify your subscription to %s', 'Postmatic' ),
				$object->subscription_object_label()
			),
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
		) );

		if ( !empty( $message_data['subject'] ) )
			$email->set_subject( $message_data['subject'] );

		if ( !empty( $message_data['message_type'] ) )
			$email->set_message_type( $message_data['message_type'] );

		if ( !empty( $message_data['from_name'] ) )
			$email->set_from_name( $message_data['from_name'] );

		self::render_email( $email, $text_template, $html_template, $message_data );

		Prompt_Command_Handling::add_command_metadata( $command, $email );

		/**
		 * Filter subscription verification email.
		 * @param Prompt_Email $email
		 * @param array $message_data {
		 * @type object $object The object being subscribed to
		 * @type string $email_address
		 * @type boolean $resending
		 * }
		 */
		return apply_filters( 'prompt/subscription_agreement_email', $email, $message_data );
	}

	/**
	 * Send an unsubscription confirmation email.
	 *
	 * @param int $subscriber_id
	 * @param Prompt_Interface_Subscribable $object
	 */
	public static function send_unsubscription_notification( $subscriber_id, $object ) {
		self::send_subscription_notification( $subscriber_id, $object, true );
	}

	/**
	 * Send a subscription confirmation email.
	 *
	 * @param int $subscriber
	 * @param Prompt_Interface_Subscribable $object
	 * @param boolean $un True if unsubscribing, default false.
	 */
	public static function send_subscription_notification( $subscriber, $object, $un = false ) {

		$prompt_subscriber = new Prompt_User( $subscriber );
		$subscriber = $prompt_subscriber->get_wp_user();

		$email = new Prompt_Email( array(
			'to_address' => $subscriber->user_email,
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
		) );

		if ( $un ) {

			$email->set_subject(
				sprintf( __( 'You\'re unsubscribed from %s', 'Postmatic' ), $object->subscription_object_label() )
			);
			$template_file = "unsubscribed-email.php";
			$filter = 'prompt/unsubscribed_email';
			$latest_post = null;
			$comments = array();

		} else {

			$email->set_subject(
				sprintf( __( 'You\'re subscribed to %s', 'Postmatic' ), $object->subscription_object_label() )
			);
			$template_file = "subscribed-email.php";
			$filter = 'prompt/subscribed_email';
			$latest_post = self::latest_post( $object );
			$comments = self::comments( $object );

		}

		$html_template = new Prompt_Email_Template( $template_file );
		$text_template = new Prompt_Text_Email_Template( str_replace( '.php', '-text.php', $template_file ) );

		$template_data = array(
			'subscriber' => $prompt_subscriber->get_wp_user(),
			'object' => $object,
			'latest_post' => $latest_post,
			'comments' => $comments,
			'subject' => $email->get_subject(),
		);
		/**
		 * Filter template data for subscription notification email.
		 *
		 * @param array $template_data {
		 *      Data supplied to the subscription notification email template.
		 *
		 *      @type WP_User $object The object subscribed to
		 *      @type Prompt_Interface_Subscribable $object The object subscribed to
		 *      @type WP_Post $latest_post For site and author subscriptions, the latest relevant post.
		 *      @type array $comments For post subscriptions, the comments on the post so far.
		 * }
		 */
		$template_data = apply_filters( $filter . '/template_data', $template_data );

		if ( $latest_post )
			setup_postdata( $GLOBALS['post'] = $latest_post );

		$post_id = 0;
		if ( $latest_post or is_a( $object, 'Prompt_Post' ) )
			$post_id = $latest_post ? $latest_post->ID : $object->id();

		$command = new Prompt_Confirmation_Command();
		$command->set_post_id( $post_id );
		$command->set_user_id( $subscriber->ID );
		$command->set_object_type( get_class( $object ) );
		$command->set_object_id( $object->id() );

		Prompt_Command_Handling::add_command_metadata( $command, $email );

		self::render_email( $email, $text_template, $html_template, $template_data );

		if ( $latest_post )
			wp_reset_postdata();

		/**
		 * Filter subscription notification email.
		 *
		 * @param Prompt_Email $email
		 * @param array $template_data @see prompt/subscribed_email/template_data
		 */
		$email = apply_filters( $filter, $email, $template_data );

		Prompt_Factory::make_mailer()->send_one( $email );
	}

	/**
	 * Send a rejoin confirmation email.
	 *
	 * @param int $subscriber
	 * @param Prompt_Post $prompt_post
	 */
	public static function send_rejoin_notification( $subscriber, $prompt_post ) {

		$prompt_subscriber = new Prompt_User( $subscriber );
		$subscriber = $prompt_subscriber->get_wp_user();

		$email = new Prompt_Email( array(
			'to_address' => $subscriber->user_email,
			'message_type' => Prompt_Enum_Message_Types::SUBSCRIPTION,
		) );

		$email->set_subject(
			sprintf( __( 'You\'ve rejoined %s', 'Postmatic' ), $prompt_post->subscription_object_label() )
		);
		$comments = self::comments( $prompt_post );

		$html_template = new Prompt_Email_Template( 'rejoined-email.php' );
		$text_template = new Prompt_Text_Email_Template( 'rejoined-email-text.php' );

		$template_data = array(
			'subscriber' => $subscriber,
			'object' => $prompt_post,
			'comments' => $comments,
			'subject' => $email->get_subject(),
		);
		/**
		 * Filter template data for subscription notification email.
		 *
		 * @param array $template_data {
		 *      Data supplied to the subscription notification email template.
		 *
		 *      @type WP_User $subscriber
		 *      @type Prompt_Interface_Subscribable $prompt_post The object subscribed to
		 *      @type array $comments The comments since flood control was triggered.
		 *      @type string $subject
		 * }
		 */
		$template_data = apply_filters( 'prompt/rejoined_email/template_data', $template_data );

		self::add_comment_command( $email, $prompt_post->id(), $subscriber->ID );

		self::render_email( $email, $text_template, $html_template, $template_data );

		/**
		 * Filter subscription notification email.
		 *
		 * @param Prompt_Email $email
		 * @param array $template_data @see prompt/rejoined_email/template_data
		 */
		$email = apply_filters( 'prompt/rejoined_email', $email, $template_data );

		Prompt_Factory::make_mailer()->send_one( $email );
	}

	protected static function add_comment_command( Prompt_Email $email, $post_id, $subscriber_id ) {
		$command = new Prompt_Comment_Command();
		$command->set_post_id( $post_id );
		$command->set_user_id( $subscriber_id );
		Prompt_Command_Handling::add_command_metadata( $command, $email );
	}

	protected static function render_email(
		Prompt_Email $email,
		Prompt_Text_Email_Template $text_template,
		Prompt_Email_Template $html_template,
		$template_data
	) {
		$email->set_text( $text_template->render( $template_data ) );
		$email->set_html( $html_template->render( $template_data ) );
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @return WP_Post
	 */
	protected static function latest_post( Prompt_Interface_Subscribable $object ) {

		if ( is_a( $object, 'Prompt_Post' ) )
			return null;

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) )
			return null;

		$query = array(
			'posts_per_page' => 1,
			'post_type' => Prompt_Core::$options->get( 'site_subscription_post_types' ),
			'meta_query' => array(
				Prompt_Post::sent_posts_meta_clause(),
			)
		);

		if ( is_a( $object, 'Prompt_User' ) )
			$query['post_author'] = $object->id();

		$posts = get_posts( $query );

		if ( empty( $posts ) )
			return null;

		return $posts[0];
	}

	protected static function comments( Prompt_Interface_Subscribable $object ) {

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) )
			return array();

		if ( ! is_a( $object, 'Prompt_Post' ) )
			return array();

		return get_comments( array(
			'post_id' => $object->id(),
			'status' => 'approve',
			'order' => 'ASC',
		) );
	}

}