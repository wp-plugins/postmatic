<?php

class Prompt_Subscription_Mailing {

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
	 * @param Prompt_Interface_Subscribable $object
	 * @param array $users_data An array of user_data arrays that include the user_email filed.
	 * @param array $template_data An array of data to provide to the subscription agreement template.
	 * @param int $chunk
	 */
	public static function send_agreements( $object, $users_data, $template_data = array(), $chunk = 0 ) {

		$emails = array();

		$chunks = array_chunk( $users_data, 25 );

		foreach ( $chunks[$chunk] as $user_data ) {
			$emails[] = self::make_agreement_email( $object, $user_data, null, $template_data );
		}

		Prompt_Factory::make_mailer( Prompt_Enum_Email_Transports::API )->send_many( $emails );

		if ( !empty( $chunks[$chunk + 1] ) ) {

			wp_schedule_single_event(
				time(),
				'prompt/subscription_mailing/send_agreements',
				array( $object, $users_data, $template_data, $chunk + 1 )
			);

		}
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

		$template = Prompt_Template::locate( 'subscription-agreement-email.php' );

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

		if ( !empty( $message_data['subject'] ) )
			$subject = $message_data['subject'];
		else
			$subject = sprintf( __( 'Please verify your subscription to %s', 'Prompt_Core' ), $object->subscription_object_label() );

		$email = new Prompt_Email( array(
			'to_address' => $email_address,
			'subject' => $subject,
			'message' => Prompt_Template::render( $template, $message_data, false ),
			'template' => 'html-email-no-widgets.php',
		) );

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
			'template' => 'html-email-no-widgets.php',
		) );

		if ( $un ) {

			$email->set_subject(
				sprintf( __( 'You\'re unsubscribed from %s', 'Prompt_Core' ), $object->subscription_object_label() )
			);
			$template = "unsubscribed-email.php";
			$filter = 'prompt/unsubscribed_email';
			$latest_post = null;

		} else {

			$email->set_subject(
				sprintf( __( 'You\'re subscribed to %s', 'Prompt_Core' ), $object->subscription_object_label() )
			);
			$template = "subscribed-email.php";
			$filter = 'prompt/subscribed_email';
			$latest_post = self::get_latest_post( $object );

		}

		$template = Prompt_Template::locate( $template );
		$template_data = array(
			'subscriber' => $prompt_subscriber->get_wp_user(),
			'object' => $object,
			'latest_post' => $latest_post,
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
		 * }
		 */
		$template_data = apply_filters( $filter . '/template_data', $template_data );

		if ( $latest_post ) {
			setup_postdata( $GLOBALS['post'] = $latest_post );
			$command = new Prompt_Comment_Command();
			$command->set_post_id( $latest_post->ID );
			$command->set_user_id( $subscriber->ID );
			Prompt_Command_Handling::add_command_metadata( $command, $email );
		}

		$email->set_message( Prompt_Template::render( $template, $template_data, false ) );

		if ( $latest_post )
			wp_reset_postdata();

		/**
		 * Filter subscription notification email.
		 *
		 * @param Prompt_Email $email
		 * @param array $template_data @see prompt/subscribed_email/template_data
		 * @type Prompt_Interface_Subscribable $object The object subscribed to
		 * }
		 */
		$email = apply_filters( $filter, $email, $template_data );

		$mailer = Prompt_Factory::make_mailer();

		$mailer->send_one( $email );
	}

	/**
	 * @param Prompt_Interface_Subscribable $object
	 * @return WP_Post
	 */
	protected static function get_latest_post( Prompt_Interface_Subscribable $object ) {

		if ( is_a( $object, 'Prompt_Post' ) )
			return null;

		$query = array(
			'posts_per_page' => 1,
			'post_type' => Prompt_Core::$options->get( 'site_subscription_post_types' ),
		);

		if ( is_a( $object, 'Prompt_User' ) )
			$query['post_author'] = $object->id();

		$posts = get_posts( $query );

		if ( empty( $posts ) )
			return null;

		return $posts[0];
	}
}