<?php

class Prompt_Incompatible_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {
		$this->remove_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8, 1 );
		$this->add_filter( 'embed_oembed_html', array( $this, 'use_original_oembed_url' ), 10, 2 );
		$this->add_filter( 'the_content', array( $this, 'strip_incompatible_tags' ), 11, 1 );
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
		if ( ! $this->has_incompatible_tag( $html ) )
			return $html;

		return preg_replace( '#https?://[^"\']*#', $url, $html );
	}

	public function strip_incompatible_tags( $content ) {

		if ( ! $this->has_incompatible_tag( $content ) )
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

	protected function has_incompatible_tag( $content ) {
		return ( false !== strpos( $content, '<iframe' ) or false !== strpos( $content, '<object' ) );
	}

}
