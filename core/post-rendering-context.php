<?php

class Prompt_Post_Rendering_Context {

	/** @var bool */
	protected $is_setup = false;
	/** @var WP_Post */
	protected $post;
	/** @var  array */
	protected $featured_image_src = null;
	/** @var  Prompt_Post_Rendering_Modifier[] */
	protected $modifiers;

	public function __construct( $post_object_or_id, $modifiers = null ) {
		$this->post = get_post( $post_object_or_id );
		$this->modifiers = $modifiers;
	}

	/**
	 * Set up the global environment needed to render a post email.
	 * @var WP_Post $post
	 */
	public function setup() {

		query_posts( array(
			'p' => $this->post->ID,
			'post_type' => $this->post->post_type,
			'post_status' => $this->post->post_status
		) );

		the_post();

		$this->is_setup = true;

		$this->add_modifiers();

		$this->modifiers = apply_filters( 'prompt/post_rendering_context/modifiers', $this->modifiers, $this->post );

		foreach( $this->modifiers as $modifier ) {
			$modifier->setup();
		}

	}

	/**
	 * Reset the global environment after rendering post emails.
	 */
	public function reset() {

		wp_reset_query();

		$this->is_setup = false;

		foreach( $this->modifiers as $modifier ) {
			$modifier->reset();
		}

	}

	/**
	 * Get Postmatic's text version of the current post content.
	 * @return mixed|string
	 */
	public function get_the_text_content() {

		$this->ensure_setup();

		$prompt_post = new Prompt_Post( $this->post );

		$text = $prompt_post->get_custom_text();

		if ( $text )
			return $text;

		if ( Prompt_Admin_Delivery_Metabox::excerpt_only( $prompt_post->id() ) )
			return Prompt_Html_To_Markdown::convert( get_the_excerpt() );

		$html = apply_filters( 'the_content', get_the_content() );

		$html = str_replace( ']]>', ']]&gt;', $html );

		return Prompt_Html_To_Markdown::convert( $html );
	}

	/**
	 * Get the array with the featured image url, width, and height (or false).
	 */
	public function get_the_featured_image_src() {

		$this->ensure_setup();

		if ( !is_null( $this->featured_image_src ) )
			return $this->featured_image_src;

		$this->featured_image_src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'prompt-post-featured' );

		if ( Prompt_Admin_Delivery_Metabox::suppress_featured_image( $this->post->ID ) )
			$this->featured_image_src = false;

		return $this->featured_image_src;
	}

	/**
	 * @return string Menu HTML.
	 */
	public function alternate_versions_menu() {
		global $polylang;

		if ( ! class_exists( 'PLL_Switcher' ) )
			return '';

		$switcher = new PLL_Switcher();

		$languages = $switcher->the_languages(
			$polylang->links,
			array(
				'post_id' => $this->post->ID,
				'echo' => false,
				'hide_if_no_translation' => true,
				'hide_current' => true,
			)
		);

		return empty( $languages ) ? '' : html( 'ul class="alternate-languages"', $languages );
	}

	/**
	 * @return bool whether the post has content that would be stripped by strip_fancy_content()
	 */
	public function has_fancy_content() {

		if ( stripos( $this->post->post_content, '<img' ) !== false )
			return true;

		if ( stripos( $this->post->post_content, '<iframe'  ) !== false )
			return true;

		if ( stripos( $this->post->post_content, '<object' ) !== false )
			return true;

		$sans_shortcodes = strip_shortcodes( $this->post->post_content );

		return ( $sans_shortcodes != $this->post->post_content );
	}

	protected function ensure_setup() {

		if ( ! $this->is_setup ) {
			$this->setup();
			return;
		}

		if ( get_the_ID() != $this->post->ID ) {
			// A widget or something has messed up the global query - redo it
			$this->setup();
		}

	}

	/**
	 * @since 1.4.0
	 */
	protected function add_modifiers() {

		if ( $this->modifiers )
			return;

		$this->modifiers = array();

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) ) {
			$this->modifiers[] = new Prompt_Local_Post_Rendering_Modifier();
			return;
		}

		$this->modifiers[] = new Prompt_Shortcode_Post_Rendering_Modifier();
		$this->modifiers[] = new Prompt_Incompatible_Post_Rendering_Modifier();
		$this->modifiers[] = new Prompt_Lazy_Load_Post_Rendering_Modifier();
		$this->modifiers[] = new Prompt_Image_Post_Rendering_Modifier( $this->get_the_featured_image_src() );

		if ( Prompt_Core::$options->get( 'enable_skimlinks' ) )
			$this->modifiers[] = new Prompt_Skimlinks_Post_Rendering_Modifier();
		
		if ( class_exists( 'ET_Bloom' ) )
			$this->modifiers[] = new Prompt_Bloom_Post_Rendering_Modifier();

		if ( class_exists( 'Jetpack' ) )
			$this->modifiers[] = new Prompt_Jetpack_Post_Rendering_Modifier();

	}
}