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
				'desc' => __( 'Subscribe authors to comments on their own posts.<small>(Recommended)</small><p>This will automatically subscribe post authors to new comment notifications on their posts. This works well to keep the author up to date with the latest comments and discussion.</p>', 'Postmatic' ),
			),
			array(
				'title' => __( 'User Accounts', 'Postmatic' ),
				'type' => 'checkbox',
				'name' => 'send_login_info',
				'desc' => __( 'Email subscribers WordPress account credentials when they subscribe. <p>Only necessary in some situations as all user commands are otherwise possible via email. If enabled we recommend using a good front end login plugin.</p>', 'Postmatic' ),
			)
		);

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
			array( 'send_login_info', 'auto_subscribe_authors' )
		);

		return $valid_data;
	}

}