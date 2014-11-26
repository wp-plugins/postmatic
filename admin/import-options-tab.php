<?php

class Prompt_Admin_Import_Options_Tab extends Prompt_Admin_Options_Tab {

	protected $jetpack_import_type = 'jetpack_import';
	protected $mailpoet_import_type = 'mailpoet_import';

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
		return $this->render_jetpack();
	}

	protected  function render_jetpack() {
		$content = html( 'h2', __( 'Jetpack Import', 'Postmatic' ) );

		if ( ! Prompt_Admin_Jetpack_Import::is_usable() )
			return $content . $this->jetpack_not_usable_content( Prompt_Admin_Jetpack_Import::not_usable_message() );

		if ( isset( $_POST['import_type'] ) and $this->jetpack_import_type == $_POST['import_type'] )
			return $content . $this->jetpack_import_content();

		return $content . $this->jetpack_ready_content();
	}

	protected function jetpack_not_usable_content( $message ) {
		return html( 'div id="jetpack-not-ready"', html( 'p', $message ) );
	}

	protected function jetpack_ready_content() {
		$content = html( 'div id="jetpack-import-instructions"',
			__( '<p>Migrating your users from Jetpack to Postmatic takes only seconds. We’ve built a safe and secure importer which will copy over all of your new post notification Jetpack subscribers with a single click. Once the import has completed you can safely disable Jetpack Subscriptions and continue on doing what you do best. Your Jetpack subscribers will be left in tact should you ever need to access them again.<p>
				<div>
				<h3>Jetpack Import FAQ</h3>
				<h4>Will my subscribers be alerted to the change?</h4>
				<p>No. The import process is invisible to subscribers. They will not be alerted nor will they need to opt-in again.</p>
				<h4>What kind of subscribers are imported?</h4>
				<p>People who have subscribed to new posts on your site will be imported. At this time it\'s not going to be in the cards to import people that have subscribed only to comments on individual posts.</p>
				<h4>Who isn\'t imported?</h4>
				<p>Jetpack supports two kinds of users: people that subscribe to your site with their email address, and people that subscribe to your site with their wordpress.com user identity. At this time we can\'t access the email address of a user which subscribed with their wordpress.com identity. This will in most cases be a very small percentage of your audience.</p>
				<h4>What happens if something goes wrong? Will my Jetpack subscribers be safe?</h4>
				<p>If something goes wrong with the import you will be notified of the error. Your Jetpack subscribers list lives on wordpress.com and will always be there in case you need it again.</p>
				</div>
				', 'Postmatic' )
		);

		$content .= html( 'div id="jetpack-import-intro"',
			__( 'Everything on your server looks good. We are ready to import your Jetpack subscribers.', 'Postmatic' )
		);

		$content .= self::send_login_warning_content();

		$content .= html( 'input',
			array( 'name' => 'import_type', 'type' => 'hidden', 'value' => $this->jetpack_import_type )
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Import from Jetpack' ) ) );
	}

	protected function jetpack_import_content() {
		$jetpack_import = Prompt_Factory::make_jetpack_import();

		$jetpack_import->execute();

		$content = $jetpack_import->get_error() ? $jetpack_import->get_error()->get_error_message() : '';

		$results_format = _n(
			'We have imported one subscriber. It is now safe to disable Jetpack commenting and subscriptions.',
			'Imported %1$s subscribers. It is now safe to disable Jetpack commenting and subscriptions.',
			$jetpack_import->get_imported_count(),
			'Postmatic'
		);

		if ( $jetpack_import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'The one user we found was already subscribed.',
				'The %2$s users we found were already subscribed.',
				$jetpack_import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$content .= ' ' . sprintf(
			$results_format,
			$jetpack_import->get_imported_count(),
			$jetpack_import->get_already_subscribed_count()
		);

		return $content;
	}

	protected function send_login_warning_content() {
		if ( !Prompt_Core::$options->get( 'send_login_info' ) )
			return '';

		return html( 'p class="send-login-warning"',
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

	protected function render_mailpoet() {
		$content = html( 'h2', __( 'Mailpoet Import', 'Postmatic' ) );

		if ( ! Prompt_Admin_Mailpoet_Import::is_ready() )
			return $content . $this->mailpoet_unavailable_content();


		if ( isset( $_POST['import_type'] ) and $this->mailpoet_import_type == $_POST['import_type'] )
			return $content . $this->mailpoet_import_content();

		return $content . $this->mailpoet_ready_content();
	}

	protected function mailpoet_unavailable_content() {
		$content = html( 'div id="mailpoet-unavailable"',
			html( 'p',
				__(
					'If you would like to import Mailpoet users please activate the Mailpoet plugin.',
					'Postmatic'
				)
			)
		);
		return $content;
	}

	protected function mailpoet_import_content() {

		$list_id = intval( $_POST['import_list'] );

		$import = Prompt_Admin_Mailpoet_Import::make( $list_id );

		$import->execute();

		$content = $import->get_error() ? $import->get_error()->get_error_message() : '';

		$results_format = _n(
			'Imported one subscriber.',
			'Imported %1$s subscribers.',
			$import->get_imported_count(),
			'Postmatic'
		);

		if ( $import->get_already_subscribed_count() > 0 ) {
			$results_format .= ' ' . _n(
				'The one user we found was already subscribed.',
				'The %2$s users we found were already subscribed.',
				$import->get_already_subscribed_count(),
				'Postmatic'
			);
		}

		$content .= ' ' . sprintf(
			$results_format,
			$import->get_imported_count(),
			$import->get_already_subscribed_count()
		);

		return $content;
	}

	protected function mailpoet_ready_content() {

		$lists = Prompt_Admin_Mailpoet_Import::get_lists();

		if ( count( $lists ) === 0 )
			return html( 'div id="mailpoet-no-subscribers',
				__( 'There are no lists available from MailPoet. Are you sure it is activated?', 'Postmatic' )
			);

		$active_subscriber_text = __(
			'Mailpoet is detected. We are ready to import active subscribers from Mailpoet.',
			'Postmatic'
		);

		$list_options = '';
		foreach ( $lists as $list ) {
			$list_options .= html( 'option',
				array( 'value' => $list['list_id'] ),
				$list['name'],
				' (',
				$list['subscribers'],
				')'
			);
		}

		$content = html( 'div id="mailpoet-import-intro"',
			__( '<div> 
				<h3>Mailpoet Import FAQ</h3>
				<h4>Will my subscribers be sent a notification?</h4>
				<p>No. The import process is invisible to subscribers.</p>
				<h4>Which of my subscribers will be imported?</h4>
				<p>We have a very strict policy regarding user imports: <em>we will never allow anyone to be subscribed to a blog running Posmatic without them having opted in</em> (such as subscriber lists bought and imported in bulk for spamming). Because of this we will not import any Mailpoet subscribers unless the following two conditions are true:</p>
				<ol>
				<li>The user has opened an email you sent through Mailpoet</li>
				<li>The user has clicked a link within an email you sent through Mailpoet</li>
				</ol>
				<h5>Why so strict?</h5>
				<p>Bulk importing unwilling users and marking them as opted-in is easy in Mailpoet. If we did not hold our import to a higher standard the magic button below would allow those unwilling users to be imported into Postmatic. And then they would spam your grandmother. Nobody wants that. Plus, if a subscriber does not open or interact with your emails maybe they aren\'t all that good of a match anyway, right? Think of it as spring cleaning :)</p>
				<h4>Can I import multiple lists?</h4>
				<p>Yes. Re-run this importer with as many lists as you like. Postmatic will not create duplicate subscribers.</p>
				<h4>Does Postmatic have lists like Mailpoet does? Is there any way to organize subscribers?</h4>
				<p>No, we do not have a concept of multiple lists. All users are the same in Postmatic. If list segmentation is important to you please let us know by visiting our support site. You\'ll find the link to the right.</p>
				<h4>What will happen to my Mailpoet subscribers?</h4>
				<p>Mailpoet and Postmatic store subscribers in different places within your WordPress database. Your Mailpoet subscribers will always be available to you provided you have Mailpoet activated.</p>
				</div>
				', 'Postmatic' ),
			' ',
			$active_subscriber_text
		);

		$content .= self::send_login_warning_content();

		$content .= html( 'label for="import_list"',
			__( 'List to import: ', 'Postmatic' ),
			html( 'select',
				array( 'name' => 'import_list', 'type' => 'select' ),
				$list_options
			)
		);

		$content .= html( 'input',
			array( 'name' => 'import_type', 'type' => 'hidden', 'value' => $this->mailpoet_import_type )
		);

		return $this->form_wrap( $content, array( 'value' => __( 'Import from Mailpoet' ) ) );
	}

}
