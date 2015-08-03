<?php

/**
 * Handle Prompt options and those of active add-ons.
 */
class Prompt_Admin_Options_Page extends scbAdminPage {
	const DISMISS_ERRORS_META_KEY = 'prompt_error_dismiss_time';
	const BUG_REPORT_OPTION_NAME = 'prompt_error_submit_time';

	protected $_overridden_options;

	protected $_active_add_on_form;

	/** @var Prompt_Admin_Options_Tab[] */
	protected $tabs;

	/** @var  Prompt_Admin_Options_Tab */
	protected $submitted_tab;

	/** @var  string shortcut for $this->options->get( 'prompt_key' ) */
	protected $key;

	/** @var  Prompt_Admin_Conditional_Notice[]  */
	protected $notices;

	/** @var  boolean */
	protected $is_current_page = false;

	public function __construct( $file = false, $options = null, $overrides = null, $tabs = null, $notices = null ) {
		parent::__construct( $file, $options );
		$this->_overridden_options = $overrides;
		$this->key = $options->get( 'prompt_key' );

		$this->maybe_auto_load();

		$this->tabs = $tabs;

		$this->notices = $notices;
	}

	/**
	 * Set any values used in the parent class.
	 */
	public function setup() {
		$this->args = array(
			'page_title' => __( 'Postmatic', 'Postmatic' ),
		);
	}

	public function add_tab( Prompt_Admin_Options_Tab $tab ) {
		if ( !$this->tabs )
			$this->tabs = array();

		$this->tabs[$tab->slug()] = $tab;
	}

	/**
	 * Before there is any output, add tabs and handle any posted options.
	 */
	public function page_loaded() {

		$this->is_current_page = true;

		if ( is_null( $this->notices ) )
			$this->add_notices();

		foreach( $this->notices as $notice ) {
			$notice->process_dismissal();
		}

		if ( $this->process_tabs() )
			return;

		if ( !empty( $_POST['error_alert'] ) ) {

			if ( !empty( $_POST['delete_errors'] ) ) {
				Prompt_Logging::delete_log();
			} else {
				update_user_meta( get_current_user_id(), self::DISMISS_ERRORS_META_KEY, time() );
			}

			if ( !empty( $_POST['submit_errors'] ) ) {
				$this->submit_errors();
				return;
			}
		}

		$this->form_handler();
		$this->reset_key();
	}

	public function admin_msg( $msg = '', $class = 'updated' ) {
		$settings_errors = get_settings_errors();
		if ( !empty( $settings_errors ) )
			return;

		if ( empty( $msg ) )
			$msg = __( 'Settings <strong>saved</strong>.', 'Postmatic' );

		echo scb_admin_notice( $msg, $class );
	}

	public function submitted_errors_admin_msg() {
		$this->admin_msg( __( 'Report sent! Our bug munchers thank you for the meal.', 'Postmatic' ) );
	}

	public function beta_request_sent_admin_msg() {
		$this->admin_msg( __( 'Request sent. We are currently sending a few hundred tokens per week. Expect to receive yours within 1-2 days. You can safely leave Postmatic activated but it is not necessary to do so.', 'Postmatic' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function page_head() {

		wp_enqueue_media();

		wp_enqueue_style(
			'prompt-admin',
			path_join( Prompt_Core::$url_path, 'css/admin.css' ),
			array(),
			Prompt_Core::version()
		);

		wp_enqueue_style(
			'prompt-jmetro',
			path_join( Prompt_Core::$url_path, 'vendor/vernal-creative/jmetro/css/jquery-ui.css' ),
			array(),
			'1.0.0'
		);

		$script = new Prompt_Script( array(
			'handle' => 'prompt-options-page',
			'path' => 'js/options-page.js',
			'dependencies' => array( 'jquery-ui-tabs' ),
		) );
		$script->enqueue();

	}

	public function page_header() {

		$wrapper = '<div class="wrap signup">';
		$account_url = Prompt_Enum_Urls::MANAGE;

		if ( $this->key ) {
			$wrapper = '<div class="wrap">';
			$account_url .= '/login';
		}

		echo $wrapper;
		echo html( 'div id="manage-account"',
			html( 'p',
				html( 'a',
					array( 'href' => $account_url ),
					__( '&#9998; Manage your account', 'Postmatic' )
				)
			)
		);
		echo html( 'h2 id="prompt-settings-header"', html( 'span', $this->args['page_title'] ) );
	}

	function page_content() {

		echo $this->log_alert();

		$key_alert = $this->key_alert();
		echo $key_alert;

		if ( $key_alert or !$this->key ) {

			self::display_key_prompt();

			echo html( 'div class="initialize-key"',
				html( 'h2', __( 'Already have a key?', 'Postmatic' ) ),
				$this->form_table( array(
					array(
						'title' => __( 'Postmatic Key', 'Postmatic' ),
						'type' => 'text',
						'name' => 'prompt_key',
						'desc' => sprintf(
							__(
								'Once you have your key, enter it here to blast off!.',
								'Postmatic'
							),
							Prompt_Enum_Urls::TERMS_OF_SERVICE
						)
					),
				) )
			);

			return;
		}

		foreach( $this->notices as $notice ) {
			$notice->maybe_display();
		}

		list( $tabs, $panels ) = $this->tabs_content();

		echo html(
			'div id="prompt-tabs"',
			html( 'ul',
				$tabs
			),
			$panels
		);

	}

	protected function key_alert() {

		// Before key is entered we don't check anything
		if ( empty( $this->key ) )
			return '';

		// Only check key validity when viewing main settings page
		if ( isset( $_POST['tab'] ) or isset( $_POST['prompt_key'] ) )
			return '';

		$key = $this->validate_key( $this->key );
		if ( is_wp_error( $key ) )
			return html( 'div class="error"',  html( 'p', $key->get_error_message() ) );

		return '';
	}

	protected function log_alert() {
		$dismiss_time = absint( get_user_meta( get_current_user_id(), self::DISMISS_ERRORS_META_KEY, true ) );

		$log = Prompt_Logging::get_log( $dismiss_time, ARRAY_A );

		if ( empty( $log ) )
			return '';

		$rows = array();
		foreach( $log as $entry ) {

			$rows[] = html( 'tr',
				html( 'td', date( 'Y-m-d H:i:s e', $entry['time'] ) ),
				html( 'td', $entry['message'] ),
				html( 'td', html( 'textarea', json_encode( $entry['data'] ) ) )
			);

		}

		if ( empty( $rows ) )
			return '';

		return html( 'div class="error"',
			html( 'form', array( 'method' => 'post', 'action' => '' ),
				html( 'p',
					__( '<strong>Attention:</strong> There have been some recent errors in your configuration. An error log can be found here: ' )
				),
				html( 'table class="wp-list-table widefat"',
					implode( '', $rows )
				),
				html( 'input', array( 'type' => 'hidden', 'name' => 'error_alert',  'value' => '1' ) ),
				get_submit_button( __( 'Dismiss', 'Postmatic' ), 'primary large', 'dismiss_errors' ),
				get_submit_button( __( 'Submit A Bug Report', 'Postmatic' ), 'left', 'submit_errors' )
			)
		);
	}

	protected function tabs_content() {

		$tabs = '';
		$panels = '';

		$submitted_slug = $this->submitted_tab ? $this->submitted_tab->slug() : '';
		foreach( $this->tabs as $slug => $tab ) {
			$tabs .= html(
				'li',
				array( 'class' => $slug == $submitted_slug ? 'ui-tabs-active' : '' ),
				html( 'a', array( 'href' => '#prompt-settings-' . $slug ), $tab->name() )
			);
			$panels .= html(
				'div',
				array( 'id' => 'prompt-settings-' . $slug ),
				$tab->render()
			);
		}

		return array( $tabs, $panels );
	}

	/**
	 * Assemble sidebar content
	 * @return string content
	 */
	protected function sidebar_content() {
		return html(
			'div id="prompt-sidebar"',
			'&nbsp;'
		);
	}

	protected function display_key_prompt() {

		$base_url = defined( 'PROMPT_RSS_BASE_URL' ) ? PROMPT_RSS_BASE_URL : Prompt_Enum_Urls::HOME;

		$feed_url = $base_url . '/targets/get-a-key/feed/?post_type=update';

		$signup_url = Prompt_Enum_Urls::MANAGE . '/signup';

		$new_site_url = Prompt_Enum_Urls::MANAGE . '/sites/link?ajax_url=' . urlencode( admin_url( 'admin-ajax.php') );

		$feed = new Prompt_Admin_Feed( $feed_url );

		$content = $feed->item_content();


		if ( $content )
			$content = str_replace( $signup_url, $new_site_url, $content );

		if ( ! $content ) {

			$template = new Prompt_Template( 'get-a-key.php' );
			$content = $template->render( compact( 'new_site_url' ) );

		}

		echo $content;
	}

	public function validate( $new_data, $old_data ) {
		$valid_data = $old_data;

		if ( !isset( $new_data['prompt_key'] ) or $new_data['prompt_key'] == $old_data['prompt_key'] )
			return $valid_data;

		$key = $this->validate_key( $new_data['prompt_key'] );
		if ( is_wp_error( $key ) ) {
			add_settings_error( 'prompt_key', 'invalid_key', $key->get_error_message() );
			return $valid_data;
		}

		$valid_data['prompt_key'] = $key;
		$this->key = $key;

		return $valid_data;
	}

	public function validate_key( $key ) {
		if ( empty( $key ) )
			return '';

		$key = preg_replace( '/\s/', '', sanitize_text_field( $key ) );

		$client = new Prompt_Api_Client( array(), $key );

		$response = $client->get( '/site' );

		if ( is_wp_error( $response ) or !in_array( $response['response']['code'], array( 200, 401 ) ) ) {
			return Prompt_Logging::add_error(
				'key_http_error',
				__( 'There\'s a problem verifying your key. Please try later or report this error.', 'Postmatic' ),
				$response
			);
		}

		if ( 401 == $response['response']['code'] ) {
			$message = sprintf(
				__(
					'We didn\'t recognize the key "%s". Please make sure it exactly matches the key we supplied you. <a href="%s" target="_blank">Visit your Postmatic dashboard for assistance</a>. ',
					'Postmatic'
				),
				$key,
				Prompt_Enum_Urls::MANAGE
			);
			return new WP_Error( 'invalid_key', $message );
		}

		$configuration = json_decode( $response['body'] );

		if ( ! self::site_matches( $configuration->site->url ) ) {
			$message = sprintf(
				__(
					'Your key was registered for a different site. Please request a key for this site\'s dedicated use, or <a href="%s" target="_blank">contact us</a> for assistance. Thanks!',
					'Postmatic'
				),
				Prompt_Enum_Urls::BUG_REPORTS
			);
			return new WP_Error( 'wrong_key', $message );
		}

		$configurator = Prompt_Factory::make_configurator( $client );

		$configurator->update_configuration( $configuration );

		return $key;
	}

	/**
	 * Get the URL of the options page.
	 *
	 * @since 1.2.3
	 *
	 * @return string
	 */
	public function url() {
		return add_query_arg( 'page', $this->args['page_slug'], admin_url( $this->args['parent'] ) );
	}

	/**
	 * Whether the current request is for the options page.
	 *
	 * Only available after the load-(page) action.
	 *
	 * @since 1.3.0
	 *
	 * @return bool
	 */
	public function is_current_page() {

		if ( ! did_action( 'current_screen' ) )
			trigger_error( 'is_current_page() is not available until after the load-(page) action' );

		return $this->is_current_page;
	}

	protected function site_matches( $url ) {
		$url_parts = parse_url( $url );

		if ( !isset( $url_parts['host'] ) or !isset( $url_parts['path'] ) )
			return false;

		$ajax_url_parts = parse_url( admin_url( 'admin-ajax.php' ) );

		return ( $url_parts['host'] === $ajax_url_parts['host'] and $url_parts['path'] === $ajax_url_parts['path'] );
	}

	protected function submit_errors() {
		$user = wp_get_current_user();

		$last_submit_time = absint( get_option( self::BUG_REPORT_OPTION_NAME ) );

		update_option( self::BUG_REPORT_OPTION_NAME, time() );

		$message = array( 'error_log' => Prompt_Logging::get_log( $last_submit_time, ARRAY_A ) );

		$environment = new Prompt_Environment();

		$message = array_merge( $message, $environment->to_array() );

		$email = new Prompt_Email( array(
			'to_address' => Prompt_Core::SUPPORT_EMAIL,
			'from_address' => $user->user_email,
			'from_name' => $user->display_name,
			'subject' => sprintf( 'Error submission from %s', get_option( 'blogname' ) ),
			'text' => json_encode( $message ),
			'message_type' => Prompt_Enum_Message_Types::ADMIN,
		));

		$sent = Prompt_Factory::make_mailer( Prompt_Enum_Email_Transports::LOCAL )->send_one( $email );

		if ( is_wp_error( $sent ) and Prompt_Core::$options->get( 'prompt_key' ) )
			$sent = Prompt_Factory::make_mailer( Prompt_Enum_Email_Transports::API )->send_one( $email );

		if ( is_wp_error( $sent ) ) {
			Prompt_Logging::add_error(
				'bug_submission_error',
				sprintf(
					__(
						'We\'re even having trouble sending a bug report. Please copy the data to the right and send to %s.',
						'Postmatic'
					),
					Prompt_Core::SUPPORT_EMAIL
				),
				$sent
			);
			return;
		}

		add_action( 'admin_notices', array( $this, 'submitted_errors_admin_msg' ) );
	}

	protected function reset_key() {
		$this->key = $this->options->get( 'prompt_key' );
	}

	/**
	 * Create tabs and process any tab-submitted data.
	 *
	 * @since 1.4.0
	 *
	 * @return bool whether submitted tab data was processed
	 */
	protected function process_tabs() {

		$did_updates = false;
		$submitted_tab_slug = isset( $_POST['tab'] ) ? $_POST['tab'] : null;

		// The options tab can influence which other tabs are created, so handle it first
		$options_tab = new Prompt_Admin_Options_Options_Tab( $this->options, $this->_overridden_options );

		if ( $options_tab->slug() === $submitted_tab_slug ) {
			$this->submitted_tab = $options_tab;
			$options_tab->form_handler();
			$did_updates = true;
		}

		if ( !$this->tabs ) {
			$this->add_tabs( $options_tab );
		}

		if ( !$did_updates and $submitted_tab_slug ) {
			$this->submitted_tab = $this->tabs[$submitted_tab_slug];
			$this->submitted_tab->form_handler();
			$this->reset_key(); // in case a new key was saved
			$did_updates = true;
		}

		return $did_updates;
	}


	/**
	 * @param Prompt_Admin_Options_Options_Tab $options_tab
	 */
	protected function add_tabs( $options_tab ) {
		$this->add_tab( new Prompt_Admin_Core_Options_Tab( $this->options, $this->_overridden_options ) );
		$this->add_tab( new Prompt_Admin_Email_Options_Tab( $this->options, $this->_overridden_options ) );
		$this->add_tab( new Prompt_Admin_Invite_Options_Tab( $this->options, $this->_overridden_options ) );
		$this->add_tab( $options_tab );
		if ( class_exists( 'Jetpack' ) )
			$this->add_tab( new Prompt_Admin_Jetpack_Import_Options_Tab( $this->options, $this->_overridden_options ) );
		if ( class_exists( 'WYSIJA' ) )
			$this->add_tab( new Prompt_Admin_Mailpoet_Import_Options_Tab( $this->options, $this->_overridden_options ) );
		$this->add_tab( new Prompt_Admin_MailChimp_Import_Options_Tab( $this->options, $this->_overridden_options ) );

		$subscribe_reloaded_import_tab = new Prompt_Admin_Subscribe_Reloaded_Import_Options_Tab(
			$this->options,
			$this->_overridden_options
		);

		if ( $subscribe_reloaded_import_tab->is_available() or $this->is_importable_comments_plugin_active() )
			$this->add_tab( $subscribe_reloaded_import_tab );

		if ( Prompt_Core::$options->get( 'enable_optins' ) )
			$this->add_tab( new Prompt_Admin_Optins_Options_Tab( $this->options, $this->_overridden_options ) );

		if ( Prompt_Core::$options->get( 'enable_skimlinks' ) )
			$this->add_tab( new Prompt_Admin_Skimlinks_Options_Tab( $this->options, $this->_overridden_options ) );

		$this->add_tab( new Prompt_Admin_Support_Options_Tab( $this->options, $this->_overridden_options ) );
	}

	protected function is_importable_comments_plugin_active() {
		// Check for Subscribe to Comments, StC Reloaded, or StC double opt-in
		return (
			class_exists( 'wp_subscribe_reloaded' ) or
			class_exists( 'CWS_STC' ) or
			class_exists( 'sg_subscribe' )
		);
	}

	protected function add_notices() {
		$this->notices = array(
			new Prompt_Admin_Local_Mail_Notice(),
			new Prompt_Admin_Widget_Notice(),
			new Prompt_Admin_Moderation_User_Notice(),
			new Prompt_Admin_Akismet_Notice(),
			new Prompt_Admin_Zero_Spam_Notice(),
		);
	}

	/**
	 * When there isn't a key and the user can add one, direct them here.
	 */
	protected function maybe_auto_load() {

		if ( $this->key )
			return;

		if ( ! current_user_can( $this->args['capability'] ) )
			return;

		if ( ! $this->options->get( 'redirect_to_options_page' ) )
			return;

		$this->options->set( 'redirect_to_options_page', false );

		wp_redirect( $this->url() );
	}

}
