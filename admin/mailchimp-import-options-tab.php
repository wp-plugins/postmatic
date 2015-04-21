<?php

class Prompt_Admin_MailChimp_Import_Options_Tab extends Prompt_Admin_Import_Options_Tab {

	protected $import_list_name = 'import_list';
	protected $mailchimp_api_key = '';
	protected $rejected_addresses_name = 'rejected_addresses';
	protected $import_type = 'mailchimp_import';

	public function name() {
		return __( 'Import from MailChimp', 'Postmatic' );
	}

	public function slug() {
		return 'import-mailchimp';
	}

	public function form_handler() {
		if ( isset( $_POST[$this->import_type_name] ) )
			$this->current_import_type = $_POST[$this->import_type_name];

		if ( isset( $_POST[ 'mailchimp_api_key' ] ) )
			$this->mailchimp_api_key = $_POST[ 'mailchimp_api_key' ];

		if ( $this->current_import_type )
			$this->add_notice( __('Import results are below.', 'Postmatic' ) );
	}

	public function render() {
		$content = html( 'h2', __( 'MailChimp Import', 'Postmatic' ) );

		if ( $this->current_import_type == $this->import_type )
			return $content . $this->import_content();

		return $content . $this->setup_import();
	}

	protected function import_content() {

		$api_key = sanitize_text_field( $_POST['mailchimp_api_key'] );
		$list_id = sanitize_text_field( $_POST[$this->import_list_name] );

		$import = new Prompt_Admin_MailChimp_Import( $api_key, $list_id );

		$import->execute();

		$content = html( 'h3', __( 'Here\'s how it went', 'Postmatic' ) );

		$content .= $import->get_error() ? $import->get_error()->get_error_message() : '';

		$results_format = _n(
			'Imported one subscriber.',
			'Imported %1$s subscribers.',
			$import->get_imported_count(),
			'Postmatic'
		);

		if ( $import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'The one valid user we found was already subscribed.',
				'The %2$s valid users we found were already subscribed.',
				$import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$rejects = $import->get_rejected_subscribers();
		$reject_content = '';
		$reject_button = '';
		if ( $rejects ) {

			$results_format .= '<br />' . _n(
				'One user didn\'t qualify for importing.',
				'There were %3$s users which didn\'t qualify for importing.',
				count( $rejects )
			);

			$reject_content = html( 'div id="mailpoet-import-intro"',
				html( 'div',
					html( 'h4', __( 'Why weren\'t more of my users imported?', 'Postmatic' ) ),
					html( 'p',
						__(
							'We have a very strict policy regarding user imports: <em>we will never allow anyone to be subscribed to a blog running Postmatic without them having opted in</em> (such as subscriber lists bought and imported in bulk for spamming). Because of this we will not import any MailChimp subscribers unless the following two conditions are true:',
							'Postmatic'
						)
					),
					html( 'ol',
						html( 'li', __( 'The user has double opted-in to your MailChimp list', 'Postmatic' ) ),
						html( 'li', __( 'The user exists on a list which is at least 60 days old', 'Postmatic' ) )
					),
					html( 'h5', __( 'Why so strict?', 'Postmatic' ) ),
					html( 'p',
						__(
							'Bulk importing unwilling users is easy in MailChimp. If we did not hold our import to a higher standard those unwilling users could be imported into Postmatic. And then they would spam your users. MailChimp is a one-way street. Postmatic is a conversation. That\'s a very important difference.',
							'Postmatic'
						)
					),
					html( 'h4', __( 'But we do have good news', 'Postmatic' ) ),
					html( 'p',
						__(
							'You can send an email to your remaining users. They will be invited to join your site by simply replying.',
							'Postmatic'
						)
					)
				)
			);

			$rejected_addresses = array();
			foreach ( $rejects as $reject ) {
				$name = trim( $reject['email'] );
				//$name = trim( $reject['firstname'] . ' ' . $reject['lastname'] );
				$rejected_addresses[] = Prompt_Email::name_address( $reject['email'], $name );
			}

			$reject_button = html( 'input',
				array(
					'name' => $this->rejected_addresses_name,
					'class' => 'button',
					'data-addresses' => implode( ",", $rejected_addresses ),
					'type' => 'submit',
					'value' => __( 'Preview and send the invitations', 'Postmatic' ),
				)
			);
		}

		$content = html( 'p',
			$content,
			sprintf(
				$results_format,
				$import->get_imported_count(),
				$import->get_already_subscribed_count(),
				count( $rejects )
			),
			$reject_content,
			$reject_button
		);

		return $content;
	}


	protected function setup_import() {

		$content = html( 'div class="mailchimp-intro"', $this->render_intro() );

		$content .= html( 'label for="mailchimp_api_key"',
			__( 'MailChimp API Key: ', 'Postmatic' ),
			html( 'input',
				array( 'name' => 'mailchimp_api_key', 'type' => 'text', 'id' => 'mailchimp_api_key', 'style' => 'width: 300px;' )
			)
			.html( 'button', array( 'id' => 'mail_chimp_load_lists', 'class' => 'button button-small', 'type' => 'button' ), __( 'Load lists', 'Postmatic' ) )
			.html( 'span', array( 'id' => 'mail_chimp_spinner', 'class' => 'spinner', 'style' => 'float: none;margin: -5px 5px;' ) )
		);

		$content .= html( 'div',
			array( 'id' => 'mailchimp_lists' )
		).html( 'input',
			array( 'name' => $this->import_type_name, 'type' => 'hidden', 'value' => $this->import_type )			
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Evaluate and import list', 'Postmatic' ), 'class' => 'hidden' ) );
	}

	protected function render_intro() {

		$template = new Prompt_Template( 'mailchimp-import-intro.php' );

		return $template->render();
	}

}
