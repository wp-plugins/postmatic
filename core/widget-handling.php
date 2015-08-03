<?php

class Prompt_Widget_Handling {

	/**
	 * Register widgets and widget areas.
	 */
	public static function register() {

		if ( ! Prompt_Core::$options->get( 'prompt_key' ) )
			return;

		register_widget( 'Prompt_Subscribe_Widget' );

		if ( Prompt_Enum_Email_Transports::API != Prompt_Core::$options->get( 'email_transport' ) )
			return;

		if ( Prompt_Enum_Email_Footer_Types::WIDGETS != Prompt_Core::$options->get( 'email_footer_type' ) )
			return;

		Prompt_Email_Footer_Sidebar::register();
		Prompt_Comment_Email_Footer_Sidebar::register();

	}
}
