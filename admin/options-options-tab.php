<?php

class Prompt_Admin_Options_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'Options', 'Postmatic' );
	}

	public function render() {

		$table_entries = array(
			array(
				'title' => __( 'Author Subscriptions', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'auto_subscribe_authors',
				'desc' => __(
					'Subscribe authors to comments on their own posts.<small>(Recommended)</small>',
					'Postmatic'
					) . html( 'p',
						__(
							'This will automatically subscribe post authors to new comment notifications on their posts. This works well to keep the author up to date with the latest comments and discussion.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'User Accounts', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'send_login_info',
				'desc' => __( 'Email subscribers WordPress account credentials when they subscribe.', 'Postmatic' ) .
					html( 'p',
						__(
							'Only necessary in some situations as all user commands are otherwise possible via email. If enabled we recommend using a good front end login plugin.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Postmatic Delivery', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'no_post_email_default',
				'desc' => __( 'Do not send new posts via email.', 'Postmatic' ) .
					html( 'p',
						__(
							'You can still uncheck the "Do not deliver this post via email" checkbox for a specific post, but delivery will be disabled by default.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Default sending mode', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'excerpt_default',
				'desc' => __( 'Send only the excerpt instead of the full post content.', 'Postmatic' ) .
					html( 'p',
						__(
							'Enable this setting to only send excerpts with a button to read more online. You can override this on a per-post basis when drafting a new post.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Comment form opt-in', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'comment_opt_in_default',
				'desc' => __( 'Subscribe commenters to the conversation by default.', 'Postmatic' ) .
					html( 'p',
						__(
							'Please note this may place you in violation of European and Canadian spam laws. Be sure to do your homework.',
							'Postmatic'
						)
					),
			),
			array(
				'title' => __( 'Comment flood control', 'Postmatic' ),
				'type' => 'text',
				'name' => 'comment_flood_control_trigger_count',
				'desc' => __( 'How many comments in one hour should it take to trigger the flood control? There is a mimimum of 3.', 'Postmatic' ) .
					html( 'p',
						sprintf(
							__(
								'Postmatic automatically pauses comment notifications on posts that go viral. Setting the trigger to be 6 comments per hour is good for most sites. You can read more about it <a href="%s" target="_blank">on our support site</a>.  ',
								'Postmatic'
							),
							'http://docs.gopostmatic.com/article/143-what-happens-if-a-post-gets-a-gazillion-comments-do-i-get-a-gazillion-emails'
						)
					),
				'extra' => array( 'size' => 3 ),
			),
			array(
				'title' => __( 'Use awesome Postmatic optin forms (beta)', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'enable_optins',
				'desc' => __( 'Looking for something more than our widget? Try out the new optin system. It will enable a new tab up top.', 'Postmatic' ),
			),
		);

		if ( in_array( Prompt_Enum_Message_Types::COMMENT_MODERATION, Prompt_Core::$options->get( 'enabled_message_types' ) ) ) {

			$table_entries[] = array(
				'title' => __( 'Enable Skimlinks support (beta)', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'enable_skimlinks',
				'desc' => __( 'Turn on the Skimlinks tab (look for it up top)', 'Postmatic' ),
			);

		}

		$this->override_entries( $table_entries );

		return $this->form_table( $table_entries );
	}

	/**
	 * Disable overridden entry UI table entries.
	 * @param array $table_entries
	 */
	protected function override_entries( &$table_entries ) {
		foreach ( $table_entries as $index => $entry ) {
			if ( isset( $this->overridden_options[$entry['name']] ) ) {
				$table_entries[$index]['extra'] = array(
					'class' => 'overridden',
					'disabled' => 'disabled',
				);
			}
		}
	}

	function validate( $new_data, $old_data ) {

		$valid_data = $this->validate_checkbox_fields(
			$new_data,
			$old_data,
			array(
				'send_login_info',
				'auto_subscribe_authors',
				'no_post_email_default',
				'excerpt_default',
				'comment_opt_in_default',
				'enable_optins',
				'enable_skimlinks',
			)
		);

		$flood_trigger_count = $new_data['comment_flood_control_trigger_count'];
		$flood_trigger_count = is_numeric( $flood_trigger_count ) ? absint( $flood_trigger_count ) : 6;
		$flood_trigger_count = ( $flood_trigger_count < 3 ) ? 3 : $flood_trigger_count;
		$valid_data['comment_flood_control_trigger_count'] = $flood_trigger_count;

		return $valid_data;
	}

}