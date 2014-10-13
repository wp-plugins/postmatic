<?php

class Prompt_Subscribe_Widget extends WP_Widget {

	// Construct Widget
	public function __construct() {
		$default_options = array(
			'description' => __( 'Get visitors subscribed to a user, post, or site with minimal fuss.', 'Prompt_Core' )
		);
		parent::__construct( false, __( 'Postmatic Subscribe', 'Prompt_Core' ), $default_options );
	}

	// Display Widget
	public function widget( $args, $instance ) {

		$this->enqueue_widget_assets();

		$commenter = wp_get_current_commenter();
		$defaults = array(
			'subscribe_name' => $commenter['comment_author'] ? $commenter['comment_author'] : '',
			'subscribe_email' => $commenter['comment_author_email'] ? $commenter['comment_author_email'] : '',
		);

		$user = wp_get_current_user();
		if ( !$user->exists() )
			$user = get_user_by( 'email', $defaults['subscribe_email'] );

		$default_object = get_queried_object();

		// The widget will offer site subscriptions on single posts
		$default_object = is_a( $default_object, 'WP_Post' ) ? null : $default_object;

		$object = isset( $instance['object'] ) ? $instance['object'] : $default_object;
		/**
		 * Filter the target object for the subscription widget.
		 *
		 * @param object $object The post, user, etc.
		 * @param Prompt_Subscribe_Widget $widget
		 * @param array $instance The widget instance data.
		 */
		$object = apply_filters( 'prompt/subscribe_widget_object', $object, $this, $instance );

		$object = Prompt_Subscribing::make_subscribable( $object );

		if ( $user and $object->is_subscribed( $user->ID ) ) {
			//TODO: Add readable submit address for groups?
			$action = $this->unsubscribe_action();
		} else {
			$action = $this->subscribe_action();
		}

		$widget = $this;
		$template_data = compact( 'widget', 'instance', 'user', 'object', 'action', 'defaults' );

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];

		$template = Prompt_Template::locate( 'subscribe-form.php' );
		Prompt_Template::render( $template, $template_data );

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

	public function subscribe_action() {
		return __( 'subscribe', 'Prompt_Core' );
	}

	public function unsubscribe_action() {
		return __( 'unsubscribe', 'Prompt_Core' );
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

		$script->localize(
			'prompt_subscribe_form_env',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'spinner_url' => path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' ),
				'nonce' => wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE ),
				'subscribe_action' => $this->subscribe_action(),
				'unsubscribe_action' => $this->unsubscribe_action(),
				'ajax_error_message' => __( 'Sorry, there was a problem reaching the server', 'Prompt_Core' ),
			)
		);

	}

 }