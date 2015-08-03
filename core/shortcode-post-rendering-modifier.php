<?php

class Prompt_Shortcode_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	/** @var array */
	protected static $shortcode_whitelist = array(
		'gallery', 'caption', 'wpv-post-body', 'types', 'yumprint-recipe', 'ultimate-recipe', 'table', 'clickToTweet'
	);

	/** @var  array */
	protected $gist_cache = array();

	public function __construct() {
		$this->remove_filter( 'the_content', 'do_shortcode', 11, 1 );
		$this->add_filter( 'the_content', array( $this, 'do_whitelisted_shortcodes' ), 11, 1 );
	}

	/**
	 * @since 1.4.0
	 *
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

		remove_filter( 'shortcode_atts_gallery', array( $this, 'override_gallery_attributes' ), 10 );

		return $content;
	}

	/**
	 * @since 1.4.0
	 *
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

}