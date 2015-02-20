<?php

class Prompt_Subscribe_Widget_Shortcode {

	public static function render( $attributes ) {

		$defaults = array(
			'title' => '',
			'collect_name' => true,
		);

		$attributes = shortcode_atts( $defaults, $attributes );

		if ( in_array( $attributes['collect_name'], array( 'false', 'FALSE', 'no', 'NO' ) ) )
			$attributes['collect_name'] = false;

		ob_start();

		the_widget( 'Prompt_Subscribe_Widget', $attributes );

		return ob_get_clean();
	}

}