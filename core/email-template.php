<?php

class Prompt_Email_Template extends Prompt_Template {
	/** @var  Prompt_Template */
	protected $wrapper_template;

	public function __construct( $name, $dir = null, $wrapper = 'html-email-wrapper.php' ) {
		$this->wrapper = new Prompt_Template( $wrapper, $dir );
		parent::__construct( $name, $dir );
	}

	public function render( $data = array(), $echo = false ) {

		$brand_type = Prompt_Core::$options->get( 'email_header_type' );
		$brand_text = Prompt_Core::$options->get( 'email_header_text' );

		if ( Prompt_Enum_Email_Header_Types::IMAGE === $brand_type ) {
			$brand_image_src = wp_get_attachment_image_src( Prompt_Core::$options->get( 'email_header_image' ), 'full' );
		} else {
			$brand_image_src = array( '', 0, 0 );
		}

		$site_icon_src = wp_get_attachment_image_src( Prompt_Core::$options->get( 'site_icon' ), 'full' );
		$site_icon_url = $site_icon_src[0];

		$wrapper_data = array(
			'subject' => __( 'Email by Postmatic', 'Postmatic' ),
			'message' => parent::render( $data ),
			'brand_type' => $brand_type,
			'brand_text' => $brand_text,
			'brand_image_url' => $brand_image_src[0],
			'brand_image_width' => $brand_image_src[1] / 2,
			'brand_image_height' => $brand_image_src[2] / 2,
			'footer_type' => Prompt_Core::$options->get( 'email_footer_type' ),
			'footer_text' => Prompt_Core::$options->get( 'email_footer_text' ),
			'site_icon_url' => $site_icon_url,
		);

		$wrapper_data = wp_parse_args( $data, $wrapper_data );

		return $this->wrapper->render( $wrapper_data, $echo );
	}
}