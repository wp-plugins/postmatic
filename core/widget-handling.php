<?php

class Prompt_Widget_Handling {

	/**
	 * Register widgets and widget areas.
	 */
	public static function register() {

		if ( ! Prompt_Core::$options->get( 'prompt_key' ) )
			return;

		if ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) )
			Prompt_Email_Footer_Sidebar::register();

		register_widget( 'Prompt_Subscribe_Widget' );

	}
}
