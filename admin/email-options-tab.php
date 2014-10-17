<?php

class Prompt_Admin_Email_Options_Tab extends Prompt_Admin_Options_Tab {

	public function name() {
		return __( 'Email Template', 'Prompt_Core' );
	}

	public function form_handler() {

		if ( !empty( $_POST['send_test_email_button'] ) ) {

			$to_address = sanitize_email( $_POST['test_email_address'] );

			if ( !is_email( $to_address ) ) {
				$this->add_notice(
					__( 'Test email was <strong>not sent</strong> to an invalid address.', 'Prompt_Core' ),
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
				$this->add_notice( __( 'Test email <strong>sent</strong>.', 'Prompt_Core' ) );
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
		$rows = array(
			$this->row_wrap(
				__( 'Email header type', 'Prompt_Core' ),
				$this->input(
					array(
						'type' => 'radio',
						'name' => 'email_header_type',
						'choices' => array(
							'image' => __( 'Image', 'Prompt_Core' ),
							'text' => __( 'Text', 'Prompt_Core' )
						),
					),
					$this->options->get()
				)
			),
			html(
				'tr class="email-header-image"',
				html( 'th scope="row"', __( 'Email header image <br/><small>Will be displayed at half the size of your uploaded image to support retina displays. The ideal width to fill the full header area is 1440px wide.</small>', 'Prompt_Core' ) ),
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
							array( 'value' => __( 'Change', 'Prompt_Core' ) )
						)
					)
				)
			),
			html(
				'tr class="email-header-text"',
				html( 'th scope="row"', __( 'Email header text', 'Prompt_Core' ) ),
				html(
					'td',
					$this->input(
						array( 'name' => 'email_header_text', 'type' => 'text' ),
						$this->options->get()
					)
				)
			),
			html(
				'tr',
				html( 'th scope="row"', __( 'Footer Widgets', 'Prompt_Core' ) ),
				html(
					'td',
					__( 'You can define widgets for your footer at ', 'Prompt_Core' ),
					html(
						'a',
						array( 'href' => admin_url( 'widgets.php' ) ),
						__( 'Appearance > Widgets', 'Prompt_Core' )
					)
				)
			),
			html(
				'tr',
				html( 'th scope="row"',  __( 'Send a test HTML email to', 'Prompt_Core' ) ),
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
						array( 'value' => __( 'Send', 'Prompt_Core' ) )
					)
				)
			)
		);

		return $this->form_table_wrap( implode( '', $rows ), $this->options->get() );
	}

	function validate( $new_data, $old_data ) {
		$valid_data = $old_data;

		if ( isset( $new_data['email_header_type'] ) and in_array( $new_data['email_header_type'], array( 'text', 'image' ) ) )  {
			$valid_data['email_header_type'] = $new_data['email_header_type'];
		}

		if ( isset( $new_data['email_header_text'] ) ) {
			$valid_data['email_header_text'] = sanitize_text_field( $new_data['email_header_text'] );
		}

		if ( isset( $new_data['email_header_image'] ) ) {
			$valid_data['email_header_image'] = absint( $new_data['email_header_image'] );
		}

		return $valid_data;
	}

}
