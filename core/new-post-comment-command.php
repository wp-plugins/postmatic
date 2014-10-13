<?php

/**
 * Comment command for a new post
 *
 * Just like comment commands except for unsubscribe replies,
 * which unsubscribe from the post author or site rather than post comments.
 */
class Prompt_New_Post_Comment_Command extends Prompt_Comment_Command {

	/**
	 * Unsubscribe from the post author or site.
	 * @return array
	 */
	protected function unsubscribe() {

		$prompt_post = new Prompt_Post( $this->post_id );
		$prompt_author = new Prompt_User( $prompt_post->get_wp_post()->post_author );

		if ( $prompt_author->is_subscribed( $this->user_id ) ) {

			$prompt_author->unsubscribe( $this->user_id );

			Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $prompt_author );

			return;
		}

		// The user was not subscribed to the post author, so unsubscribe them from the site.

		$prompt_site = new Prompt_Site();

		$prompt_site->unsubscribe( $this->user_id );

		Prompt_Subscription_Mailing::send_unsubscription_notification( $this->user_id, $prompt_site );
	}

}