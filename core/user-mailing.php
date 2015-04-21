<?php

class Prompt_User_Mailing {

	/**
	 * Send an email to a user who has had an account created for them.
	 *
	 * @param int|object $user
	 * @param string $password
	 * @param Prompt_Template $template
	 */
	public static function send_new_user_notification( $user, $password, $template ) {
		$user = is_integer( $user ) ? get_userdata( $user ) : $user;

		$template_data = compact( 'user', 'password' );
		/**
		 * Filter new user email template data.
		 *
		 * @param array $template_data {
		 * @type WP_User $user
		 * @type string $password
		 * }
		 */
		$template_data = apply_filters( 'prompt/new_user_email/template_data', $template_data );

		$subject = sprintf( __( 'Welcome to %s', 'Postmatic' ), get_option( 'blogname' ) );
		$email = new Prompt_Email( array(
			'to_address' => $user->user_email,
			'subject' => $subject,
			'html' => $template->render( $template_data ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		) );

		/**
		 * Filter new user email.
		 *
		 * @param Prompt_Email $email
		 * @param array $template_data {
		 * @type WP_User $user
		 * @type string $password
		 * }
		 */
		$email = apply_filters( 'prompt/new_user_email', $email, $template_data );

		$mailer = Prompt_Factory::make_mailer();
		$mailer->send_one( $email );
	}
}