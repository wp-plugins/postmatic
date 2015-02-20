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
	 * Find out if the "no email" metabox checkbox was checked for a post.
	 * @param int $post_id
	 * @return bool
	 */
	public static function suppress_email( $post_id ) {

		if ( isset( $_POST[self::$no_email_name] ) and isset( $_POST['post_ID'] ) and $_POST['post_ID'] == $post_id )
			return true; // Meta hasn't been saved yet but will be

		$meta_value = get_post_meta( $post_id, self::$no_email_name, true );

		if ( '' !== $meta_value )
			return (bool) $meta_value;

		return Prompt_Core::$options->get( 'no_post_email_default' );
	}

	/**
	 * Find out if the "no featured image" metabox checkbox was checked.
	 * @param int $post_id
	 * @return bool
	 */
	public static function suppress_featured_image( $post_id ) {

		if ( isset( $_GET['action'] ) and 'prompt_post_delivery_preview' == $_GET['action'] )
			return intval( $_GET['post_id'] ) == $post_id and !empty( $_GET[self::$no_featured_image_name] );

		if (
			isset( $_POST['post_ID'] ) and
			intval( $_POST['post_ID'] ) == $post_id and
			isset( $_POST[self::$no_featured_image_name] )
		) {
			return true; // Meta hasn't been saved yet but will be
		}

		$meta_value = get_post_meta( $post_id, self::$no_featured_image_name, true );

		if ( '' != $meta_value )
			return (bool) $meta_value;

		return Prompt_Core::$options->get( 'no_post_featured_image_default' );
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
					'desc' => __( 'Do not deliver this post via email.', 'Postmatic' ),
					'checked' => self::suppress_email( $this->post->ID ),
				)
			)
		);

		$form_html .= html( 'p',
			scbForms::input(
				array(
					'type' => 'checkbox',
					'name' => self::$no_featured_image_name,
					'desc' => __( 'Do not use the featured image in email.', 'Postmatic' ),
					'checked' => self::suppress_featured_image( $this->post->ID ),
				)
			)
		);

		$form_html .= html( 'p',
			html( 'input',
				array(
					'type' => 'submit',
					'name' => self::$preview_email_name,
					'value' => __( 'Send me a preview email', 'Postmatic' ),
					'class' => 'button',
				)
			)
		);

		return $form_html;
	}

	protected function before_save( $post_data, $post_id ) {
		$post_data =  array(
			self::$no_email_name => isset( $_POST[self::$no_email_name] ),
			self::$no_featured_image_name => isset( $_POST[self::$no_featured_image_name] ),
		);

		// Make changes to featured image suppression sticky
		if ( $post_data[self::$no_featured_image_name] != Prompt_Core::$options->get( 'no_post_featured_image_default' ) )
			Prompt_Core::$options->set( 'no_post_featured_image_default', $post_data[self::$no_featured_image_name] );

		return $post_data;
	}

	protected function set_post( $post ) {
		$this->post = $post;
		$prompt_post = new Prompt_Post( $post );
		$this->recipient_ids = $prompt_post->recipient_ids();
		$this->sent_recipient_ids = $prompt_post->sent_recipient_ids();
	}

}