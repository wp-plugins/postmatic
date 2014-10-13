<?php

/**
 * Handle WordPress events that could trigger mailings.
 */
class Prompt_Outbound_Handling {

	/**
	 * Any time a post is published schedule notifications.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	public static function action_transition_post_status( $new_status, $old_status, $post ) {

		if ( 'publish' == $old_status or 'publish' != $new_status )
			return;

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING )
			return;

		$prompt_post = new Prompt_Post( $post );

		if ( $post->post_author and Prompt_Core::$options->get( 'auto_subscribe_authors' ) )
			$prompt_post->subscribe( $post->post_author );

		if ( ! $prompt_post->unsent_recipient_ids() or Prompt_Admin_Delivery_Metabox::suppress_email() )
			return;

		wp_schedule_single_event(
			time(),
			'prompt/post_mailing/send_notifications',
			array( $post->ID )
		);
	}

	/**
	 * When a comment is published notify subscribers if needed.
	 *
	 * @param int $id
	 * @param object $comment
	 */
	public static function action_wp_insert_comment( $id, $comment ) {
		if ( $comment->comment_approved != '1'  or !empty( $comment->comment_type ) )
			return;

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING )
			return;

		if ( $comment->comment_parent ) {
			Prompt_Comment_Mailing::send_reply_notification( $comment );
			return;
		}

		$prompt_post = new Prompt_Post( $comment->comment_post_ID );

		if ( ! $prompt_post->subscriber_ids() )
			return;

		wp_schedule_single_event(
			time(),
			'prompt/comment_mailing/send_notifications',
			array( $id )
		);
	}

	public static function action_transition_comment_status( $new_status, $old_status, $comment ) {
		if ( 'approved' != $new_status or $old_status == $new_status or !empty( $comment->comment_type ) )
			return;

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING )
			return;

		wp_schedule_single_event(
			time(),
			'prompt/comment_mailing/send_notifications',
			array( $comment )
		);
	}

}
