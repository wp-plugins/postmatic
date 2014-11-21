<?php

class Prompt_Subscribe_Widget extends WP_Widget {

	// Construct Widget
	public function __construct() {
		$default_options = array(
			'description' => __( 'Get visitors subscribed to a user, post, or site with minimal fuss.', 'Postmatic' )
		);
		parent::__construct( false, __( 'Postmatic Subscribe', 'Postmatic' ), $default_options );
	}

	// Display Widget
	public function widget( $args, $instance ) {

		$instance_defaults = array(
			'title' => '',
			'collect_name' => false,
		);

		$instance = wp_parse_args( $instance, $instance_defaults );
		$this->enqueue_widget_assets();

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];

		echo html( 'div',
			array(
				'class' => 'prompt-subscribe-widget-content',
				'data-collect-name' => $instance['collect_name'],
				'data-widget-id' => $this->id,
			)
		);

		echo $args['after_widget'];
	}


	// Update Widget
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['collect_name'] = isset( $new_instance['collect_name'] ) ? true : false;

		return $instance;
	}


	// Display Widget Control
	public function form( $instance ) {
		$template = Prompt_Template::locate( 'subscribe-widget-settings.php' );
		$template_data = array( 'widget' => $this, 'instance' => $instance );
		Prompt_Template::render( $template, $template_data );
	 }

	// Default value logic
	public function get_default_value( $instance, $field, $fallback = '', $escape_callback = 'esc_attr' ) {
		if ( isset( $instance[$field] ) )
			$value = $instance[$field];
		else
			$value = $fallback;

		if ( function_exists( $escape_callback ) )
			$value = call_user_func( $escape_callback, $value );

		return $value;
	}

	public static function subscribe_action() {
		return __( 'subscribe', 'Postmatic' );
	}

	public static function unsubscribe_action() {
		return __( 'unsubscribe', 'Postmatic' );
	}

	/**
	 * Emit markup for the dynamic portion of the widget content.
	 *
	 * @param string $widget_id
	 * @param array $instance {
	 *      Widget options
	 *      @type boolean $collect_name
	 * }
	 * @param Prompt_Interface_Subscribable $object Target object for subscriptions
	 */
	public static function render_dynamic_content( $widget_id, $instance, $object ) {

		$commenter = wp_get_current_commenter();
		$defaults = array(
			'subscribe_name' => $commenter['comment_author'] ? $commenter['comment_author'] : '',
			'subscribe_email' => $commenter['comment_author_email'] ? $commenter['comment_author_email'] : '',
		);

		$user = wp_get_current_user();
		if ( !$user->exists() )
			$user = get_user_by( 'email', $defaults['subscribe_email'] );

		if ( $user and $object->is_subscribed( $user->ID ) ) {
			$action = self::unsubscribe_action();
		} else {
			$action = self::subscribe_action();
		}

		$template_data = compact( 'widget_id', 'instance', 'user', 'object', 'action', 'defaults' );

		$template = Prompt_Template::locate( 'subscribe-form.php' );
		Prompt_Template::render( $template, $template_data );

	}

	protected function enqueue_widget_assets() {

		wp_enqueue_style(
			'prompt-subscribe-form',
			path_join( Prompt_Core::$url_path, 'css/subscribe-form.css' ),
			array(),
			Prompt_Core::version()
		);

		$script = new Prompt_Script( array(
			'handle' => 'prompt-subscribe-form',
			'path' => 'js/subscribe-form.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();

		$target_object = $this->get_target_object();

		$script->localize(
			'prompt_subscribe_form_env',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'spinner_url' => path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' ),
				'nonce' => wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE ),
				'subscribe_action' => self::subscribe_action(),
				'unsubscribe_action' => self::unsubscribe_action(),
				'ajax_error_message' => __( 'Sorry, there was a problem reaching the server', 'Postmatic' ),
				'object_type' => get_class( $target_object ),
				'object_id' => $target_object->id(),
			)
		);

	}

	/**
	 * @return Prompt_Interface_Subscribable
	 */
	protected function get_target_object() {

		$default_object = get_queried_object();

		// The widget will offer site subscriptions on single posts
		$object = is_a( $default_object, 'WP_Post' ) ? null : $default_object;

		/**
		 * Filter the target object for the subscription widget.
		 *
		 * @param object $object The post, user, etc.
		 * @param Prompt_Subscribe_Widget $widget
		 * @param array $instance The widget instance data.
		 */
		$object = apply_filters( 'prompt/subscribe_widget_object', $object, $this );

		return Prompt_Subscribing::make_subscribable( $object );
	}
 }