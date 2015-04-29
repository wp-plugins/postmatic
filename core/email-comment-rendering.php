<?php

class Prompt_Email_Comment_Rendering {

	protected static $post_id;
	protected static $flood_comment;

	public static function render( $comment, $args, $depth ) {

		self::set_context( $comment );

		// Note that WordPress closes the div for you, do not close it here!
		// https://codex.wordpress.org/Function_Reference/wp_list_comments
		printf(
			'<div class="%s">',
			implode( ' ', get_comment_class( self::base_classes( $comment ), $comment, $comment->comment_post_ID ) )
		);

		echo html( 'div class="comment-header"',
			get_avatar( $comment ),
			html( 'div class="author-name"',
				get_comment_author_link( $comment->comment_ID )
			),
			html( 'div class="comment-body"',
				apply_filters( 'comment_text', get_comment_text( $comment->comment_ID ), $comment )
			)
		);
	}

	public static function render_text( $comment, $args, $depth ) {

		self::set_context( $comment );

		echo self::indent( '', $depth );

		echo self::indent(
			'- ' . $comment->comment_author . ' -',
			$depth
		);

		echo self::indent( Prompt_Html_To_Markdown::convert( wpautop( $comment->comment_content ) ), $depth );


	}

	protected static function indent( $text, $depth ) {
		$lines = $text ? preg_split( '/$\R?^/m', $text ) : array( '' );
		$indented_text = '';
		foreach( $lines as $line ) {
			$indented_text .= str_repeat( '  ', $depth - 1 ) . $line . "\n";
		}
		return $indented_text;
	}

	protected static function set_context( $comment ) {

		if ( self::$post_id == $comment->comment_post_ID )
			return;

		self::$post_id = $comment->comment_post_ID;
		$prompt_post = new Prompt_Post( self::$post_id );

		$flood_comment_id = $prompt_post->get_flood_control_comment_id();
		if ( $flood_comment_id )
			self::$flood_comment = get_comment( $flood_comment_id );

	}

	protected static function base_classes( $comment ) {

		if ( ! self::$flood_comment )
			return '';

		if ( self::$flood_comment->comment_ID == $comment->comment_ID )
			return 'flood-point post-flood';

		if ( self::$flood_comment->comment_date_gmt < $comment->comment_date_gmt )
			return 'post-flood';

		return '';
	}

}