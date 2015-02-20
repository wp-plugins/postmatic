<?php

class Prompt_Email_Comment_Rendering {

	public static function render( $comment, $args, $depth ) {

		// Note that WordPress closes the div for you, do not close it here!
		// https://codex.wordpress.org/Function_Reference/wp_list_comments
		printf( '<div class="%s">', implode( ' ', get_comment_class( '', $comment, $comment->comment_post_ID ) ) );

		echo html( 'div class="comment-header"',
			get_avatar( $comment ),
			html( 'div class="author-name"',
				get_comment_author( '', $comment )
			),
			html( 'div class="comment-body"',
				apply_filters( 'comment_text', get_comment_text( $comment->comment_ID ), $comment )
			)
		);
	}

}