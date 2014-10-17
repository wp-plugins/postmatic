<?php

class Prompt_Comment_Form_Handling {

	const SUBSCRIBE_CHECKBOX_NAME = 'prompt_comment_subscribe';
	const UNSUBSCRIBE_ACTION = 'prompt_comment_unsubscribe';

	/** @var Prompt_Post */
	protected static $prompt_post;

	/**
	 * Subscribe or unsubscribe a commenter.
	 *
	 * Called by the comment_post action.
	 *
	 * @param int $comment_id
	 * @param string $status
	 */
	public static function handle_form( $comment_id, $status ) {

		if ( !Prompt_Core::$options->get( 'augment_comment_form' ) or '1' != $status )
			return;

		$comment = get_comment( $comment_id );

		if ( empty( $comment->user_id ) and empty( $comment->comment_author_email ) )
			return;

		$user_id = $comment->user_id;

		if ( !$user_id ) {
			$user = get_user_by( 'email', $comment->comment_author_email );
			$user_id = $user ? $user->ID : null;
		}

		$checked = isset( $_POST[self::SUBSCRIBE_CHECKBOX_NAME] );

		if ( !$checked )
			return;

		$prompt_post = new Prompt_Post( $comment->comment_post_ID );

		if ( !$user_id ) {

			$user_data = array( 'display_name' => $comment->comment_author );

			Prompt_Subscription_Mailing::send_agreement(
				$prompt_post,
				$comment->comment_author_email,
				$user_data
			);

			return;
		}

		if ( !$prompt_post->is_subscribed( $user_id ) ) {

			$prompt_post->subscribe( $user_id );

			Prompt_Subscription_Mailing::send_subscription_notification( $user_id, $prompt_post );
		}
	}

	/**
	 * Echo comment form content.
	 *
	 * Called by the comment_form action.
	 *
	 * @param $post_id
	 */
	public static function form_content( $post_id ) {

		if ( !Prompt_Core::$options->get( 'prompt_key' ) or !Prompt_Core::$options->get( 'augment_comment_form' ) )
			return;

		$script = new Prompt_Script( array(
			'handle' => 'prompt-comment-form',
			'path' => 'js/comment-form.js',
			'dependencies' => array( 'jquery' ),
		) );

		$script->enqueue();

		$script->localize(
			'prompt_comment_form_env',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( Prompt_Ajax_Handling::AJAX_NONCE ),
				'action' => self::UNSUBSCRIBE_ACTION,
				'post_id' => $post_id,
			)
		);

		self::$prompt_post = new Prompt_Post( $post_id );

		$current_user = Prompt_User_Handling::current_user();

		if ( $current_user and self::$prompt_post->is_subscribed( $current_user->ID ) )
			return;

		echo html( 'label id="prompt-comment-subscribe"',
			html( 'input',
				array(
					'type' => 'checkbox',
					'name' => self::SUBSCRIBE_CHECKBOX_NAME,
					'value' => '1',
				)
			),
			'&nbsp;',
			__( 'Participate in this conversation via e-mail', 'Prompt_Core' )
		);

	}

	public static function after_form() {

		if ( !Prompt_Core::$options->get( 'augment_comment_form' ) )
			return;

		$current_user = Prompt_User_Handling::current_user();

		if ( !$current_user or !self::$prompt_post->is_subscribed( $current_user->ID ) )
			return;


		echo html( 'div class="prompt-unsubscribe"',
			html( 'div class=".loading-indicator" style="display: none;"',
				html( 'img', array( 'src' => path_join( Prompt_Core::$url_path, 'media/ajax-loader.gif' ) ) )
			),
			html( 'p',
				__( 'You are subscribed to new comments on this post.', 'Prompt_Core' )
			),
			scbForms::input( array(
				'type' => 'submit',
				'name' => self::UNSUBSCRIBE_ACTION,
				'value' => __( 'Unsubscribe', 'Prompt_Core' ),
			) )
		);

	}

}