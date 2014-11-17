<?php

class Prompt_Admin_Invite_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'Invite Subscribers', 'Postmatic' );
	}

	public function slug() {
		return 'invite';
	}

	public function form_handler() {

		if ( !empty( $_POST['recipients'] ) ) {

			$recipients = trim( str_replace( "\r", "", wp_unslash( $_POST['recipients'] ) ) );
			$recipients = explode( "\n", $recipients );

			$subject = sanitize_text_field( wp_unslash( $_POST['invite_subject'] ) );
			$message = wpautop( wp_unslash( $_POST['invite_introduction'] ) );

			$this->schedule_invites( $recipients, $subject, $message );
		}
	}

	public function schedule_invites( $recipients, $subject, $message ) {

		$users_data = array();
		$failures = array();
		$prompt_site = new Prompt_Site();

		foreach( $recipients as $recipient ){

			$to_address = Prompt_Email::address( $recipient );

			if ( !is_email( $to_address ) ) {
				$failures[] = __( 'Invalid email address', 'Postmatic' ) . ': ' . $recipient;
				continue;
			}

			$user = get_user_by( 'email', $to_address );
			if ( $user and $prompt_site->is_subscribed( $user->ID ) ) {
				$failures[] = __( 'Already subscribed', 'Postmatic' ) . ': ' . $recipient;
				continue;
			}

			$to_name = Prompt_Email::name( $recipient );

			$users_data[] = array(
				'user_email' => $to_address,
				'display_name' => $to_name,
			);

		}

		if ( !empty( $users_data ) ) {
			$message_data = array(
				'subject' => $subject,
				'invite_introduction' => $message,
			);

			wp_schedule_single_event(
				time(),
				'prompt/subscription_mailing/send_agreements',
				array( $prompt_site, $users_data, $message_data )
			);

			$confirmation_format = _n( 'Success. %d invite sent.', 'Success. %d invites sent.', count( $users_data ), 'Postmatic' );
			$this->add_notice( sprintf( $confirmation_format, count( $users_data ) ) );
		}

		if ( !empty( $failures ) ) {
			$failure_notice = __( 'Something went wrong and these invites were not sent: ', 'Postmatic' ) . '<br/>' . implode( '<br/>', $failures );
			$this->add_notice( $failure_notice, 'error' );
		}

	}

	public function render() {

		$introduction = __( '<h2>Send email invitations to subscribe to your site</h2><p>Use this tool to reach out to your community of past or current commenters. Be sure to check the subject and introductory text below.<p>', 'Postmatic' );

		$rows = array(
			$this->row_wrap(
				__( 'Email Subject', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'invite_subject',
						'value' => sprintf( __( 'You\'re invited to subscribe to %s', 'Postmatic' ), get_option( 'blogname' ) ),
					)
				)
			),
			$this->row_wrap(
				__( 'Email Introduction<br /><small>This text will be placed at the top of invitation message. Make it as friendly and personalized as you can.', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'textarea',
						'name' => 'invite_introduction',
						'extra' => 'rows="7"',
					),
					wp_unslash( $_POST )
				)
			),
			html( 'tr class="recipient-type"',
				html( 'th', __( 'Who should we send this invite to?', 'Postmatic' ) ),
				html( 'td',
					$this->input(
						array(
							'type' => 'radio',
							'name' => 'recipient_type',
							'choices' => array(
								'manual' => __( 'A list of email addresses', 'Postmatic' ),
								'recent' => __( 'People who have recently commented', 'Postmatic' ),
								'count' => __( 'People who comment the most', 'Postmatic' ),
								'all' => __( 'Anyone that has ever commented', 'Postmatic' ),
							),
						),
						$_POST
					)
				)
			),
			html(
				'tr class="invite-manual"',
				html( 'th', '' ),
				html( 'td',
					$this->input(
						array(
							'type' => 'textarea',
							'name' => 'manual_addresses',
							'desc' => __( '<br />Please separate addresses with commas.', 'Postmatic' ),
						)
					)
				)
			),
			html(
				'tr class="invite-recent"',
				html( 'th', '' ),
				html( 'td',
					$this->input(
						array(
							'type' => 'select',
							'name' => 'activity_months',
							'desc' => __( 'Send this invitation to people who have had an approved comment within the last', 'Postmatic' ),
							'desc_pos' => 'before',
							'choices' => range( 1, 48 ),
						),
						$_POST
					),
					html( 'label', __( 'months.', 'Postmatic' ) )
				)
			),
			html(
				'tr class="invite-count"',
				html( 'th', '' ),
				html( 'td',
					$this->input(
						array(
							'type' => 'select',
							'name' => 'minimum_count',
							'desc' => __( 'Send this invitation to people who have at least', 'Postmatic' ),
							'desc_pos' => 'before',
							'choices' => range( 2, 10 ),
							'selected' => 5,
						),
						$_POST
					),
					html( 'label', __( 'approved comments.', 'Postmatic' ) )
				)
			),
			html(
				'tr class="recipient-list"',
				html( 'th', __( 'Recipients', 'Postmatic' ) ),
				html( 'td',
					html( 'div class="loading-indicator" style="display: none;"',
						html( 'img',
							array(
								'src' => path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' ),
								'alt' => __( 'Loading...', 'Postmatic' ),
							)
						)
					),
					html( 'p',
						__( 'Based on the above your invite will be sent to', 'Postmatic' ),
						' ',
						html( 'span class="recipient-count"', '0' ),
						' ',
						__( 'people.', 'Postmatic' )
					),
					$this->input(
						array(
							'type' => 'textarea',
							'name' => 'recipients',
							'extra' => 'rows="7" cols="45" disabled="disabled"',
						)
					)
				)
			),
		);

		return
			$introduction .
			$this->form_table_wrap( implode( '', $rows ), array( 'value' => __( 'Send Invites', 'Postmatic' ) ) );
	}

}
