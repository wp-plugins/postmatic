<?php

/**
 * Pass significant occurrences on to the events API.
 */
class Prompt_Event_Handling {
	const URL = 'https://events.gopostmatic.com/api/v1';

	public static function record_deactivation() {

		$key = Prompt_Core::$options->get( 'prompt_key' );
		if ( !$key )
			return;

		self::record_event( time(), 'activated', compact( 'key' ), $key, self::URL );

	}

	public static function record_reactivation() {

		// Core options not yet available
		$options = get_option( 'prompt_options' );

		if ( !$options )
			return;

		$key = $options['prompt_key'];

		if ( !$key )
			return;

		self::record_event( time(), 'activated', compact( 'key' ), $key, self::URL );

	}

	public static function record_environment() {

		$environment = new Prompt_Environment();

		self::record_event( time(), 'environment', $environment->to_array() );

	}

	protected static function record_event( $timestamp, $code, $data, $key = '', $url = '' ) {

		$key = $key ? $key : Prompt_Core::$options->get( 'prompt_key' );

		$url = $url ? $url : self::URL;

		$client = new Prompt_Api_Client( array(), $key, $url );

		$body = array( 'events' => array( compact( 'timestamp', 'code', 'data' ) ) );

		$client->post( '/events', array( 'body' => json_encode( $body ) ) );

	}
}