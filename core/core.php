<?php

class Prompt_Core {
	/** @var string */
	static public $dir_path;
	/** @var string */
	static public $basename;
	/** @var string */
	static public $url_path;
	/** @var  scbOptions  */
	static public $options;

	static protected $active_add_ons = array();
	static protected $version = '';
	static protected $full_version = '';

	static protected $settings_page = null;
	static protected $delivery_metabox = null;

	static private $overridden_options;

	public static function load() {
		self::$dir_path = dirname( dirname( __FILE__ ) );
		self::$basename = plugin_basename( self::$dir_path . '/postmatic.php' );
		self::$url_path = plugins_url( '', dirname( __FILE__ ) );

		load_plugin_textdomain( 'Postmatic', '', path_join( dirname( self::$basename ), 'lang' ) );

		scb_init();

		add_action( 'plugins_loaded', array( __CLASS__, 'action_plugins_loaded' ) );

	}

	/**
	 * Instantiate SCB framework classes.
	 */
	public static function action_plugins_loaded() {
		$invite_intro = __( 'This is an invitation to subscribe to email updates from this website. We hope it is welcome, but we promise we won\'t contact you again unless you respond.', 'Postmatic' );
		$default_options = array(
			'auto_subscribe_authors' => true,
			'prompt_key' => '',
			'site_subscription_post_types' => array( 'post' ),
			'site_id' => null,
			'skip_widget_intro' => false,
			'skip_akismet_intro' => false,
			'skip_zero_spam_intro' => false,
			'augment_comment_form' => true,
			'send_login_info' => false,
			'email_header_type' => Prompt_Enum_Email_Header_Types::TEXT,
			'email_header_image' => 0,
			'email_header_text' => get_option( 'blogname' ),
			'email_footer_type' => Prompt_Enum_Email_Footer_Types::WIDGETS,
			'email_footer_text' => '',
			'plan' => '',
			'email_transport' => Prompt_Enum_Email_Transports::API,
			'messages' => array( 'welcome' => __( 'Welcome!', 'Postmatic' ) ),
			'invite_introduction' => $invite_intro,
			'last_version' => 0,
			'enable_collection' => false,
		);
		self::$options = new scbOptions( 'prompt_options', __FILE__, $default_options );

		/**
		 * Filter overridden options.
		 *
		 * @param array $overridden_options
		 * @param array $current_options
		 */
		$filtered_options = apply_filters( 'prompt/override_options', array(), self::$options->get() );
		self::$overridden_options = wp_array_slice_assoc( $filtered_options, array_keys( self::$options->get() ) );
		if ( !empty( self::$overridden_options ) )
			self::$options->set( self::$overridden_options );

		// Until we have a key we won't do much
		if ( self::$options->get( 'prompt_key' ) )
			self::add_hooks();

		if ( is_admin() ) {
			self::settings_page();
			self::delivery_metabox();
		}

		do_action( 'prompt/core_loaded' );
	}

	/**
	 * Register the WordPress hooks we will respond to.
	 */
	protected static function add_hooks() {

		register_deactivation_hook( self::$basename, array( 'Prompt_Event_Handling', 'record_deactivation' ) );
		register_activation_hook( self::$basename, array( 'Prompt_Event_Handling', 'record_reactivation' ) );

		add_action( 'admin_init', array( __CLASS__, 'detect_version_change' ) );

		add_action( 'widgets_init', array( 'Prompt_Widget_Handling', 'register' ), 100 ); // Let theme load first

		add_action( 'wp_ajax_nopriv_prompt/pull-updates', array( 'Prompt_Web_Api_Handling', 'receive_pull_updates' ) );
		add_action( 'wp_ajax_nopriv_prompt/pull-configuration', array( 'Prompt_Web_Api_Handling', 'receive_pull_configuration' ) );

		add_action( 'transition_post_status', array( 'Prompt_Outbound_Handling', 'action_transition_post_status' ), 10, 3 );
		add_action( 'wp_insert_comment', array( 'Prompt_Outbound_Handling', 'action_wp_insert_comment' ), 10, 2 );
		add_action( 'transition_comment_status', array( 'Prompt_Outbound_Handling', 'action_transition_comment_status' ), 10, 3 );

		add_action( 'prompt/post_mailing/send_notifications', array( 'Prompt_Post_Mailing', 'send_notifications' ) );
		add_action( 'prompt/comment_mailing/send_notifications', array( 'Prompt_Comment_Mailing', 'send_notifications' ) );
		add_action( 'prompt/subscription_mailing/send_agreements', array( 'Prompt_Subscription_Mailing', 'send_agreements' ), 10, 4 );

		add_action( 'wp_ajax_prompt_subscribe', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_subscribe' ) );
		add_action( 'wp_ajax_nopriv_prompt_subscribe', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_subscribe' ) );
		add_action( 'wp_ajax_prompt_subscribe_widget_content', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_subscribe_widget_content' ) );
		add_action( 'wp_ajax_nopriv_prompt_subscribe_widget_content', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_subscribe_widget_content' ) );
		add_action( 'wp_ajax_prompt_get_commenters', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_get_commenters' ) );
		add_action( 'wp_ajax_prompt_get_invite_users', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_get_invite_users' ) );
		add_action( 'wp_ajax_prompt_comment_unsubscribe', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_comment_unsubscribe' ) );
		add_action( 'wp_ajax_nopriv_prompt_comment_unsubscribe', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_comment_unsubscribe' ) );
		add_action( 'wp_ajax_prompt_post_delivery_status', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_post_delivery_status' ) );
		add_action( 'wp_ajax_prompt_post_delivery_preview', array( 'Prompt_Ajax_Handling', 'action_wp_ajax_prompt_post_delivery_preview' ) );

		add_action( 'deleted_user', array( 'Prompt_User_Handling', 'delete_subscriptions' ) );
		add_action( 'edit_user_profile', array( 'Prompt_User_Handling', 'render_profile_options' ) );
		add_action( 'show_user_profile', array( 'Prompt_User_Handling', 'render_profile_options' ) );
		add_action( 'edit_user_profile_update', array( 'Prompt_User_Handling', 'update_profile_options' ) );
		add_action( 'personal_options_update', array( 'Prompt_User_Handling', 'update_profile_options' ) );

		add_action( 'comment_form', array( 'Prompt_Comment_Form_Handling', 'form_content' ) );
		add_action( 'comment_post', array( 'Prompt_Comment_Form_Handling', 'handle_form' ), 10, 2 );
		add_action( 'comment_form_after', array( 'Prompt_Comment_Form_Handling', 'after_form' ) );

		add_action( 'admin_enqueue_scripts', array( 'Prompt_Admin_Users_Handling', 'enqueue_scripts' ) );
		add_filter( 'manage_users_columns', array( 'Prompt_Admin_Users_Handling', 'manage_users_columns' ) );
		add_filter( 'manage_users_custom_column', array( 'Prompt_Admin_Users_Handling', 'subscriptions_column' ), 10, 3 );

		add_action( 'admin_post_prompt_subscribers_export_csv', array( 'Prompt_Admin_Subscribers_Export', 'export_subscribers_csv' ) );

		add_image_size( 'prompt-post-featured', 1480, 600, true );
	}

	public static function detect_version_change() {

		if ( self::version() == self::$options->get( 'last_version' ) )
			return;

		self::$options->set( 'last_version', self::version() );

		if ( self::$options->get( 'enable_collection' ) )
			Prompt_Event_Handling::record_environment();
	}

	/**
	 * Get the plugin version.
	 *
	 * @param bool $full If true, append build or commit. Default false.
	 * @return string
	 */
	public static function version( $full = false ) {
		if ( $full and self::$full_version )
			return self::$full_version;

		if ( !$full and self::$version )
			return self::$version;

		$build_file = path_join( self::$dir_path, 'version' );

		if ( file_exists( $build_file ) ) {
			self::$full_version = file_get_contents( $build_file );
			$parts = explode( '-', self::$full_version );
			self::$version = $parts[0];
			return $full ? self::$full_version : self::$version;
		}

		// This is not a built package, dig around some more

		if ( !function_exists( 'get_plugin_data' ) )
			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		$plugin_data = get_plugin_data( self::$dir_path . '/postmatic.php' );
		self::$version = $plugin_data['Version'];

		if ( !$full )
			return self::$version;

		if ( getenv( 'CI' ) )
			return self::$version . '-' . getenv( 'CI_COMMIT_ID' );

		$head = path_join( self::$dir_path, '.git/HEAD' );

		if ( !file_exists( $head ) )
			return self::$version;

		$ref = path_join( self::$dir_path, '.git/' . trim( substr( file_get_contents( $head ), 5 ) ) );

		if ( !file_exists( $ref ) )
			return self::$version;

		self::$full_version =  trim( self::$version . '-' . file_get_contents( $ref ) );

		return self::$full_version;
	}

	/**
	 * @return Prompt_Admin_Options_Page
	 */
	public static function settings_page() {
		if ( !self::$settings_page )
			self::$settings_page = new Prompt_Admin_Options_Page(
				self::$dir_path . '/postmatic.php',
				self::$options,
				self::$overridden_options
			);

		return self::$settings_page;
	}

	/**
	 * @return Prompt_Admin_Delivery_Metabox
	 */
	public static function delivery_metabox() {
		if ( !self::$delivery_metabox ) {

			self::$delivery_metabox = new Prompt_Admin_Delivery_Metabox(
				'prompt_delivery',
				__( 'Postmatic Delivery', 'Postmatic' ),
				array(
					'post_type' => self::$options->get( 'site_subscription_post_types' ),
					'context' => 'side',
					'priority' => 'high',
				)
			);
		}

		return self::$delivery_metabox;
	}

} // end Prompt_Core class
