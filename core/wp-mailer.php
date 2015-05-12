<?php

class Prompt_Wp_Mailer extends Prompt_Mailer {

	/** @var bool Timeouts on local outbounds will not complete successfully */
	protected $ignore_timeouts = false;
	/** @var  PHPMailer */
	protected $local_mailer;

	public function __construct( Prompt_Interface_Http_Client $client = null ) {
		parent::__construct( $client );

		if ( func_num_args() > 1 ) {
			$this->local_mailer = func_get_arg( 1 );
		} else {
			$this->local_mailer = $this->get_phpmailer();
		}
	}

	public function send_one( Prompt_Email $email ) {

		$results = new stdClass();

		if ( $email->get_metadata() )
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

		if ( $emails[0]->get_metadata() )
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

		$this->local_mailer->clearAllRecipients();
		$this->local_mailer->clearCustomHeaders();
		$this->local_mailer->clearReplyTos();

		$this->local_mailer->From = $email->get_from_address();
		$this->local_mailer->FromName =  $email->get_from_name();

		$this->local_mailer->addAddress( $email->get_to_address(), $email->get_to_name() );

		if ( $email->get_reply_address() )
			$this->local_mailer->addReplyTo( $email->get_reply_address(), $email->get_reply_name() );

		$this->local_mailer->Subject = $email->get_subject();

		$this->local_mailer->Body = $email->get_html();
		$this->local_mailer->AltBody = $email->get_text();
		$this->local_mailer->ContentType = Prompt_Enum_Content_Types::HTML;

		$this->local_mailer->isMail();

		$this->local_mailer->CharSet = 'UTF-8';

		foreach ( $email->get_file_attachments() as $file_attachment ) {
			$this->local_mailer->addAttachment( $file_attachment );
		}

		try {
			$this->local_mailer->send();
		} catch ( phpmailerException $e ) {
			Prompt_Logging::add_error(
				'prompt_wp_mail',
				__( 'Failed sending an email locally. Did you know Postmatic can deliver email for you?', 'Prompt_Core' ),
				array( 'email' => $email, 'error_info' => $this->local_mailer->ErrorInfo )
			);
			return false;
		}

		return true;
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

		// currently we only track replies locally
		$actions = in_array( 'track-replies', $actions ) ? array( 'track-replies' ) : array();

		$results = $this->prompt_outbound( $emails, $actions );

		if ( is_wp_error( $results ) ) {
			// Already logged
			return $results;
		}

		$result_messages = $results->outboundMessages;

		for( $i = 0; $i < count( $result_messages ); $i += 1 ) {
			$emails[$i]->set_reply_address( Prompt_Email::address( $result_messages[$i]->reply_to ) );
			$emails[$i]->set_reply_name( Prompt_Email::name( $result_messages[$i]->reply_to ) );
		}

		return $results;
	}

	protected function get_phpmailer() {
		if ( !class_exists( 'PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			require_once ABSPATH . WPINC . '/class-smtp.php';
		}
		return new PHPMailer( true );
	}

}