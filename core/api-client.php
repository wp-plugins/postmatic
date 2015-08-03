<?php

/**
 * A client for Prompt web services.
 *
 * Decorates the native WordPress HTTP function wp_remote_request().
 */
class Prompt_Api_Client implements Prompt_Interface_Http_Client {

	protected $key;
	protected $base_url;
	protected $defaults;
	protected $implementation;

	/**
	 * @see wp_remote_request()
	 * @param array $defaults           Optional defaults applied to all requests, see wp_remote_request().
	 * @param null $key                 Optional Prompt key. Defaults to the saved prompt_key option.
	 * @param null $base_url            Optional base API URL. Defaults to https://api.gopostmatic.com/api/v1.
	 * @param string $implementation    Optional decorator target. Defaults to wp_remote_request.
	 */
	public function __construct( $defaults = array(), $key = null, $base_url = null, $implementation = 'wp_remote_request' ) {
		$default_url = defined( 'PROMPT_API_URL' ) ? PROMPT_API_URL : 'https://app.gopostmatic.com/api/v1';
		$this->key = $key ? $key : Prompt_Core::$options->get( 'prompt_key' );
		$this->base_url = $base_url ? $base_url : $default_url;
		$this->defaults = $defaults;
		$this->implementation = $implementation;
	}

	public function send( $endpoint, $request = array() ) {

		$url = $this->make_url( $endpoint );

		$request = wp_parse_args( $request, $this->defaults );

		if ( !isset( $request['headers'] ) )
			$request['headers'] = array();

		if ( !isset( $request['headers']['Authorization'] ) )
			$request['headers']['Authorization'] = 'Basic ' . base64_encode( 'api:' . $this->key );

		if ( !isset( $request['headers']['X-Prompt-Core-Version'] ) )
			$request['headers']['X-Prompt-Core-Version'] = Prompt_Core::version( $full = true );

		// TODO: decrease timeout when traffic allows
		if ( !isset( $request['timeout'] ) )
			$request['timeout'] = 15;

		$reply = call_user_func( $this->implementation, $url, $request );

		if ( !is_wp_error( $reply ) and isset( $reply['response']['code'] ) and 400 == $reply['response']['code'] )
			Prompt_Core::$options->set( 'upgrade_required', true );

		return $reply;
	}

	public function get( $endpoint, $request = array() ) {
		$request['method'] = 'GET';
		return $this->send( $endpoint, $request );
	}

	public function post( $endpoint, $request = array() ) {
		$request['method'] = 'POST';

		if ( !isset( $request['headers']['Content-Type'] ) )
			$request['headers']['Content-Type'] = 'application/json';

		return $this->send( $endpoint, $request );
	}

	public function head( $endpoint, $request = array() ) {
		$request['method'] = 'HEAD';
		return $this->send( $endpoint, $request );
	}

	public function put( $endpoint, $request = array() ) {
		$request['method'] = 'PUT';

		if ( !isset( $request['headers']['Content-Type'] ) )
			$request['headers']['Content-Type'] = 'application/json';

		return $this->send( $endpoint, $request );
	}

	public function delete( $endpoint, $request = array() ) {
		$request['method'] = 'DELETE';
		return $this->send( $endpoint, $request );
	}

	protected function make_url( $endpoint ) {
		if ( empty( $endpoint ) or '/' == $endpoint[0] )
			return $this->base_url . $endpoint;

		return $endpoint;
	}


}