<?php

class Prompt_Admin_Skimlinks_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'Skimlinks', 'Postmatic' );
	}

	public function render() {

		$table_entries = array(
			array(
				'title' => __( 'Enter your Skimlinks publication ID:', 'Postmatic' ),
				'type' => 'text',
				'name' => 'skimlinks_publisher_id',
				'desc' => sprintf(
					__( 'This can be found in your Skimlinks <a href="%s" target="_blank">Publisher Hub</a> in <em>account settings</em>.', 'Postmatic' ),
					'https://hub.skimlinks.com/account'
				),
			)
		);


		$welcome_content = html(
			'div class="welcome" id="skimlinks-welcome"',
			html( 'h2', __( 'Enable Skimlinks sent via Postmatic', 'Postmatic' ) ),
			html( 'P',
				__(
					'Enter your Skimlinks publisher ID to start monetizing your posts using Postmatic. Links that you place into your content will be tracked using the Skimlinks url shortener api. We do not yet offer support for either Skimwords nor the Skimlinks custom domain service. We very well may in the future.',
					'Postmatic'
				)
			)
		);
		
		return $welcome_content . $this->form_table( $table_entries );
	}

	function validate( $new_data, $old_data ) {
		$valid_data = array();

		if ( ! empty( $new_data['skimlinks_publisher_id'] ) ) {
			$valid_data['skimlinks_publisher_id'] = sanitize_text_field( $new_data['skimlinks_publisher_id'] );
		}

		return $valid_data;
	}
}
