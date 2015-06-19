<?php

class Prompt_Admin_Invite_Options_Tab extends Prompt_Admin_Options_Tab {

	protected $limit = 1500;

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
			Prompt_Core::$options->set( 'invite_subject', $subject );

			$message = wp_unslash( $_POST['invite_introduction'] );
			Prompt_Core::$options->set( 'invite_introduction', $message );
			$message = wpautop( $message );

			$this->schedule_invites( $recipients, $subject, $message );
		}
	}

	public function schedule_invites( $recipients, $subject, $message ) {

		$users_data = array();
		$address_index = array();
		$failures = array();
		$prompt_site = new Prompt_Site();
		$current_user = wp_get_current_user();

		foreach( $recipients as $recipient ){

			$to_address = Prompt_Email::address( $recipient );
			$lower_case_to_address = strtolower( $to_address );

			if ( isset( $address_index[$lower_case_to_address] ) ) {
				$failures[] = __( 'Duplicate email address', 'Postmatic' ) . ': ' . $recipient;
				continue;
			}

			if ( !is_email( $to_address ) ) {
				$failures[] = __( 'Invalid email address', 'Postmatic' ) . ': ' . $recipient;
				continue;
			}

			$user = get_user_by( 'email', $to_address );
			if ( $user and $prompt_site->is_subscribed( $user->ID ) ) {
				$failures[] = __( 'Already subscribed', 'Postmatic' ) . ': ' . $recipient;
				continue;
			}

			$address_index[$lower_case_to_address] = true;

			$to_name = Prompt_Email::name( $recipient );

			$users_data[] = array(
				'user_email' => $to_address,
				'display_name' => $to_name,
			);

		}

		if ( !empty( $users_data ) ) {
			$message_data = array(
				'subject' => html_entity_decode( $subject, ENT_QUOTES ),
				'invite_introduction' => $message,
				'message_type' => Prompt_Enum_Message_Types::INVITATION,
				'from_name' => $current_user->display_name . ' - ' . get_option( 'blogname' ),
			);

			Prompt_Subscription_Mailing::schedule_agreements( $prompt_site, $users_data, $message_data );

			$confirmation_format = _n( 'Success. %d invite sent.', 'Success. %d invites sent.', count( $users_data ), 'Postmatic' );
			$this->add_notice( sprintf( $confirmation_format, count( $users_data ) ) );
		}

		if ( !empty( $failures ) ) {
			$failure_notice = __( 'Something went wrong and these invites were not sent: ', 'Postmatic' ) . '<br/>' . implode( '<br/>', $failures );
			$this->add_notice( $failure_notice, 'error' );
		}

	}

	public function render() {

		$introduction = html( 'h2',
			__( 'Send email invitations to subscribe to your site', 'Postmatic' )
		) . html( 'p',
			__( 'Use this tool to reach out to your community of past or current commenters. Be sure to check the subject and introductory text below.',
				'Postmatic'
			)
		);

		$rows = array(
			$this->row_wrap(
				__( 'Email Subject', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'invite_subject',
					),
					$this->options->get()
				)
			),
			$this->row_wrap(
				__( 'Email Introduction', 'Postmatic' ) .
				'<br />' .
				html( 'small',
					__( 'This text will be placed at the top of invitation message. Make it as friendly and personalized as you can.',
						'Postmatic'
					)
				),
				$this->input(
					array(
						'type' => 'textarea',
						'name' => 'invite_introduction',
						'extra' => 'rows="7"',
					),
					$this->options->get()
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
								'users' => __( 'WordPress users who are not subscribed', 'Postmatic' ),
								'post_subscribers' => __(
									'WordPress users subscribed to a comment thread but not new posts',
									'Postmatic'
								),
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
							'desc' => '<br />' . __( 'Please separate addresses with commas.', 'Postmatic' ),
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
				'tr class="invite-users"',
				html( 'th', '' ),
				html( 'td',
					$this->input(
						array(
							'type' => 'select',
							'name' => 'user_role',
							'desc' => __( 'Send this invitation to ', 'Postmatic' ),
							'desc_pos' => 'before',
							'choices' => $this->user_role_choices(),
							'selected' => 5,
						),
						$_POST
					),
					html( 'label', __( 'users who are not subscribed.', 'Postmatic' ) )
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
						),
						' ',
						__( 'If you have many commenters, this could take some time.', 'Postmatic' )
					),
					html( 'p',
						__(
							'Based on the above your invite will be sent to <span class="recipient-count">0</span> people.',
							'Postmatic'
						)
					),
					html( 'div class="invite-limit-warning"',
						array( 'data-limit' => $this->limit ),
						sprintf(
							__(
								'Wow, that\'s a lot of invitations! This tool is currently limited to %d invites. You can contact support if you need to arrange more.',
								'Postmatic'
							),
							$this->limit
						)
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

	protected function user_role_choices() {
		global $wp_roles;
		return array_merge( array( 'all' => __( 'All', 'Postmatic' ) ), $wp_roles->role_names );
	}
}
