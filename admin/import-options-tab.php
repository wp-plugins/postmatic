<?php

class Prompt_Admin_Import_Options_Tab extends Prompt_Admin_Options_Tab {

	protected $jetpack_import_type = 'jetpack_import';

	public function name() {
		return __( 'Import Users', 'Postmatic' );
	}

	public function slug() {
		return 'import';
	}

	public function form_handler() {
		if ( !empty( $_POST['import_type'] ) ) {
			$this->add_notice( __('Import results are below.', 'Postmatic' ) );
		}
	}

	public function render() {
		$content = html( 'h2', __( 'Jetpack Import', 'Postmatic' ) );

		if ( ! Prompt_Admin_Jetpack_Import::is_jetpack_ready() )
			return $content . $this->jetpack_unavailable_content();


		if ( isset( $_POST['import_type'] ) and $this->jetpack_import_type == $_POST['import_type'] )
			return $content . $this->jetpack_import_content();

		return $content . $this->jetpack_ready_content();
	}

	protected function jetpack_unavailable_content() {
		$content = html( 'div id="jetpack-unavailable"',
			html( 'p',
				__(
					'Before you can import Jetpack subscribers, the Jetpack plugin and its Stats module must be active.',
					'Postmatic'
				)
			)
		);
		return $content;
	}

	protected function jetpack_ready_content() {
		$content = html( 'div id="jetpack-import-intro"',
			__( 'We are ready to import email-only subscribers from Jetpack.', 'Postmatic' )
		);

		if ( Prompt_Core::$options->get( 'send_login_info' ) ) {
			$content .= html( 'p class="send-login-warning"',
				html( 'strong', __( 'Important:', 'Postmatic' ) ),
				' ',
				__(
					'You have User Account notifications enabled in the Options tab, which means that each new ' .
					'subscriber imported will receive an email with their credentials. It is not necessary to send ' .
					'these credentials to Postmatic subscribers as all subscriber functions can be done directly via ' .
					'email. If you would like to disable these notifications please do so in the Options tab above.',
					'Postmatic'
				)
			);
		}

		$content .= html( 'input',
			array( 'name' => 'import_type', 'type' => 'hidden', 'value' => $this->jetpack_import_type )
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Import from Jetpack' ) ) );
	}

	protected function jetpack_import_content() {
		$jetpack_import = Prompt_Factory::make_jetpack_import();

		$jetpack_import->execute();

		$results_format = _n(
			'Imported one subscriber.',
			'Imported %1$s subscribers.',
			$jetpack_import->get_imported_count(),
			'Postmatic'
		);

		if ( $jetpack_import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'One user was already subscribed.',
				'%2$s users were already subscribed.',
				$jetpack_import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$content = sprintf(
			$results_format,
			$jetpack_import->get_imported_count(),
			$jetpack_import->get_already_subscribed_count()
		);

		return $content;
	}
}
