<?php

class Prompt_Skimlinks_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {

		if ( ! Prompt_Core::$options->get( 'skimlinks_publisher_id' ) )
			return;

		$this->add_filter( 'the_content', array( $this, 'modify_links' ), 1, 1 );
	}

	public function modify_links( $content ) {

		$content = preg_replace_callback(
			"/ href ?= ?(\"|'|&quot;)(((?!\\1).)+)(\\1)/siU",
			array( $this, 'replace_link'),
			$content
		);

		return $content;
	}

	public function replace_link( $matches ) {

		$full_match = $matches[0];
		$quote = $matches[1];
		$url = $matches[2];
		$url_parts = parse_url( $url );

		if ( 'http' !== $url_parts['scheme'] and 'https' !== $url_parts['scheme'] )
			return $full_match;

		// Leave internal links
		if ( strpos( $url, home_url() ) !== false )
			return $full_match;

		// Leave already redirected links
		if ( strpos( $url, 'redirectingat.com' ) !== false )
			return $full_match;

		$url = htmlentities(
			sprintf(
				'http://go.redirectingat.com/?id=%s&xs=1&url=%s&sref=postmatic',
				Prompt_Core::$options->get( 'skimlinks_publisher_id' ),
				urlencode( $url )
			)
		);

		if ( '&quot;' === $quote ) {
			$url = htmlentities( $url );
		}

		return ' href=' . $quote . $url . $quote;
	}

}
