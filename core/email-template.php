<?php

class Prompt_Email_Template extends Prompt_Template {
	/** @var  Prompt_Template */
	protected $wrapper_template;

	public function __construct( $name, $dir = null, $wrapper = null ) {

		if ( !$wrapper ) {
			$is_api_delivery = ( Prompt_Enum_Email_Transports::API == Prompt_Core::$options->get( 'email_transport' ) );
			$wrapper = $is_api_delivery ? 'html-email-wrapper.php' : 'html-local-email-wrapper.php';
		}

		$this->wrapper = new Prompt_Template( $wrapper, $dir );
		parent::__construct( $name, $dir );
	}

	public function render( $data = array(), $echo = false ) {

		$brand_type = Prompt_Core::$options->get( 'email_header_type' );
		$brand_text = Prompt_Core::$options->get( 'email_header_text' );

		$brand_image_id = 0;
		if ( Prompt_Enum_Email_Header_Types::IMAGE === $brand_type ) {
			$brand_image_id = Prompt_Core::$options->get( 'email_header_image' );
		}

		$brand_image = new Prompt_Attachment_Image( $brand_image_id );

		$site_icon = new Prompt_Attachment_Image( Prompt_Core::$options->get( 'site_icon' ) );

		$wrapper_data = array(
			'subject' => __( 'Email by Postmatic', 'Postmatic' ),
			'message' => parent::render( $data ),
			'brand_type' => $brand_type,
			'brand_text' => $brand_text,
			'brand_image_url' => $brand_image->url(),
			'brand_image_width' => $brand_image->width() / 2,
			'brand_image_height' => $brand_image->height() / 2,
			'footer_type' => Prompt_Core::$options->get( 'email_footer_type' ),
			'footer_text' => Prompt_Core::$options->get( 'email_footer_text' ),
			'site_icon_url' => $site_icon->url(),
		);

		$wrapper_data = wp_parse_args( $data, $wrapper_data );

		return $this->wrapper->render( $wrapper_data, $echo );
	}
}