<?php

class Prompt_Logging {
	const OPTION_NAME = 'prompt_log';

	/**
	 * Save the most recent errors for review.
	 * @param string $code
	 * @param string $message
	 * @param mixed $data
	 * @return WP_Error
	 */
	public static function add_error( $code = '', $message = '', $data = array() ) {

		$wp_error = new WP_Error( $code, $message, $data );

		$log = self::get_log();

		if ( !$log )
			$log = array();

		// If we go over 25 messages, only keep the most recent 20
		if ( count( $log ) > 25 )
			$log = array_slice( $log, 0, 20 );

		$time = time();

		array_unshift( $log, compact( 'time', 'code', 'message', 'data' ) );

		update_option( self::OPTION_NAME, $log );

		// Puke a little in dev environments
		trigger_error( $message, E_USER_NOTICE );

		return $wp_error;
	}

	/**
	 * Get saved error log entries.
	 * @param int $since Include only entries more recent than this timestamp.
	 * @param string $data_format Specify ARRAY_A to convert data to array format.
	 * @return array
	 */
	public static function get_log( $since = 0, $data_format = OBJECT ) {
		$log = get_option( self::OPTION_NAME );

		if ( !is_array( $log ) )
			$log = json_decode( $log );

		if ( !$log ) {
			$log = array();
			add_option( self::OPTION_NAME, $log, '', $autoload = 'no' );
		}

		$filtered_log = array();

		foreach ( $log as $entry ) {
			$entry = (array) $entry;

			if ( $data_format == ARRAY_A )
				$entry['data'] = self::object_to_array( $entry['data'] );

			if ( $entry['time'] >= $since )
				$filtered_log[] = $entry;
		}

		return $filtered_log;
	}

	public static function delete_log() {
		delete_option( self::OPTION_NAME );
	}

	protected static function object_to_array( $obj ) {
		if ( is_object( $obj ) ) {
			$obj = (array)$obj;
		}
		if ( is_array( $obj ) ) {
			$new = array();
			foreach ( $obj as $key => $val ) {
				$new[$key] = self::object_to_array( $val );
			}
		} else {
			$new = $obj;
		}
		return $new;
	}
}