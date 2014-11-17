<?php

class Prompt_Web_Api_Handling {

	/**
	 * Receive an ajax API pull updates request.
	 */
	public static function receive_pull_updates() {

		self::validate_or_die();

		$messenger = Prompt_Factory::make_inbound_messenger();

		self::set_return_code_and_die( $messenger->pull_updates() );
	}

	public static function receive_pull_configuration() {

		self::validate_or_die();

		$configurator = Prompt_Factory::make_configurator();

		self::set_return_code_and_die( $configurator->pull_configuration() );
	}

	protected static function validate_or_die() {
		if ( !self::validate_request() ) {
			status_header( 401 );
			wp_die();
		}
	}

	protected static function set_return_code_and_die( $status ) {
		if ( is_wp_error( $status ) )
			status_header( 500 );

		wp_die();
	}

	/**
	 * @return bool Whether request is valid.
	 */
	protected static function validate_request() {

		$timestamp = intval( $_REQUEST['timestamp'] );
		if (
			!isset( $_REQUEST['timestamp'] ) or
			!is_numeric( $_REQUEST['timestamp'] ) or
			abs( time() - $timestamp ) > 60*60*6
		) {
			Prompt_Logging::add_error(
				'inbound_invalid_timestamp',
				__( 'Rejected an inbound request with an invalid timestamp. Could be bot activity.', 'Postmatic' ),
				$_REQUEST
			);
			return false;
		}

		if ( empty( $_REQUEST['token'] ) ) {
			Prompt_Logging::add_error(
				'inbound_invalid_token',
				__( 'Rejected an inbound request with an invalid token. Could be bot activity.', 'Postmatic' ),
				$_REQUEST
			);
			return false;
		}
		$token = sanitize_key( $_REQUEST['token'] );

		if ( !isset( $_REQUEST['signature'] ) or strlen( $_REQUEST['signature'] ) != 64 ) {
			Prompt_Logging::add_error(
				'inbound_invalid_signature',
				__( 'Rejected an inbound request with an invalid signature. Could be bot activity.', 'Postmatic' ),
				$_REQUEST
			);
			return false;
		}

		$signature = $_REQUEST['signature'];
		if ( hash_hmac( 'sha256', $timestamp . $token, Prompt_Core::$options->get( 'prompt_key' ) ) != $signature ) {
			Prompt_Logging::add_error(
				'inbound_invalid_signature',
				__( 'Rejected an inbound request with an invalid signature. Could be bot activity.', 'Postmatic' ),
				$_REQUEST
			);
			return false;
		}

		return true;
	}

}