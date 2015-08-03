<?php

class Prompt_Lazy_Load_Post_Rendering_Modifier extends Prompt_Post_Rendering_Modifier {

	public function __construct() {
		$this->add_filter( 'the_content', array( $this, 'include_noscript_content' ), 100, 1 );
		$this->add_filter( 'do_rocket_lazyload', '__return_false', 10, 1 );
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

}
