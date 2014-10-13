<?php

class Prompt_Admin_Delivery_Metabox extends scbPostMetabox {

	/** @var string */
	static protected $no_email_name = 'prompt_no_email';
	/** @var string */
	static protected $no_featured_image_name = 'prompt_no_featured_image';
	/** @var string */
	static protected $preview_email_name = 'prompt_preview_email';

	/** @var WP_Post */
	protected $post;
	/** @var  array */
	protected $recipient_ids;
	/** @var  array */
	protected $sent_recipient_ids;

	/**
	 * Find out if the "no email" metabox checkbox was checked.
	 * @return bool
	 */
	public static function suppress_email() {
		return isset( $_POST[self::$no_email_name] );
	}

	/**
	 * Find out if the "no featured image" metabox checkbox was checked.
	 * @return bool
	 */
	public static function suppress_featured_image() {
		return isset( $_POST[self::$no_featured_image_name] ) or isset( $_GET[self::$no_featured_image_name] );
	}

	public function admin_enqueue_scripts() {
		$script = new Prompt_Script( array(
			'handle' => 'prompt-post-editor',
			'path' => 'js/post-editor.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();
	}

	public function render_status() {

		return html( 'p',
			array( 'class' => 'status' ),
			html( 'span', array( 'class' => 'spinner' ) )
		);

	}

	public function display( $post ) {
		$this->set_post( $post );
		echo $this->render_status();
		echo $this->render_form();
	}

	public function render_form() {
		$form_html = '';

		if ( 'publish' == $this->post->post_status or count( $this->sent_recipient_ids ) >= count( $this->recipient_ids ) )
			return $form_html;

		$form_html .= html( 'p',
			scbForms::input(
				array(
					'type' => 'checkbox',
					'name' => self::$no_email_name,
					'desc' => __( 'Do not deliver this post via email.', 'Prompt_Core' ),
				)
			)
		);

		$form_html .= html( 'p',
			scbForms::input(
				array(
					'type' => 'checkbox',
					'name' => self::$no_featured_image_name,
					'desc' => __( 'Do not use the featured image in email.', 'Prompt_Core' ),
				)
			)
		);

		$form_html .= html( 'p',
			html( 'input',
				array(
					'type' => 'submit',
					'name' => self::$preview_email_name,
					'value' => __( 'Send me a preview email', 'Prompt_Core' ),
					'class' => 'button',
				)
			)
		);

		return $form_html;
	}

	protected function set_post( $post ) {
		$this->post = $post;
		$prompt_post = new Prompt_Post( $post );
		$this->recipient_ids = $prompt_post->recipient_ids();
		$this->sent_recipient_ids = $prompt_post->sent_recipient_ids();
	}

}