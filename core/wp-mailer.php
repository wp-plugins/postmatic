<?php

class Prompt_Wp_Mailer extends Prompt_Mailer {

	/** @var bool Timeouts on local outbounds will not complete successfully */
	protected $ignore_timeouts = false;

	public function send_one( Prompt_Email $email ) {

		$results = new stdClass();

		if ( Prompt_Enum_Content_Types::HTML == $email->get_content_type() or $email->get_metadata() )
			$results = $this->prepare_one( $email );

		if ( is_wp_error( $results ) )
			return $results;

		return $this->send_prepared( $email );
	}

	/**
	 * @param Prompt_Email[] $emails
	 * @return object|WP_Error
	 */
	public function send_many( array $emails ) {
		if ( empty( $emails ) )
			return false;

		$results = new stdClass();

		if ( Prompt_Enum_Content_Types::HTML == $emails[0]->get_content_type() or $emails[0]->get_metadata() )
			$results = $this->prepare_many( $emails );

		if ( is_wp_error( $results ) )
			return $results;

		$results->messages = array();
		foreach ( $emails as $email ) {
			$results->messages[] = $this->send_prepared( $email );
		}
		return $results;
	}

	protected function send_prepared( Prompt_Email $email ) {
		// beware changing content type if retrieve_password() is used: http://core.trac.wordpress.org/ticket/23578
		add_filter( 'wp_mail_content_type', array( $email, 'get_content_type' ) );

		$to = Prompt_Email::name_address( $email->get_to_address(), $email->get_to_name() );

		$headers = array();

		if ( $email->get_from_address() )
			$headers[] = 'from: ' . Prompt_Email::name_address( $email->get_from_address(), $email->get_from_name() );

		if ( $email->get_reply_address() )
			$headers[] = 'reply-to: ' . Prompt_Email::name_address( $email->get_reply_address(), $email->get_reply_name() );

		$sent = wp_mail(
			$to,
			$email->get_subject(),
			$email->get_rendered_message(),
			$headers,
			$email->get_file_attachments()
		);

		if ( !$sent )
			Prompt_Logging::add_error(
				'prompt_wp_mail',
				__( 'WordPress failed sending an email. Did you know Postmatic can deliver email for you?', 'Prompt_Core' ),
				array( 'email' => $email, 'error_info' => $GLOBALS['phpmailer']->ErrorInfo )
			);

		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		return $sent;
	}

	protected function prepare_one( &$email ) {
		$emails = array( $email );
		return $this->prepare_many( $emails );
	}

	/**
	 * @param Prompt_Email[] $emails
	 * @return object|WP_Error
	 */
	protected function prepare_many( array &$emails ) {

		$actions = $this->implied_actions( $emails );

		$results = $this->prompt_outbound( $emails, $actions );

		if ( is_wp_error( $results ) ) {
			// Already logged
			return $results;
		}

		$result_messages = $results->outboundMessages;

		for( $i = 0; $i < count( $result_messages ); $i += 1 ) {

			if ( in_array( 'inline-styles', $actions ) )
				$emails[$i]->set_rendered_message( $result_messages[$i]->message );

			$emails[$i]->set_reply_address( Prompt_Email::address( $result_messages[$i]->reply_to ) );
			$emails[$i]->set_reply_name( Prompt_Email::name( $result_messages[$i]->reply_to ) );
		}

		return $results;
	}

}