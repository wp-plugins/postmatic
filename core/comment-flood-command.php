<?php

/**
 * Allow users of a comment-flooded post to rejoin the discussion.
 */
class Prompt_Comment_Flood_Command extends Prompt_Comment_Command {

	protected static $rejoin_method = 'rejoin';
	protected static $ignore_method = 'ignore';

	function get_text_command() {

		$message_text = $this->get_message_text();

		if ( preg_match( '/^\s*$/', $message_text, $matches ) )
			return self::$rejoin_method;

		if ( preg_match( '/^\s*rejoin\s*/i', $message_text, $matches ) )
			return self::$rejoin_method;

		if ( preg_match( '/^[\s\*\_]*(rej[io][io]n|resubscribe|subscribe)[\s\*\_]*/i', $message_text, $matches ) )
			return self::$rejoin_method;

		return self::$ignore_method;
	}

	protected function rejoin() {

		$prompt_post = new Prompt_Post( $this->post_id );

		if ( $prompt_post->is_subscribed( $this->user_id ) )
			return;

		$prompt_post->subscribe( $this->user_id );

		Prompt_Subscription_Mailing::send_rejoin_notification( $this->user_id, $prompt_post );

		return;
	}

	function ignore() {
		// We're ignoring any message content but the rejoin command
	}
}