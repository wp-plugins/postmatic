<?php

class Prompt_Admin_Email_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'Email Template', 'Postmatic' );
	}

	public function form_handler() {

		if ( !empty( $_POST['site_icon_button'] ) ) {
			Prompt_Core::set_site_icon();
			return;
		}

		if ( !empty( $_POST['send_test_email_button'] ) ) {

			$to_address = sanitize_email( $_POST['test_email_address'] );

			if ( !is_email( $to_address ) ) {
				$this->add_notice(
					__( 'Test email was <strong>not sent</strong> to an invalid address.', 'Postmatic' ),
					'error'
				);
				return;
			}

			$template = Prompt_Template::locate( 'test-email.php' );
			$email = new Prompt_Email( array(
				'to_address' => $to_address,
				'message' => Prompt_Template::render( $template, array(), $echo = false ),
			) );

			if ( Prompt_Factory::make_mailer()->send_one( $email ) ) {
				$this->add_notice( __( 'Test email <strong>sent</strong>.', 'Postmatic' ) );
				return;
			}

		}

		parent::form_handler();
	}

	public function render() {

		$email_header_image_src = array( '', 0, 0 );
		if ( $this->options->get( 'email_header_image' ) ) {
			$email_header_image_src = wp_get_attachment_image_src( $this->options->get( 'email_header_image' ), 'full' );
		}

		$site_icon_src = array( '', 0, 0 );
		if ( $this->options->get( 'site_icon' ) ) {
			$site_icon_src = wp_get_attachment_image_src( $this->options->get( 'site_icon' ), 'full' );
		}

		$rows = array(
			$this->row_wrap(
				__( 'Email header type', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'radio',
						'name' => 'email_header_type',
						'choices' => array(
							Prompt_Enum_Email_Header_Types::IMAGE => __( 'Image', 'Postmatic' ),
							Prompt_Enum_Email_Header_Types::TEXT => __( 'Text', 'Postmatic' ),
						),
					),
					$this->options->get()
				)
			),
			html(
				'tr class="email-header-image"',
				html( 'th scope="row"',
					__( 'Email header image', 'Postmatic' ),
					'<br/>',
					html( 'small',
						__(
							'Will be displayed at half the size of your uploaded image to support retina displays. The ideal width to fill the full header area is 1440px wide.',
							'Postmatic'
						)
					)
				),
				html(
					'td',
					html(
						'img',
						array(
							'src' => $email_header_image_src[0],
							'width' => $email_header_image_src[1] / 2,
							'height' => $email_header_image_src[2] / 2,
							'class' => 'alignleft',
						)
					),
					html(
						'div class="uploader"',
						$this->input(
							array( 'name' => 'email_header_image', 'type' => 'hidden' ),
							$this->options->get()
						),
						html(
							'input class="button" type="button" name="email_header_image_button"',
							array( 'value' => __( 'Change', 'Postmatic' ) )
						)
					)
				)
			),
			html(
				'tr class="email-header-text"',
				html( 'th scope="row"', __( 'Email header text', 'Postmatic' ) ),
				html(
					'td',
					$this->input(
						array( 'name' => 'email_header_text', 'type' => 'text' ),
						$this->options->get()
					)
				)
			),
			html(
				'tr class="site-icon"',
				html( 'th scope="row"',
					__( 'Site icon', 'Postmatic' ),
					'<br/>',
					html( 'small',
						__(
							'This is generated from your site\'s favicon, and used in comment notifications in place of the header image.',
							'Postmatic'
						)
					)
				),
				html(
					'td',
					html(
						'img',
						array(
							'src' => $site_icon_src[0],
							'width' => $site_icon_src[1] / 2,
							'height' => $site_icon_src[2] / 2,
							'class' => 'alignleft',
						)
					),
					html(
						'div',
						$this->input(
							array( 'name' => 'site_icon', 'type' => 'hidden' ),
							$this->options->get()
						),
						html(
							'input class="button" type="submit" name="site_icon_button"',
							array( 'value' => __( 'Refresh', 'Postmatic' ) )
						)
					)
				)
			),
			$this->row_wrap(
				__( 'Email footer type', 'Postmatic' ),
				$this->input(
					array(
						'type' => 'radio',
						'name' => 'email_footer_type',
						'choices' => array(
							Prompt_Enum_Email_Footer_Types::WIDGETS => __( 'Widgets', 'Postmatic' ),
							Prompt_Enum_Email_Header_Types::TEXT => __( 'Text', 'Postmatic' )
						),
					),
					$this->options->get()
				)
			),
			html(
				'tr class="email-footer-widgets"',
				html( 'th scope="row"', __( 'Footer Widgets', 'Postmatic' ) ),
				html(
					'td',
					__( 'You can define widgets for your footer at ', 'Postmatic' ),
					html(
						'a',
						array( 'href' => admin_url( 'widgets.php' ) ),
						__( 'Appearance > Widgets', 'Postmatic' )
					)
				)
			),
			html(
				'tr class="email-footer-text"',
				html( 'th scope="row"', __( 'Email footer text', 'Postmatic' ) ),
				html(
					'td',
					$this->input(
						array( 'name' => 'email_footer_text', 'type' => 'text' ),
						$this->options->get()
					)
				)
			),
			html(
				'tr',
				html( 'th scope="row"',  __( 'Send a test HTML email to', 'Postmatic' ) ),
				html(
					'td',
					$this->input(
						array(
							'type' => 'text',
							'name' => 'test_email_address',
							'value' => wp_get_current_user()->user_email,
						),
						$_POST
					),
					html(
						'input class="button" type="submit" name="send_test_email_button"',
						array( 'value' => __( 'Send', 'Postmatic' ) )
					)
				)
			)
		);

		return $this->form_table_wrap( implode( '', $rows ), $this->options->get() );
	}

	function validate( $new_data, $old_data ) {
		$valid_data = $old_data;

		$header_type_reflect = new ReflectionClass( 'Prompt_Enum_Email_Header_Types' );
		$header_types = array_values( $header_type_reflect->getConstants() );

		if ( isset( $new_data['email_header_type'] ) and in_array( $new_data['email_header_type'], $header_types ) )  {
			$valid_data['email_header_type'] = $new_data['email_header_type'];
		}

		if ( isset( $new_data['email_header_text'] ) ) {
			$valid_data['email_header_text'] = sanitize_text_field( $new_data['email_header_text'] );
		}

		if ( isset( $new_data['email_header_image'] ) ) {
			$valid_data['email_header_image'] = absint( $new_data['email_header_image'] );
		}

		if ( isset( $new_data['site_icon'] ) ) {
			$valid_data['site_icon'] = absint( $new_data['site_icon'] );
		}

		$footer_type_reflect = new ReflectionClass( 'Prompt_Enum_Email_Footer_Types' );
		$footer_types = array_values( $footer_type_reflect->getConstants() );

		if ( isset( $new_data['email_footer_type'] ) and in_array( $new_data['email_footer_type'], $footer_types ) )  {
			$valid_data['email_footer_type'] = $new_data['email_footer_type'];
		}

		if ( isset( $new_data['email_footer_text'] ) ) {
			$valid_data['email_footer_text'] = sanitize_text_field( $new_data['email_footer_text'] );
		}

		return $valid_data;
	}

}
