<?php

class Prompt_Template {

	/**
	 * Search the active theme for a template, falling back to the plugin template.
	 *
	 * Templates are are sought first in a 'prompt' subdirectory, then the theme
	 * root. If none are found, the plugin default is used.
	 *
	 * @param string $template_name
	 * @param string $dir
	 * @return string Selected template.
	 */
	public static function locate( $template_name, $dir = null ) {

		// First choice is a template in the theme root or prompt subdirectory
		$template_names = array(
			'postmatic/' . $template_name,
			'prompt/' . $template_name,
			$template_name,
		);
		$template = locate_template( $template_names );

		// Fallback is the core or provided directory
		if ( !$template ) {
			$dir = $dir ? $dir : path_join( Prompt_Core::$dir_path, 'templates' );
			$template = path_join( $dir, $template_name );
		}

		return $template;
	}

	/**
	 * Render a template with an array of data in scope.
	 *
	 * @param string $template The template filename
	 * @param array $data An array of data to provide to the template
	 * @param boolean $echo Whether to echo output, default true
	 * @return string Rendered output
	 */
	public static function render( $template, $data, $echo = true ) {
		$output = '';
		if ( $template ) {
			extract( $data );
			if ( !$echo )
				ob_start();
			require( $template );
			if ( !$echo )
				$output = ob_get_clean();
		}
		return $output;
	}
}