<?php

class Prompt_Admin_Text_Metabox extends scbPostMetabox {

	/** @var string  */
	protected static $custom_text_name = 'prompt_custom_text';

	/** @var  Prompt_Post */
	protected $prompt_post;

	public function __construct( $id, $title, $args = array() ) {

		if ( isset( $_GET['post'] ) )
			$this->prompt_post = new Prompt_Post( intval( $_GET['post'] ) );

		if ( isset( $_POST['post_ID'] ) )
			$this->prompt_post = new Prompt_Post( intval( $_POST['post_ID'] ) );

		parent::__construct( $id, $title, $args );
	}

	public function admin_enqueue_scripts() {

		wp_enqueue_style(
			'prompt-admin',
			path_join( Prompt_Core::$url_path, 'css/admin.css' ),
			array(),
			Prompt_Core::version()
		);

		$script = new Prompt_Script( array(
			'handle' => 'prompt-text-metabox',
			'path' => 'js/text-metabox.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();

		$env = array(
			'custom_text_name' => self::$custom_text_name,
		);

		$script->localize( 'prompt_text_metabox_env', $env );
	}

	/**
	 * @param WP_Post $post
	 */
	public function display( $post ) {

		if ( ! $this->prompt_post )
			$this->prompt_post = new Prompt_Post( $post );

		if ( $post->ID != $this->prompt_post->id() )
			return;

		$sent = (bool) $this->prompt_post->sent_recipient_ids();

		if ( $sent ) {
			echo html( 'h3', __( 'This was the text version sent to subscribers.', 'Postmatic' ) );
		}

		$text = $this->prompt_post->get_custom_text();

		if ( !$sent and $text and 'publish' != $post->post_status ) {
			echo self::render_form( $text );
			return;
		}

		if ( 'publish' != $post->post_status )
			echo html( 'input type="button" class="button prompt-customize-text"',
				array( 'value' => __( 'Customize', 'Postmatic' ) )
			);

		if ( empty( $text ) and 'auto-draft' != $post->post_status ) {
			$context = new Prompt_Post_Rendering_Context( $post );
			$text = $context->get_the_text_content();
			$context->reset();
		}

		echo html( 'pre class="prompt-custom-text"', $text );

		echo html( 'div class="prompt-custom-text-upgrade"',
			sprintf(
				__( 'This post will be sent as a multipart message. We have automatically generated a plaintext version for older email clients. You can adjust it if you need to.', 'Postmatic' ),
				Prompt_Enum_Urls::PREMIUM
			)
		);
	}

	public function render_form( $text ) {
		return html( 'textarea class="prompt-custom-text"',
			array( 'name' => self::$custom_text_name ),
			$text
		);
	}

	protected function save( $post_id ) {

		if ( !isset( $_POST[self::$custom_text_name] ) or $post_id != $this->prompt_post->id() )
			return;

		if ( !current_user_can( 'edit_post', $post_id ) )
			return;

		$this->prompt_post->set_custom_text( $_POST[self::$custom_text_name] );
	}

}