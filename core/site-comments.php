<?php

class Prompt_Site_Comments extends Prompt_Option_Subscribable_Object {
	const OPTION_KEY = 'prompt_comments_subscribed_user_ids';

	protected function option_key() {
		return self::OPTION_KEY;
	}

	public function id() {
		return get_current_blog_id();
	}

	public function subscription_url() {
		return get_edit_profile_url();
	}

	public function subscription_object_label() {
		return sprintf( __( 'All comments on %s', 'Postmatic' ), get_option( 'blogname' ) );
	}

	public function subscription_description() {
		return sprintf(
			__(
				'You have successfully subscribed to all comments on %s and will receive new comments as soon as they are published.',
				'Postmatic'
			),
			get_option( 'blogname' )
		);
	}

	public static function subscribed_object_ids( $user_id ) {
		$ids = array();
		$site = new Prompt_Site_Comments();
		if ( $site->is_subscribed( $user_id ) )
			$ids[] = $site->id();
		return $ids;
	}

	public static function all_subscriber_ids() {
		// Currently just the default site comments subscribers
		$site = new Prompt_Site_Comments();
		return $site->subscriber_ids();
	}

}