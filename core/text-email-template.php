<?php

class Prompt_Text_Email_Template extends Prompt_Template {

	public function __construct( $name, $dir = null, $wrapper  = 'text-email-wrapper.php' ) {
		$this->wrapper = new Prompt_Template( $wrapper, $dir );
		parent::__construct( $name, $dir );
	}

	public function render( $data = array(), $echo = false ) {

		$wrapper_data = array(
			'message' => parent::render( $data, false ),
			'brand_text' => Prompt_Core::$options->get( 'email_header_text' ),
			'footer_text' => Prompt_Core::$options->get( 'email_footer_text' ),
		);

		$wrapper_data = wp_parse_args( $data, $wrapper_data );

		return $this->wrapper->render( $wrapper_data, $echo );
	}
}