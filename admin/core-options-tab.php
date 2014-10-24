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

		$valid_data = $this->validate_checkbox_fields( $new_data, $old_data, array( 'enable_collection' ) );

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