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
			'collect_name' => true,
			'template_path' => null,
			'subscribe_prompt' => null,
		);

		$instance = wp_parse_args( $instance, $instance_defaults );

		$this->enqueue_widget_assets();

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];

		$container_attributes = array(
			'class' => 'prompt-subscribe-widget-content',
			'data-widget-id' => $this->id,
			'data-template' => self::template_id( $instance['template_path'] ),
			'data-collect-name' => (int) $instance['collect_name'],
			'data-subscribe-prompt' => $instance['subscribe_prompt'],
		);

		echo html( 'div',$container_attributes );

		echo $args['after_widget'];
	}


	// Update Widget
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['collect_name'] = isset( $new_instance['collect_name'] ) ? true : false;
		$instance['subscribe_prompt'] = sanitize_text_field( $new_instance['subscribe_prompt'] );

		return $instance;
	}


	// Display Widget Control
	public function form( $instance ) {
		$template = new Prompt_Template( 'subscribe-widget-settings.php' );
		$template_data = array( 'widget' => $this, 'instance' => $instance );
		$template->render( $template_data, $echo = true );
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
	 * @type boolean $collect_name
	 * }
	 * @param Prompt_Interface_Subscribable $object Target object for subscriptions
	 * @param string $template_id
	 */
	public static function render_dynamic_content( $widget_id, $instance, $object, $template_id = null ) {

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

		$loading_image_url = path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' );

		$unsubscribe_prompt = self::unsubscribe_prompt();

		$template_data = compact(
			'widget_id',
			'instance',
			'user',
			'object',
			'action',
			'defaults',
			'loading_image_url',
			'unsubscribe_prompt'
		);

		if ( is_null( $template_id ) )
			$template = new Prompt_Template( 'subscribe-form.php' );
		else
			$template = new Prompt_Template( self::template_path( $template_id ) );

		$template->render( $template_data, $echo = true );
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

	protected static function template_id( $template_path ) {

		if ( !$template_path )
			return null;

		$templates = Prompt_Core::$options->get( 'custom_widget_templates' );

		$reverse = array_flip( $templates );

		if ( isset( $reverse[$template_path] ) )
			return $reverse[$template_path];

		$count = array_push( $templates, $template_path );

		Prompt_Core::$options->set( 'custom_widget_templates', $templates );

		return $count - 1;
	}

	protected static function template_path( $template_id ) {
		$templates = Prompt_Core::$options->get( 'custom_widget_templates' );

		return isset( $templates[$template_id] ) ? $templates[$template_id] : null;
	}

	protected static function subscribe_prompt( $instance, Prompt_Interface_Subscribable $object ) {

		if ( !empty( $instance['subscribe_prompt'] ) )
			return esc_html( $instance['subscribe_prompt'] );

		if ( is_user_logged_in() )
			return sprintf( __( 'Subscribe to %s:', 'Postmatic' ), $object->subscription_object_label() );

		return sprintf(
			__( 'Enter your email to subscribe to %s:', 'Postmatic' ), $object->subscription_object_label()
		);
	}

	protected static function unsubscribe_prompt() {

		if ( is_user_logged_in() )
			return '';

		return html( 'h5', __( 'Want to unsubscribe?', 'Postmatic' ) ) . __( 'Confirm your email:', 'Postmatic' );
	}
 }
