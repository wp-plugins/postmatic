<?php

class Prompt_Email {
	/** @var  string */
	protected $to_name;
	/** @var  string */
	protected $to_address;
	/** @var  string */
	protected $subject;
	/** @var  string */
	protected $html;
	/** @var  string */
	protected $text;
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
	/** @var  object */
	protected $metadata;
	/** @var  string */
	protected $message_type;

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
			'from_name' => get_option( 'blogname' ),
			'from_address' =>  self::default_from_email(),
			'reply_name' => '',
			'reply_address' => '',
			'file_attachments' => array(),
			'metadata' => null,
			'message_type' => '',
		);

		$values = wp_parse_args( $values, $defaults );

		foreach( $values as $name => $value ) {
			call_user_func( array( $this, 'set_' . $name ), $value );
		}

	}

	/**
	 * @param string $to_name
	 * @return Prompt_Email
	 */
	public function set_to_name( $to_name ) {
		$this->to_name = $this->to_utf8( $to_name );
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_to_name() {
		return $this->to_name;
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
		$this->from_name = $this->to_utf8( $from_name );
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_from_name() {
		return $this->from_name;
	}

	/**
	 * @param string $html
	 * @return Prompt_Email
	 */
	public function set_html( $html ) {
		$this->html = $html;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_html() {

		if ( ! isset( $this->html ) and isset( $this->text ) )
			$this->html = $this->text;

		return $this->html;
	}

	/**
	 * @param string $text
	 * @return Prompt_Email
	 */
	public function set_text( $text ) {
		$this->text = $text;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_text() {

		if ( ! isset( $this->text ) and isset( $this->html ) ) {
			$html = preg_replace( '@<(head|script|style)[^>]*?>.*?</\\1>@si', '', $this->html );
			//$html = preg_replace( '@<\!--.*?-->@si', '', $html );
			$text = Prompt_Html_To_Markdown::convert( $html );
			$this->text = strip_tags( $text );
		}

		return $this->text;
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
		$this->reply_name = $this->to_utf8( $reply_name );
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
		$this->subject = $this->to_utf8( $subject );
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
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
	 * @param string $message_type
	 * @return Prompt_Email
	 */
	public function set_message_type( $message_type ) {
		$this->message_type = $message_type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function get_message_type() {
		return $this->message_type;
	}

	/**
	 * @return bool
	 */
	public function have_html() {
		return isset( $this->html );
	}

	// TODO: add string attachments
	private function add_string_attachment( $string, $filename, $encoding = 'base64' , $type = 'application/octet-stream' ) {
		$this->string_attachments[] = compact( 'string', 'filename', 'encoding', 'type' );
	}

	protected function to_utf8( $content ) {
		return wp_strip_all_tags( html_entity_decode( $content, ENT_QUOTES, 'UTF-8' ) );
	}
}