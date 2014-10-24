<?php

/**
 * Handle Prompt options and those of active add-ons.
 */
class Prompt_Admin_Options_Page extends scbAdminPage {
	const KEY_URL = 'https://app.gopostmatic.com';
	const SUPPORT_URL = 'https://support.gopostmatic.com';
	const SUPPORT_EMAIL = 'support@gopostmatic.com';
	const DISMISS_ERRORS_META_KEY = 'prompt_error_dismiss_time';
	const BUG_REPORT_OPTION_NAME = 'prompt_error_submit_time';

	protected $_overridden_options;

	protected $_active_add_on_form;

	/** @var Prompt_Admin_Options_Tab[] */
	protected $tabs;

	protected $submitted_tab;

	public function __construct( $file = false, $options = null, $overrides = null, $tabs = null ) {
		parent::__construct( $file, $options );
		$this->_overridden_options = $overrides;

		$this->add_tab( new Prompt_Admin_Core_Options_Tab( $options, $overrides ) );
		$this->add_tab( new Prompt_Admin_Email_Options_Tab( $options, $overrides ) );
		$this->add_tab( new Prompt_Admin_Invite_Options_Tab( $options, $overrides ) );
		$this->add_tab( new Prompt_Admin_Options_Options_Tab( $options, $overrides ) );
	}

	public function add_tab( Prompt_Admin_Options_Tab $tab ) {
		if ( !$this->tabs )
			$this->tabs = array();

		$this->tabs[$tab->slug()] = $tab;
	}

	/**
	 * Before there is any output, handle any posted options.
	 */
	public function page_loaded() {

		if ( isset( $_POST['tab'] ) and isset( $this->tabs[$_POST['tab']] ) ) {
			$this->submitted_tab = $this->tabs[$_POST['tab']];
			$this->submitted_tab->form_handler();
			return;
		}

		if ( !empty( $_POST['send_beta_request'] ) ) {
			$this->send_beta_request();
			return;
		}

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
	}

	public function admin_msg( $msg = '', $class = 'updated' ) {
		$settings_errors = get_settings_errors();
		if ( !empty( $settings_errors ) )
			return;

		if ( empty( $msg ) )
			$msg = __( 'Settings <strong>saved</strong>.', 'Prompt_Core' );

		echo scb_admin_notice( $msg, $class );
	}

	public function submitted_errors_admin_msg() {
		$this->admin_msg( __( 'Report sent! Our bug munchers thank you for the meal.', 'Prompt_Core' ) );
	}

	public function beta_request_sent_admin_msg() {
		$this->admin_msg( __( 'Request sent. We are currently sending a few hundred tokens per week. Expect to receive yours within 1-2 days. You can safely leave Postmatic activated but it is not necessary to do so.', 'Prompt_Core' ) );
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

	public function setup() {
		$this->args = array(
			'page_title' => __( 'Postmatic', 'Prompt_Core' ),
		);

		if (
			!$this->options->get( 'skip_widget_intro' ) and
			( !empty( $_GET['skip_widget_intro'] ) or self::is_subscribe_widget_in_use() )
		) {
			$this->options->set( 'skip_widget_intro', true );
		}
	}

	public function page_header() {
		if ( $this->options->get( 'prompt_key' ) )
			echo '<div class="wrap">';
		else
			echo '<div class="wrap signup">';

		echo html( 'h2 id="prompt-settings-header"', html( 'span', $this->args['page_title'] ) );
	}

	protected function is_subscribe_widget_in_use() {
		$sidebars_widgets = wp_get_sidebars_widgets();
		$subscribe_widget_in_use = false;
		foreach( $sidebars_widgets as $sidebar => $widgets ) {
			foreach ( $widgets as $widget ) {
				if ( strpos( $widget, 'prompt_subscribe_widget' ) === 0 )
					$subscribe_widget_in_use = true;
			}
		}
		return $subscribe_widget_in_use;
	}

	function page_content() {

		echo $this->log_alert();

		if ( !$this->options->get( 'prompt_key' ) ) {

			self::render_key_form();

			echo html( 'div class="initialize-key"',
				html( 'h2', __( 'Have a key?', 'Prompt_Core' ) ),
				$this->form_table( array(
					array(
						'title' => __( 'Postmatic Key', 'Prompt_Core' ),
						'type' => 'text',
						'name' => 'prompt_key',
						'desc' => sprintf(
							'%s<br/>%s <a href="http://gopostmatic.com/tos" target="_blank">%s</a>.',
							__( 'Once you have your key, enter it here to blast off!', 'Prompt_Core' ),
							__( 'By entering a valid api key and activating Postmatic you agree to the', 'Prompt_Core' ),
							__( 'terms of service', 'Prompt_Core' )
						)
					),
				) )
			);

			return;
		}

		if ( !$this->options->get( 'skip_widget_intro' ) ) {
			echo $this->widget_intro();
		}

		list( $tabs, $panels ) = $this->tabs_content();

		echo $this->sidebar_content();

		echo html(
			'div id="prompt-tabs"',
			html( 'ul',
				$tabs
			),
			$panels
		);
	}

	protected function log_alert() {
		$dismiss_time = absint( get_user_meta( get_current_user_id(), self::DISMISS_ERRORS_META_KEY, true ) );

		$log = Prompt_Logging::get_log( $dismiss_time );

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
				get_submit_button( __( 'Dismiss', 'Prompt_Core' ), 'primary large', 'dismiss_errors' ),
				get_submit_button( __( 'Submit A Bug Report', 'Prompt_Core' ), 'left', 'submit_errors' )
			)
		);
	}

	protected function widget_intro() {

		$sidebars = wp_get_sidebars_widgets();

		$dismiss_link = html( 'a', array( 'href' => add_query_arg( 'skip_widget_intro', 'true' ), 'class' => 'button' ), __( 'Dismiss' ) );
		if ( empty( $sidebars ) ) {
			$content = html(
				'p',
				__( 'Your current theme has no widget areas. This means you\'ll have to use the template tag to display the Postmatic Subscription widget.', 'Prompt_Core' ),
				html( 'pre class="code"', htmlentities( '<?php the_widget( \'Prompt_Subscribe_Widget\', array( \'title\' => \'Subscribe by email\', \'collect_name\' => false ) ); ?>' ) ),
				'&nbsp;',
				$dismiss_link
			);
		} else {
			$content = html(
				'p',
				__( 'To get started now, place the Postmatic Subscribe widget where people can use it to subscribe!', 'Prompt_Core' ),
				'&nbsp;',
				html( 'a', array( 'href' => admin_url( 'widgets.php' ), 'class' => 'button' ), __( 'Visit Your Widgets' ) ),
				'&nbsp;',
				$dismiss_link
			);
		}

		return html( 'div class="error"', $content );
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
			$this->support_content(),
			$this->news_content()
		);
	}

	/**
	 * Assemble support widget content
	 * @return string content
	 */
	protected function support_content() {
		return html(
			'div class="ui-widget"',
			html( 'div class="ui-widget-header ui-corner-top"', __( 'Get Support', 'Prompt_Core' ) ),
			html(
				'div class="ui-widget-content ui-corner-bottom"',
				html(
					'a',
					array( 'href' => self::SUPPORT_URL ),
					__( 'View the FAQ and submit support requests at gopostmatic.com.', 'Prompt_Core' )
				)
			)
		);
	}

	/**
	 * Assemble news widget content
	 * @return string content
	 */
	protected function news_content() {
		$feed = fetch_feed( 'http://gopostmatic.com/feed' );

		if ( is_wp_error( $feed ) )
			return __( 'No news available at the moment.', 'Prompt_Core' );

		$item_count = $feed->get_item_quantity( 3 );

		/** @var SimplePie_Item[] $items */
		$items = $feed->get_items( 0, $item_count );
		
		$news_items = '';
		foreach ( $items as $item ) {
			$news_items .= html(
				'li',
				html( 'small', $item->get_date( 'j F Y ' ) ),
				html( 'a', array( 'href' => $item->get_permalink() ), $item->get_title() )
			);
		}

		return html(
			'div class="ui-widget"',
			html( 'div class="ui-widget-header ui-corner-top"', __( 'Postmatic News', 'Prompt_Core' ) ),
			html(
				'div class="ui-widget-content ui-corner-bottom"',
				html( 'ul class="prompt-news"', $news_items )
			)
		);
	}

	protected function render_key_form() {

		$user = wp_get_current_user();

		$lead = html(
			'div',
			array( 'class' => 'description' ),
			html( 'h1', __( 'Welcome to Postmatic. Let\'s get started.', 'Prompt_Core' ) ),
			html( 'p', __( 'Postmatic is in limited-access beta and requires an activation key.', 'Prompt_Core' ) ),
			html( 'h2', __( 'Need a key?', 'Prompt_Core' ) )
		);

		$key_url = self::KEY_URL . '/sites/link?ajax_url=' . urlencode( admin_url( 'admin-ajax.php' ) );

		$rows = array(
			$this->row_wrap(
				__( 'First Name', 'Prompt_Core' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'first_name',
						'value' => $user->first_name,
					),
					wp_unslash( $_POST )
				)
			),
			$this->row_wrap(
				__( 'Last Name', 'Prompt_Core' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'last_name',
						'value' => $user->last_name,
					),
					wp_unslash( $_POST )
				)
			),
			$this->row_wrap(
				__( 'Email', 'Prompt_Core' ),
				$this->input(
					array(
						'type' => 'text',
						'name' => 'email',
						'value' => $user->user_email,
					),
					wp_unslash( $_POST )
				)
			)
		);

		echo html(
			'div',
			array( 'class' => 'get-prompt-key' ),
			$lead,
			$this->form_wrap(
				html( 'div', $this->table_wrap( implode( '', $rows ) ) ),
				array(
					'action' => 'send_beta_request',
					'value' => __( 'Request a free Postmatic Api Key', 'Prompt_Core' ),
					'class' => 'button-primary',
				)
			)
		);
	}

	protected function add_ons_content() {
		$content = '';

		$available_add_ons = Prompt_Add_On_Manager::available_add_ons();

		foreach( $available_add_ons as $add_on_core => $add_on ) {

			$active = class_exists( $add_on_core );
			if ( $active ) {
				$status = html(
					'p class="ui-state-highlight"',
					html( 'span', array( 'class' => 'ui-icon ui-icon-check' ) ),
					__( 'Installed and Active', 'Prompt_Core' )
				);
			} else {
				$status = html(
					'a',
					array( 'href' => $add_on['PluginURI'], 'class' => 'button', 'target' => '_blank' ),
					__( 'Purchase', 'Prompt_Core' )
				);
			}

			$content .= html(
				'div class="add-on"',
				html_link( $add_on['PluginURI'], html( 'h3', $add_on['Name'] ) ),
				html( 'p', $add_on['Description'] ),
				$status
			);
		}

		return html( 'div class="add-on-group"', $content );
	}

	public function validate( $new_data, $old_data ) {
		$valid_data = $old_data;

		if ( isset( $new_data['prompt_key'] ) and $new_data['prompt_key'] != $old_data['prompt_key'] ) {
			$key = $this->validate_key( $new_data['prompt_key'] );
			if ( is_wp_error( $key ) )
				add_settings_error( 'prompt_key', 'invalid_key', $key->get_error_message() );
			else
				$valid_data['prompt_key'] = $key;
		}

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
				__( 'There\'s a problem verifying your key. Please try later or report this error.', 'Prompt_Core' ),
				$response
			);
		}

		if ( 401 == $response['response']['code'] ) {
			$message = sprintf(
				__( 'We didn\'t recognize the key "%s". Please make sure it matches the one we gave you.', 'Prompt_Core' ),
				$key
			);
			return new WP_Error( 'invalid_key', $message );
		}

		$configurator = Prompt_Factory::make_configurator( $client );

		$configurator->update_configuration( json_decode( $response['body'] ) );

		return $key;
	}

	protected function submit_errors() {
		$user = wp_get_current_user();

		$email = new Prompt_Email();
		$email->set_to_address( self::SUPPORT_EMAIL );
		$email->set_from_address( $user->user_email );
		$email->set_from_name( $user->display_name );
		$email->set_subject( sprintf( 'Error submission from %s', get_option( 'blogname' ) ) );
		$email->set_content_type( 'application/json' );
		$email->set_template( '' );

		$last_submit_time = absint( get_option( self::BUG_REPORT_OPTION_NAME ) );

		$options = array_diff_key( Prompt_Core::$options->get(), array( 'prompt_key' => '' ) );

		$message = array(
			'url' => get_option( 'siteurl' ),
			'options' => $options,
			'version' => Prompt_Core::version( $full = true ),
			'error_log' => Prompt_Logging::get_log( $last_submit_time ),
		);

		$environment = new Prompt_Environment();

		$message = array_merge( $message, $environment->to_array() );

		$email->set_message( json_encode( $message ) );

		Prompt_Factory::make_mailer()->send_one( $email );

		update_option( self::BUG_REPORT_OPTION_NAME, time() );

		add_action( 'admin_notices', array( $this, 'submitted_errors_admin_msg' ) );
	}

	protected function send_beta_request() {

		if ( !is_email( $_POST['email'] ) ) {
			add_settings_error(
				'send_beta_request',
				'invalid_email',
				__( 'Please enter a valid email address.', 'Prompt_Core' )
			);
			return;
		}

		wp_mail(
			'postmatic@robot.zapier.com',
			'Postmatic Beta Key Request',
			sprintf(
				"first: %s\nlast: %s\nurl: %s",
				$_POST['first_name'],
				$_POST['last_name'],
				site_url()
			),
			array(
				'From: ' . $_POST['email'],
				'Cc: jason@gopostmatic.com',
			)
		);

		add_action( 'admin_notices', array( $this, 'beta_request_sent_admin_msg' ) );
	}

}