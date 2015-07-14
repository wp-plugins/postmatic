<?php

class Prompt_Post_Rendering_Context {

	/** @var array */
	protected static $shortcode_whitelist = array(
		'gallery', 'caption', 'wpv-post-body', 'types', 'yumprint-recipe', 'ultimate-recipe', 'table'
	);

	/** @var bool */
	protected $is_setup = false;
	/** @var WP_Post */
	protected $post;
	/** @var  string */
	protected $original_content;
	/** @var  string */
	protected $original_excerpt;
	/** @var  array */
	protected $featured_image_src = null;
	/** @var  array */
	protected $gist_cache;

	public function __construct( $post_object_or_id ) {
		$this->post = get_post( $post_object_or_id );
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

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) ) {
			add_filter( 'the_content', array( $this, 'set_original_content' ), 1 );
			add_filter( 'the_excerpt', array( $this, 'set_original_excerpt' ), 1 );
			add_filter( 'the_content', array( $this, 'override_content_filters' ), 9999 );
			add_filter( 'the_excerpt', array( $this, 'override_excerpt_filters' ), 9999 );
			return;
		}

		if ( class_exists( 'ET_Bloom' ) ) {
			$bloom = ET_Bloom::get_this();
			// For Bloom < 1.0.5
			remove_filter( 'the_content', array( $bloom, 'display_below_post' ) );
			// For Bloom 1.0.5 and hopefully later
			remove_filter( 'the_content', array( $bloom, 'display_below_post' ), 9999 );
			remove_filter( 'the_content', array( $bloom, 'trigger_bottom_mark' ), 9999 );
			// TODO: restore these?
		}

		remove_shortcode( 'gallery' );
		add_shortcode( 'gallery', array( $this, 'suppress_jetpack_tiled_gallery' ) );

		remove_filter( 'the_content', 'do_shortcode', 11 );
		remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
		remove_filter( 'the_content', 'hammy_replace_images', 999 );
		add_filter( 'the_content', array( $this, 'do_whitelisted_shortcodes' ), 11 );
		add_filter( 'the_content', array( $this, 'strip_image_height_attributes' ), 11 );
		add_filter( 'the_content', array( $this, 'limit_image_width_attributes' ), 11 );
		add_filter( 'the_content', array( $this, 'strip_incompatible_tags' ), 11 );
		add_filter( 'the_content', array( $this, 'strip_duplicate_featured_images' ), 100 );
		add_filter( 'the_content', array( $this, 'include_noscript_content' ), 100 );
		add_filter( 'embed_oembed_html', array( $this, 'use_original_oembed_url' ), 10, 2 );
		add_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
		add_filter( 'do_rocket_lazyload', '__return_false' );
	}

	/**
	 * Reset the global environment after rendering post emails.
	 */
	public function reset() {

		wp_reset_query();

		$this->is_setup = false;

		if ( Prompt_Enum_Email_Transports::LOCAL == Prompt_Core::$options->get( 'email_transport' ) ) {
			remove_filter( 'the_excerpt', array( $this, 'override_excerpt_filters' ), 9999 );
			remove_filter( 'the_content', array( $this, 'override_content_filters' ), 9999 );
			return;
		}

		remove_shortcode( 'gallery' );
		add_shortcode( 'gallery', 'gallery_shortcode' );

		remove_filter( 'do_rocket_lazyload', '__return_false' );
		remove_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
		remove_filter( 'embed_oembed_html', array( $this, 'use_original_oembed_url' ), 10, 2 );
		remove_filter( 'the_content', array( $this, 'strip_incompatible_tags' ), 11 );
		remove_filter( 'the_content', array( $this, 'limit_image_width_attributes' ), 11 );
		remove_filter( 'the_content', array( $this, 'strip_image_height_attributes' ), 11 );
		remove_filter( 'the_content', array( $this, 'do_whitelisted_shortcodes' ), 11 );
		remove_filter( 'the_content', array( $this, 'strip_duplicate_featured_images' ), 100 );
		remove_filter( 'the_content', array( $this, 'include_noscript_content' ), 100 );
		add_filter( 'the_content', 'do_shortcode', 11 );
		add_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
		if ( function_exists( 'hammy_replace_images' ) )
			add_filter( 'the_content', 'hammy_replace_images', 999 );
	}

	/**
	 * @since 1.3.2
	 *
	 * @param string $content
	 * @return string
	 */
	public function set_original_content( $content ) {
		$this->original_content = $content;
		return $content;
	}

	/**
	 * @since 1.3.2
	 *
	 * @param string $excerpt
	 * @return string
	 */
	public function set_original_excerpt( $excerpt ) {
		$this->original_excerpt = $excerpt;
		return $excerpt;
	}

	/**
	 * @param $content
	 * @return string content with only our own filters applied
	 */
	public function override_content_filters( $content ) {
		return wpautop( wptexturize( $this->strip_fancy_content( $this->original_content ) ) );
	}

	/**
	 * @since 1.3.2
	 *
	 * @param $excerpt
	 * @return string
	 */
	public function override_excerpt_filters( $excerpt ) {
		return wp_trim_excerpt( wpautop( wptexturize( $this->original_excerpt ) ) );
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
	 * @param string $content
	 * @return string
	 */
	public function strip_image_height_attributes( $content ) {
		return preg_replace( '/(<img[^>]*?) height=["\']\d*["\']([^>]*?>)/', '$1$2', $content );
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function limit_image_width_attributes( $content ) {
		return preg_replace_callback(
			'/(<img[^>]*?) width=["\'](\d*)["\']([^>]*?>)/',
			array( __CLASS__, 'limit_image_width_attribute' ),
			$content
		);
	}

	public function limit_image_width_attribute( $match ) {
		$max_width = 709;

		$width = intval( $match[2] );

		if ( $width <= $max_width )
			return $match[0];

		$tag = $match[1] . ' width="' . $max_width . '"' . $match[3];

		return $this->add_image_size_class( $tag );
	}

	public function strip_incompatible_tags( $content ) {

		if ( false === strpos( $content, '<iframe' ) and false === strpos( $content, '<object' ) )
			return $content;

		$content = preg_replace_callback(
			'#<(iframe|object)([^>]*)(src|data)=[\'"]([^\'"]*)[\'"][^>]*>.*?<\\/\\1>#',
			array( $this, 'strip_incompatible_tag' ),
			$content
		);

		return $content;
	}

	public function strip_incompatible_tag( $m ) {
		$class = $m[1];

		$url_parts = parse_url( $m[4] );

		$url = null;
		if ( $url_parts and isset( $url_parts['host'] ) ) {
			$class = 'embed ' . str_replace( '.', '-', $url_parts['host'] );
			$url = $m[4];
		}

		return $this->incompatible_placeholder( $class, $url );
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function do_whitelisted_shortcodes( $content ) {
		global $shortcode_tags;

		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}

		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;

		add_filter( 'shortcode_atts_gallery', array( $this, 'override_gallery_attributes' ), 10, 3 );

		$pattern = get_shortcode_regex();
		$content = preg_replace_callback( "/$pattern/s", array( $this, 'do_whitelisted_shortcode_tag' ), $content );

		remove_filter( 'shortcode_atts_gallery', array( $this, 'override_gallery_attributes' ), 10, 3 );

		return $content;
	}

	/**
	 * @param array $m
	 * @return string
	 */
	public function do_whitelisted_shortcode_tag( $m ) {

		$tag = $m[2];

		if ( 'wpgist' == $tag )
			return $this->override_wp_gist_shortcode_tag( $m );

		if ( in_array( $tag, self::$shortcode_whitelist ) )
			return do_shortcode_tag( $m );

		return $m[1] . $this->incompatible_placeholder( $tag ) . $m[6];
	}

	/**
	 * @param array $atts
	 * @return string
	 */
	public function suppress_jetpack_tiled_gallery( $atts ) {

		// Jetpack adds the type attribute
		if ( isset( $atts['type'] ) )
			return '';

		return gallery_shortcode( $atts );
	}

	/**
	 * Use the old HTML 4 default gallery tags for better email (gmail) client support.
	 *
	 * @param array $out
	 * @param array $pairs
	 * @param array $atts
	 * @return array Overriden attributes.
	 */
	public function override_gallery_attributes( $out, $pairs, $atts ) {
		$out['itemtag'] = 'dl';
		$out['icontag'] = 'dt';
		$out['captiontag'] = 'dd';
		return $out;
	}

	/**
	 * Replace constructed provider URL with the original for placeholders.
	 *
	 * @see oembed_dataparse WordPress filter
	 *
	 * @param string $html
	 * @param string $url
	 * @return string
	 */
	public function use_original_oembed_url( $html, $url ) {
		return preg_replace( '#https?://[^"\']*#', $url, $html );
	}

	/**
	 * Remove featured images of any size if Postmatic is supplying one.
	 *
	 * @param string $content
	 * @return string
	 */
	public function strip_duplicate_featured_images( $content ) {

		$src = $this->get_the_featured_image_src();

		if ( empty( $src[0] ) )
			return $content;

		$url = $src[0];

		$last_hyphen_pos = strrpos( $url, '-');

		$match = $last_hyphen_pos ? substr( $url, 0, $last_hyphen_pos ) : $url;

		return preg_replace(
			'/<img[^>]*src=["\']' . preg_quote( $match, '/' ) . '[^>]*>/',
			'',
			$content
		);
	}

	/**
	 * Remove noscript tags, but retain their content.
	 *
	 * @param string $content
	 * @return string
	 */
	public function include_noscript_content( $content ) {
		$content = str_replace( '<noscript>', '', $content );
		return str_replace( '</noscript>', '', $content );
	}

	/**
	 * Remove all images, shortcodes, iframes, objects, and other fancy stuff.
	 *
	 * Makes unlinked URIs clickable.
	 *
	 * @param string $content
	 * @return string
	 */
	public function strip_fancy_content( $content ) {

		// strip images
		$content = preg_replace( '/<img[^>]*>/', '', $content );

		// strip shortcodes
		$content = strip_shortcodes( $content );

		// strip iframes and objects
		$content = preg_replace( '#<(iframe|object)[^>]*>.*?<\\/\\1>#', '', $content );

		// make unlinked URLs clickable
		$content = make_clickable( $content );

		return $content;
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

	protected function override_wp_gist_shortcode_tag( $m ) {
		$defaults = array( 'file' => '', 'id' => '', 'url' => '' );

		$atts = shortcode_atts( $defaults, shortcode_parse_atts( $m[3] ) );

		if ( empty( $atts['id'] ) and empty( $atts['url'] ) )
			return '';

		if ( empty( $atts['id'] ) ) {
			$url_parts = parse_url( $atts['url'] );
			$atts['id'] = basename( $url_parts['path'] );
		}

		$gist = $this->fetch_gist( $atts['id'] );

		if ( !$gist )
			return '';

		$files = $gist['files'];

		if ( empty( $files ) )
			return '';

		if ( empty( $atts['file'] ) or empty( $files[$atts['file'] ] ) ) {
			$file_keys = array_keys( $files );
			$atts['file'] = $file_keys[0];
		}

		$content = $files[$atts['file']]['content'];

		return html( 'pre class="wp-gist"', esc_html( $content ) );
	}

	protected function fetch_gist( $id ) {

		if ( !empty( $this->gist_cache[$id] ) )
			return $this->gist_cache[$id];

		$api_url = 'https://api.github.com/gists/' . $id;

		$response = wp_remote_get( $api_url );
		$json = wp_remote_retrieve_body( $response );

		$this->gist_cache[$id] = json_decode( $json, $associative_arrays = true );

		return $this->gist_cache[$id];
	}

	protected function incompatible_placeholder( $class = '', $url = null ) {
		$class = 'incompatible' . ( $class ? ' ' . $class : '' );
		$url = $url ? $url : get_permalink();
		return html( 'div',
			array( 'class' => $class ),
			__( 'This content is not compatible with your email client. ', 'Postmatic' ),
			html( 'a',
				array( 'href' => $url ),
			__( 'Click here to view this content in your browser.', 'Postmatic' )
			)
		);
	}

	protected function add_image_size_class( $tag ) {

		if ( preg_match( '/class=[\'"]([^\'"]*)[\'"]/', $tag, $matches ) ) {
			$classes = explode( ' ', $matches[1] );
			$classes[] = 'retina';
			return str_replace( $matches[0], 'class="' . implode( ' ', $classes ) . '"', $tag );
		}

		return str_replace( '<img', '<img class="retina"', $tag );
	}

}