<?php

class Prompt_Mailer {

	/** @var \Prompt_Api_Client|\Prompt_Interface_Http_Client  */
	protected $client;

	/** @var bool Timeouts on API outbounds will probably complete successfully */
	protected $ignore_timeouts = true;

	public function __construct( Prompt_Interface_Http_Client $client = null ) {
		$this->client = $client ? $client : new Prompt_Api_Client();
	}

	/**
	 * @param Prompt_Email $email
	 * @return object|WP_Error transport dependent result
	 */
	function send_one( Prompt_Email $email ) {
		$emails = array( $email );
		$actions = $this->implied_actions( $emails );
		$actions[] = 'send-email';

		return $this->prompt_outbound( $emails, $actions );
	}

	/**
	 * @param Prompt_Email[] $emails
	 * @return object|WP_Error transport dependent results
	 */
	function send_many( array $emails ) {
		$actions = $this->implied_actions( $emails );
		$actions[] = 'send-email';

		return $this->prompt_outbound( $emails, $actions );
	}

	/**
	 * Submit emails to the server for processing.
	 *
	 * @param Prompt_Email[] $emails
	 * @param array $actions Array of at least one of 'send-email', 'inline-styles', 'track-replies'
	 * @return object|WP_Error results
	 */
	protected function prompt_outbound( array $emails, array $actions ) {

		$email_data = new stdClass();

		if ( empty( $actions ) or empty( $emails ) )
			return $email_data;

		$email_data->actions = $actions;
		$email_data->outboundMessages = array();

		foreach ( $emails as $email ) {
			$email_data->outboundMessages[] = $this->make_prompt_message( $email );
		}

		$request = array(
			'body' => json_encode( $email_data ),
			'headers' => array( 'Content-Type' => 'application/json' ),
		);

		$response = $this->client->post( '/outbound_messages', $request );

		if ( is_wp_error( $response ) )
			return $response;

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error(
				Prompt_Enum_Error_Codes::API,
				wp_remote_retrieve_response_message( $response ),
				$response
			);
		}

		$results = json_decode( $response['body'] );

		if ( !isset( $results->outboundMessages ) or count( $results->outboundMessages ) != count( $emails ) )
			return Prompt_Logging::add_error(
				'invalid_outbound_results',
				__( 'An email sending operation behaved erratically and may have failed.', 'Postmatic' ),
				compact( 'email_data', 'results' )
			);

		// TODO: also get a status on messages?

		return $results;
	}


	/**
	 * Format an email for the prompt outbound service.
	 *
	 * @param Prompt_Email $email
	 * @return array
	 */
	protected function make_prompt_message( Prompt_Email $email ) {

		$message = array(
			'to' => array( 'address' => $email->get_to_address(), 'name' => $email->get_to_name() ),
			'from' => array( 'address' => $email->get_from_address(), 'name' => $email->get_from_name() ),
			'subject' => $email->get_subject(),
			'html_content' => $email->get_html(),
			'text_content' => $email->get_text(),
			'type' => $email->get_message_type(),
		);

		if ( $email->get_reply_address() )
			$message['reply-to'] = array( 'address' => $email->get_reply_address(), 'name' => $email->get_reply_name() );

		if ( $email->get_metadata() )
			$message['metadata'] = $email->get_metadata();

		return $message;
	}

	/**
	 * Get actions implied in emails.
	 *
	 * If any emails have content type text/html, 'inline-styles' is included.
	 *
	 * If any emails have metadata, 'track-replies' is included.
	 *
	 * @param Prompt_Email[] $emails
	 * @return array implied actions
	 */
	protected function implied_actions( $emails ) {
		$actions = array();

		foreach ( $emails as $email ) {

			if ( $email->have_html() and !in_array( 'inline-styles', $actions ) )
				$actions[] = 'inline-styles';

			if ( $email->get_metadata() and !in_array( 'track-replies', $actions ) )
				$actions[] = 'track-replies';

			if ( count( $actions ) == 2 )
				break;

		}

		return $actions;
	}

}