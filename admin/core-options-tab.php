<?php

class Prompt_Admin_Core_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'General', 'Postmatic' );
	}

	public function render() {
		$table_entries = array(
			array(
				'title' => __( 'Postmatic Api Key', 'Postmatic' ),
				'type' => 'text',
				'name' => 'prompt_key',
				'extra' => array( 'class' => 'regular-text last-submit' ),
			),
		);

		$this->override_entries( $table_entries );

		$output = $this->table( $table_entries, $this->options->get() ) .
			$this->plan_intro() .
			html( 'div class="opt-in"',
				html( 'div',
					html( 'h3', __( 'Improve your site by making Postmatic even better', 'Postmatic' ) ),
					html( 'p',
						__(
							'We rely on users like you to help shape our development roadmap. By checking the box below you will be helping us know more about your site and how we can make Postmatic even better.',
							'Postmatic'
						)
					)
				),
				scbForms::input(
					array(
						'type' => 'checkbox',
						'name' => 'enable_collection',
						'desc' => __( 'Yes, send periodic usage statistics to Postmatic.', 'Postmatic' ),
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

	protected function plan_intro() {

		$base_url = defined( 'PROMPT_RSS_BASE_URL' ) ? PROMPT_RSS_BASE_URL : Prompt_Enum_Urls::HOME;

		$feed_url = $base_url . '/targets/' . $this->plan() . '/feed/?post_type=update';

		$feed = new Prompt_Admin_Feed( $feed_url );

		$content = $feed->item_content( 0 );

		if ( ! $content )
			$content = $this->plan_intro_fallback();

		return html( 'div class="plan-intro"', $content );
	}

	protected function plan() {

		if ( Prompt_Enum_Email_Transports::LOCAL == $this->options->get( 'email_transport' ) )
			return 'free';

		if ( in_array( Prompt_Enum_Message_Types::DIGEST, $this->options->get( 'enabled_message_types' ) ) )
			return 'premium';

		return 'beta';
	}

	protected function plan_intro_fallback() {

		$template = new Prompt_Template( sprintf( 'core-options-%s.php', $this->plan() ) );

		return $template->render( array(
			'upgrade_url' => Prompt_Enum_Urls::PREMIUM,
			'manage_url' => Prompt_Enum_Urls::MANAGE,
			'image_url' => path_join( Prompt_Core::$url_path, 'media/danny3-530x10241.png' ),
		) );
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