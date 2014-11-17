<?php

class Prompt_Email {
	/** @var  string */
	protected $to_name;
	/** @var  string */
	protected $to_address;
	/** @var  string */
	protected $subject;
	/** @var  string */
	protected $message;
	/** @var  string */
	protected $from_name;
	/** @var  string */
	protected $from_address;
	/** @var  string */
	protected $reply_name;
	/** @var  string */
	protected $reply_address;
	/** @var  array */
	protected $file_attachments;
	/** @var  string */
	protected $content_type;
	/** @var  string */
	protected $template;
	/** @var  object */
	protected $metadata;

	// TODO: string attachments
	protected $string_attachments = array();

	/**
	 * Return a full name-address made from the two parts.
	 *
	 * A name "Foo Bar" and address "foo@bar.com" result in "Foo Bar <foo@bar.com>".
	 *
	 * If no address is included, the default site address is returned.
	 *
	 * If no name is included, the plain email address is returned.
	 *
	 * @param string $address
	 * @param string $name
	 * @return string name address
	 */
	static public function name_address( $address = null, $name = '' ) {
		if ( !$address )
			return self::default_from_email();

		if ( empty( $name ) )
			return $address;

		return $name . ' <' . $address . '>';
	}

	/**
	 * Get the address part of a name-address string.
	 *
	 * "Foo Bar <foo@bar.com>" will return "foo@bar.com".
	 *
	 * If there is no angle-bracketed address, the passed in address is returned unchanged.
	 *
	 * @param string $name_address
	 * @return string
	 */
	static public function address( $name_address ) {
		$address = $name_address;

		if ( preg_match( '/([^<]+) <([^>]+)>/', $name_address, $matches ) )
			$address = $matches[2];

		return $address;
	}

	/**
	 * Get the name part of a name-address string.
	 *
	 * "Foo Bar <foo@bar.com>" will return "Foo Bar".
	 *
	 * If there is no angle-bracketed address, an empty string is returned.
	 *
	 * @param string $name_address
	 * @return string
	 */
	static public function name( $name_address ) {
		$name = '';

		if ( preg_match( '/([^<]+) <([^>]+)>/', $name_address, $matches ) )
			$name = $matches[1];

		return $name;
	}

	/**
	 * @return string the default from address used for Prompt emails.
	 */
	static public function default_from_email() {
		/**
		 * Filter default from email.
		 *
		 * @param string $email
		 */
		return apply_filters( 'prompt/default_from_email', 'hello@email.gopostmatic.com' );
	}

	public function __construct( $values = '' ) {
		$defaults = array(
			'to_address' => '',
			'to_name' => '',
			'subject' => __( 'This is a test email. By Postmatic.', 'Postmatic' ),
			'message' => '',
			'from_name' => get_option( 'blogname' ),
			'from_address' =>  self::default_from_email(),
			'reply_name' => '',
			'reply_address' => '',
			'file_attachments' => array(),
			'metadata' => null,
			'content_type' => Prompt_Enum_Content_Types::HTML,
			'template' => 'html-email-wrapper.php',
		);

		$values = wp_parse_args( $values, $defaults );

		foreach( $values as $name => $value ) {
			call_user_func( array( $this, 'set_' . $name ), $value );
		}

		if ( Prompt_Enum_Content_Types::HTML != $this->content_type and $defaults['template'] == $this->template )
			$this->template = null;

	}

	/**
	 * @param string $to_name
	 * @return Prompt_Email
	 */
	public function set_to_name( $to_name ) {
		$this->to_name = $to_name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_to_name() {
		return $this->to_name;
	}

	/**
	 * @param string $content_type
	 * @return Prompt_Email
	 */
	public function set_content_type( $content_type ) {
		$this->content_type = $content_type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_content_type() {
		return $this->content_type;
	}

	/**
	 * @param array $file_attachments
	 * @return Prompt_Email
	 */
	public function set_file_attachments( $file_attachments ) {
		$this->file_attachments = $file_attachments;
		return $this;
	}

	/**
	 * @return array
	 */
	public function get_file_attachments() {
		return $this->file_attachments;
	}

	/**
	 * @param string $from_address
	 * @return Prompt_Email
	 */
	public function set_from_address( $from_address ) {
		$this->from_address = $from_address;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_from_address() {
		return $this->from_address;
	}

	/**
	 * @param string $from_name
	 * @return Prompt_Email
	 */
	public function set_from_name( $from_name ) {
		$this->from_name = $from_name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_from_name() {
		return $this->from_name;
	}

	/**
	 * @param string $message
	 * @return Prompt_Email
	 */
	public function set_message( $message ) {
		$this->message = $message;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * @param object $metadata
	 * @return Prompt_Email
	 */
	public function set_metadata( $metadata ) {
		$this->metadata = $metadata;
		return $this;
	}

	/**
	 * @return object
	 */
	public function get_metadata() {
		return $this->metadata;
	}

	/**
	 * @param string $reply_address
	 * @return Prompt_Email
	 */
	public function set_reply_address( $reply_address ) {
		$this->reply_address = $reply_address;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_reply_address() {
		return $this->reply_address;
	}

	/**
	 * @param string $reply_name
	 * @return Prompt_Email
	 */
	public function set_reply_name( $reply_name ) {
		$this->reply_name = $reply_name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_reply_name() {
		return $this->reply_name;
	}

	/**
	 * @param string $subject
	 * @return Prompt_Email
	 */
	public function set_subject( $subject ) {
		$this->subject = wp_strip_all_tags( html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' ) );
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * @param string $template
	 * @return Prompt_Email
	 */
	public function set_template( $template ) {
		$this->template = $template;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * @param string $to_address
	 * @return Prompt_Email
	 */
	public function set_to_address( $to_address ) {
		$this->to_address = $to_address;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_to_address() {
		return $this->to_address;
	}

	/**
	 * @return string
	 */
	public function get_rendered_message() {

		$message = $this->message;

		if ( !empty( $this->template ) ) {
			// Wrap the message in the given template
			$brand_type = Prompt_Core::$options->get( 'email_header_type' );
			if ( Prompt_Enum_Email_Header_Types::IMAGE === $brand_type ) {
				$brand_image_src = wp_get_attachment_image_src( Prompt_Core::$options->get( 'email_header_image' ), 'full' );
				$brand_text = '';
			} else {
				$brand_image_src = array( '', 0, 0 );
				$brand_text = Prompt_Core::$options->get( 'email_header_text' );
			}

			$template = Prompt_Template::locate( $this->template );
			$template_data = array(
				'subject' => $this->subject,
				'message' => $this->message,
				'brand_type' => $brand_type,
				'brand_text' => $brand_text,
				'brand_image_url' => $brand_image_src[0],
				'brand_image_width' => $brand_image_src[1] / 2,
				'brand_image_height' => $brand_image_src[2] / 2,
				'footer_type' => Prompt_Core::$options->get( 'email_footer_type' ),
				'footer_text' => Prompt_Core::$options->get( 'email_footer_text' ),
			);
			$message = Prompt_Template::render( $template, $template_data, false );
		}

		return $message;
	}

	// TODO: add string attachments
	private function add_string_attachment( $string, $filename, $encoding = 'base64' , $type = 'application/octet-stream' ) {
		$this->string_attachments[] = compact( 'string', 'filename', 'encoding', 'type' );
	}

	/**
	 * Replace the message with a fully rendered version.
	 *
	 * Assumes any template supplied is no longer needed, and ditches it.
	 *
	 * @param $message
	 * @return Prompt_Email
	 */
	public function set_rendered_message( $message ) {
		$this->template = null;
		$this->message = $message;
		return $this;
	}

}