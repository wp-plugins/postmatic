<?php

class Prompt_Factory {

	/**
	 * Create an instance of a Prompt object with sensible defaults.
	 * @param string $thing
	 * @param array $args
	 * @return mixed
	 */
	public static function make( $thing, $args = array() ) {
		return call_user_func_array( array( __CLASS__, 'make_' . strtolower( $thing ) ), $args );
	}

	/**
	 * Make a mailer instance appropriate to the environment.
	 * @param string $transport Optional transport to use
	 * @return Prompt_Mailer
	 */
	public static function make_mailer( $transport = null ) {

		$transport = $transport ? $transport : Prompt_Core::$options->get( 'email_transport' );

		$mailer = new Prompt_Wp_Mailer();

		if ( Prompt_Enum_Email_Transports::API ==  $transport )
			$mailer = new Prompt_Mailer();

		$mailer = apply_filters( 'prompt/make_mailer', $mailer, $transport );

		return $mailer;
	}

	/**
	 * @param Prompt_Api_Client $client Optional API client instance.
	 * @return Prompt_Inbound_Messenger
	 */
	public static function make_inbound_messenger( Prompt_Api_Client $client = null ) {
		return apply_filters( 'prompt/make_inbound_messenger', new Prompt_Inbound_Messenger( $client ) );
	}

	/**
	 * @param Prompt_Api_Client $client Optional API client instance.
	 * @return Prompt_Configurator
	 */
	public static function make_configurator( Prompt_Api_Client $client = null ) {
		return apply_filters( 'prompt/make_configurator', new Prompt_Configurator( $client ) );
	}


	/**
	 * @return Prompt_Admin_Jetpack_Import
	 */
	public static function make_jetpack_import() {
		return apply_filters( 'prompt/make_jetpack_import', Prompt_Admin_Jetpack_Import::make() );
	}
}