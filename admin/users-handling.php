<?php

class Prompt_Admin_Users_Handling {

	protected static $subscriptions_column_name = 'prompt_subscriptions';

	/**
	 * Add columns to the users table.
	 *
	 * @see manage_users_column filter trigger
	 *
	 * @param $columns
	 * @return mixed
	 */
	public static function manage_users_columns( $columns ) {
		$columns[self::$subscriptions_column_name] = __( 'Postmatic Subscriptions', 'Prompt_Core' );
		return $columns;
	}

	/**
	 * Build output for the subscriptions column.
	 *
	 * @see manage_users_custom_column filter trigger
	 *
	 * @param string $value
	 * @param string $column_name
	 * @param int $user_id
	 * @return string column content
	 */
	public static function subscriptions_column( $value, $column_name, $user_id ) {

		if ( self::$subscriptions_column_name !== $column_name )
			return $value;

		$column_content = '';

		$edit_url = esc_url(
			add_query_arg(
				'wp_http_referer',
				urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_id )
			)
		);

		$prompt_site = new Prompt_Site();
		if ( $prompt_site->is_subscribed( $user_id ) )
			$column_content .= html( 'a',
				array( 'href' => $edit_url . '#prompt-site-subscription' ),
				__( 'New Posts', 'Prompt_Core' ),
				'<br/>'
			);

		$author_count = count( Prompt_User::subscribed_object_ids( $user_id ) );
		if ( $author_count > 0 )
			$column_content .= html( 'a',
				array( 'href' => $edit_url . '#prompt-author-subscriptions' ),
				sprintf(
					_n( '%d Author', '%d authors', $author_count ),
					$author_count
				),
				'<br/>'
			);

		$post_count = count( Prompt_Post::subscribed_object_ids( $user_id ) );
		if ( $post_count > 0 )
			$column_content .= html( 'a',
				array( 'href' => $edit_url . '#prompt-post-subscriptions' ),
				sprintf(
					_n( '%d Conversations', '%d Conversations', $post_count ),
					$post_count
				),
				'<br/>'
			);

		return $column_content;
	}
}