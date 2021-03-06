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

		// There is no way to suppress mailing when restoring a trashed post, so we always do it
		if ( 'trash' == $old_status )
			return;

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING )
			return;

		if ( self::ignore_published_post( $post->ID ) )
			return;

		$prompt_post = new Prompt_Post( $post );

		if ( ! $prompt_post->unsent_recipient_ids() or Prompt_Admin_Delivery_Metabox::suppress_email( $post->ID ) )
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

		wp_schedule_single_event(
			time(),
			'prompt/comment_mailing/send_notifications',
			array( $id )
		);
	}

	/**
	 * When a comment is approved notify subscribers if needed.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $comment
	 */
	public static function action_transition_comment_status( $new_status, $old_status, $comment ) {

		if ( defined( 'WP_IMPORTING' ) and WP_IMPORTING ) {
			return;
		}

		if ( 'approved' != $new_status or $old_status == $new_status or !empty( $comment->comment_type ) ) {
			return;
		}

		wp_schedule_single_event(
			time(),
			'prompt/comment_mailing/send_notifications',
			array( $comment )
		);
	}

	/**
	 * When a comment is unapproved, notify moderators for API user.
	 *
	 * This is inspired by the Crowd Control plugin, to let moderators know when the crowd has unapproved a comment.
	 *
	 * @since 1.5.0
	 *
	 * @param object $comment
	 */
	public static function action_comment_approved_to_unapproved( $comment ) {

		if ( current_user_can( 'moderate_comments' ) ) {
			return;
		}

		$enabled_message_types = Prompt_Core::$options->get( 'enabled_message_types' );

		if ( !in_array( Prompt_Enum_Message_Types::COMMENT_MODERATION, $enabled_message_types ) ) {
			return;
		}

		wp_notify_moderator( $comment->comment_ID );
	}

	/**
	 * Override native comment moderation notifications.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/comment_moderation_recipients/
	 *
	 * @param array $addresses
	 * @param int $comment_id
	 * @return array Empty array to short circuit native notifications.
	 */
	public static function filter_comment_moderation_recipients( $addresses, $comment_id ) {

		$enabled_message_types = Prompt_Core::$options->get( 'enabled_message_types' );

		if ( !in_array( Prompt_Enum_Message_Types::COMMENT_MODERATION, $enabled_message_types ) )
			return $addresses;

		Prompt_Moderation_Mailing::send_notifications( $comment_id, $addresses );

		return array();
	}

	/**
	 * Override native comment notifications.
	 *
	 * @since 1.4.4
	 *
	 * @link https://developer.wordpress.org/reference/hooks/comment_notification_recipients/
	 *
	 * @param array $addresses
	 * @return array Empty array to short circuit native notifications.
	 */
	public static function filter_comment_notification_recipients( $addresses ) {

		if ( Prompt_Core::$options->get( 'auto_subscribe_authors' ) ) {
			// Posmatic will send its own notifications on the transition_comment_status hook
			return array();
		}

		return $addresses;
	}

	/**
	 * Whether to ignore a published post.
	 *
	 * Currently only ignores Polylang translations.
	 *
	 * @param $post_id
	 * @return bool
	 */
	protected static function ignore_published_post( $post_id ) {

		if ( ! function_exists( 'pll_default_language' ) )
			return false;

		$default_slug = pll_default_language( 'slug' );

		$post_slug = pll_get_post_language( $post_id, 'slug' );

		return ( $default_slug !== $post_slug );
	}

}
