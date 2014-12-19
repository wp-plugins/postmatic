<?php

class Prompt_Admin_Support_Options_Tab extends Prompt_Admin_Options_Tab {

	protected $send_diagnostics_name = 'send_diagnostic_report';
	protected $support_url = 'http://docs.gopostmatic.com';
	protected $widget_url = 'http://gopostmatic.com/widgets';
	protected $ticket_url = 'http://gopostmatic.com/bug';

	public function name() {
		return __( 'Get Support', 'Postmatic' );
	}

	public function form_handler() {

		$environment = new Prompt_Environment();

		$user = wp_get_current_user();

		$email = new Prompt_Email( array(
			'to_address' => 'support@gopostmatic.com',
			'from_address' => $user->user_email,
			'from_name' => $user->display_name,
			'subject' => sprintf( __( 'Diagnostics from %s', 'Postmatic' ), get_option( 'blogname' ) ),
			'content_type' => 'application/json',
			'template' => '',
			'message' => json_encode( $environment->to_array() ),
		) );

		$sent = Prompt_Factory::make_mailer()->send_one( $email );

		if ( is_wp_error( $sent ) ) {
			Prompt_Logging::add_error(
				'diagnostic_submission_error',
				__( 'Diagnostics could not be sent, please try a bug report.', 'Postmatic' ),
				$sent
			);
			return;
		}

		$this->add_notice( __( 'Diagnostics <strong>sent</strong>.', 'Postmatic' ) );
	}

	public function render() {
		$content = html( 'h2', __( 'Support, News, and Documentation', 'Postmatic' ) );

		$content .= html( 'div id="postmatic-documentation" class="widget"',
			html( 'h3', __( 'Documentation', 'Postmatic' ) ),
			html( 'p', __( 'Find answers to the most common questions and ask your own.', 'Postmatic' ) ),
			html( 'p',
				html( 'a',
					array( 'href' => $this->support_url ),
					__( 'Find Answers', 'Postmatic' )
				)
			)
		);

		$content .= html( 'div id="postmatic-widget-directory" class="widget"',
			html( 'h3', __( 'Widget Directory', 'Postmatic' ) ),
			html( 'p', __( 'We\'ve hand curated dozen of widgets. Get the most out of your email template.', 'Postmatic' ) ),
			html( 'p',
				html( 'a',
					array( 'href' => $this->widget_url ),
					__( 'Research Widgets', 'Postmatic' )
				)
			)
		);

		$content .= html( 'div id="postmatic-support" class="widget"',
			html( 'h3', __( 'Get Support', 'Postmatic' ) ),
			html( 'p', __( 'Let us know if something isn\'t right. We\'ll fix it right away.', 'Postmatic' ) ),
			html( 'p',
				html( 'a',
					array( 'href' => $this->ticket_url ),
					__( 'Submit a Ticket', 'Postmatic' )
				)
			)
		);

		$content .= html( 'h3 id="news-header"', __( 'The Latest Postmatic News:', 'Postmatic' ) );
		
		$content .= $this->news_content();

		return $this->form_wrap( $content, array( 'value' => __( 'Advanced: Send Diagnostic Info to Support', 'Postmatic') ) );
	}

	/**
	 * Assemble news widget content
	 * @return string content
	 */
	protected function news_content() {
		$feed = fetch_feed( 'http://gopostmatic.com/feed' );

		if ( is_wp_error( $feed ) )
			return __( 'No news available at the moment.', 'Postmatic' );

		$item_count = $feed->get_item_quantity( 4 );

		/** @var SimplePie_Item[] $items */
		$items = $feed->get_items( 0, $item_count );

		$news_items = '';
		foreach ( $items as $item ) {
			$news_items .= html(
				'li',
				html( 'small', $item->get_date( 'j F Y ' ) ),
				html( 'a', array( 'href' => $item->get_permalink() ), $item->get_title() ),
				html( 'div', $item->get_description() )
			);
		}

		return html( 'ul class="prompt-news"', $news_items );
	}

}
