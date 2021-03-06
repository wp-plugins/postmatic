<?php

/**
 * Base class for option tabs on the settings page.
 *
 * Makes use of scbAdminPage techniques for outputting a form and saving options,
 * but instead of adding an admin page the UI is embedded in the Prompt settings page
 * by calling Prompt_Core::settings_page()->add_tab( $tab );
 */
class Prompt_Admin_Options_Tab extends scbAdminPage {

	/** @var array */
	protected $overridden_options;
	/** @var array */
	protected $notices;

	/**
	 * Construct so the form is embeddable in another page rather than adding a new one.
	 * @param bool|string $options
	 * @param null $overridden_options
	 */
	public function __construct( $options, $overridden_options = null ) {
		$this->options = $options;
		$this->overridden_options = $overridden_options;
		$this->nonce = '';
		$this->notices = array();
	}

	/**
	 * A name for the tab.
	 *
	 * Override the default for a nice name.
	 *
	 * @return string Tab name.
	 */
	public function name() {
		return str_replace( 'Prompt_', '', get_class( $this ) );
	}

	/**
	 * A CSS-style identifier for the tab.
	 *
	 * @return string Tab identifier.
	 */
	public function slug() {
		return sanitize_title( $this->name() );
	}

	/**
	 * A PHP form style identifier for the tab.
	 *
	 * @return string Tab action.
	 */
	public function action() {
		return str_replace( '-', '_', $this->slug() );
	}

	/**
	 * Adapt the page_content method to form_content
	 * @return string
	 */
	public function page_content() {
		return $this->form_content();
	}

	/**
	 * Generate the form markup.
	 * @return string form HTML
	 */
	public function render() {
		return 'This tab has no settings.';
	}

	/**
	 * Add button args to form tables.
	 * @param string $content
	 * @param array $button_args Optional button args.
	 * @return string
	 */
	public function form_table_wrap( $content, $button_args = array() ) {
		$content = $this->table_wrap( $content );
		return $this->form_wrap( $content, $button_args );
	}

	/**
	 * Generate a submit action based on the options key.
	 * @param string $content
	 * @param array $button_args
	 * @return string
	 */
	public function form_wrap( $content, $button_args = array() ) {
		$content .= html(
			'input',
			array( 'name' => 'tab', 'type' => 'hidden', 'value' => $this->slug() )
		);
		$content .= html(
			'input',
			array( 'name' => 'action', 'type' => 'hidden', 'value' => 'save_prompt_tab_options' )
		);
		$button_args = array_merge(
			array( 'action' => $this->action() . '_submit' ),
			$button_args
		);
		return parent::form_wrap( $content, $button_args );
	}

	/**
	 * Add an admin notice to display at the top of the parent page.
	 * @param $content
	 * @param string $class
	 */
	public function add_notice( $content, $class = 'updated' ) {
		$this->notices[] = array( $content, $class );

		if ( !has_action( 'admin_notices', array( $this, 'notices' ) ) )
			add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	/**
	 * Display queued admin notices.
	 */
	public function notices() {
		foreach ( $this->notices as $notice ) {
			echo scb_admin_notice( $notice[0], $notice[1] );
		}
	}

	protected function validate_checkbox_fields( $new_data, $old_data, $field_names ) {
		$valid_data = $old_data;

		if ( $this->overridden_options )
			$field_names = array_diff( $field_names, array_keys( $this->overridden_options ) );

		foreach ( $field_names as $field ) {
			if ( isset( $new_data[$field] ) )
				$valid_data[$field] = true;
			else
				$valid_data[$field] = false;
		}

		return $valid_data;
	}
}
