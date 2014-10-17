<?php

class Prompt_Admin_Core_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'General', 'Prompt_Core' );
	}

	public function render() {
		$table_entries = array(
			array(
				'title' => __( 'Postmatic Api Key', 'Prompt_Core' ),
				'type' => 'text',
				'name' => 'prompt_key',
			),
			array(
				'title' => __( 'Author Subscriptions', 'Prompt_Core' ),
				'type' => 'checkbox',
				'name' => 'auto_subscribe_authors',
				'desc' => __( 'Subscribe authors to comments on their own posts.<small>(Recommended)</small>', 'Prompt_Core' ),
			),
			array(
				'title' => __( 'User Accounts', 'Prompt_Core' ),
				'type' => 'checkbox',
				'name' => 'send_login_info',
				'desc' => __( 'Email subscribers WordPress account credentials when they subscribe. Only necessary in some situations as all user commands are otherwise possible via email. If enabled we recommend using a good front end login plugin.', 'Prompt_Core' ),
			)
		);

		$this->override_entries( $table_entries );

		$output = $this->table( $table_entries, $this->options->get() ) .
			html( 'div class="opt-in"',
				html( 'div', __( '<h3>Improve your site by making Postmatic even better</h3><p>We rely on users like you to help shape our development roadmap. By checking the box below you will be helping us know more about your site and how we can make Postmatic even better.</p>', 'Prompt_Core' ) ),
				scbForms::input(
					array(
						'type' => 'checkbox',
						'name' => 'enable_collection',
						'desc' => __( 'Yes, send periodic usage statistics to Postmatic.', 'Prompt_Core' ),
					),
					$this->options->get()
				)
			);

		return $this->form_wrap( $output );
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
		$valid_data = $old_data;

		$checkbox_fields = array( 'send_login_info', 'enable_collection', 'auto_subscribe_authors' );
		$checkbox_fields = array_diff( $checkbox_fields, array_keys( $this->overridden_options ) );
		foreach ( $checkbox_fields as $field ) {
			if ( isset( $new_data[$field] ) )
				$valid_data[$field] = true;
			else
				$valid_data[$field] = false;
		}

		if ( isset( $new_data['enable_collection'] ) and !$old_data['enable_collection'] )
			Prompt_Event_Handling::record_environment();

		if ( isset( $new_data['prompt_key'] ) and $new_data['prompt_key'] != $old_data['prompt_key'] ) {
			$key = Prompt_Core::settings_page()->validate_key( $new_data['prompt_key'] );
			if ( is_wp_error( $key ) )
				add_settings_error( 'prompt_key', 'invalid_key', $key->get_error_message() );
			else
				$valid_data['prompt_key'] = $key;
		}

		return $valid_data;
	}

}