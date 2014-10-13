<?php

class Prompt_Inbound_Messenger {

	protected $client;

	public function __construct( Prompt_Interface_Http_Client $client = null ) {
		$this->client = $client ? $client : new Prompt_Api_Client();
	}

	/**
	 * Check for new messages.
	 * @return boolean|WP_Error status
	 */
	public function pull_updates() {

		$response = $this->client->get( '/updates/undelivered' );

		if ( is_wp_error( $response ) or 200 != $response['response']['code'] )
			return Prompt_Logging::add_error(
				'pull_updates_http',
				__( 'A request for inbound messages failed.', 'Prompt_Core' ),
				$response
			);

		$data = json_decode( $response['body'] );

		if ( !isset( $data->updates ) )
			return Prompt_Logging::add_error(
				'pull_updates_empty',
				__( 'Inbound messages arrived in an unrecognized format.', 'Prompt_Core' ),
				$data
			);

		if ( empty( $data->updates ) )
			return true;

		$result_updates = array();
		foreach ( $data->updates as $update ) {
			$result = array( 'id' => $update->id );
			$result['status'] = $this->process_update( $update );
			$result_updates[] = $result;
		}
		$results = array( 'updates' => $result_updates );

		$request = array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => json_encode( $results ),
		);

		$response = $this->client->put( '/updates', $request );

		if ( is_wp_error( $response ) or 200 != $response['response']['code'] ) {
			//TODO: schedule a retry
			return Prompt_Logging::add_error(
				'updates_put_http',
				__( 'Failed to acknowledge receipt of messages - they may arrive again.', 'Prompt_Core' ),
				compact( 'response', 'results' )
			);
		}

		return true;
	}

	/**
	 * Process updates according to their type.
	 * @param object $update
	 * @return string Status: 'delivered', 'lost'
	 */
	public function process_update( $update ) {

		if ( 'inbound-email' == $update->type )
			return $this->process_inbound_email( $update );

		Prompt_Logging::add_error(
			'unknown_update_type',
			__( 'Unable to deliver a message of unknown type.', 'Prompt_Core' ),
			$update
		);

		return 'lost';
	}

	/**
	 * Process a incoming email POST request.
	 * @param object $update The email data
	 * @return array Representation of processing results.
	 */
	public function process_inbound_email( $update ) {

		$command = Prompt_Command_Handling::make_command( $update->data );

		if ( !$command )
			return null;

		$command->execute();

		return 'delivered';
	}

}