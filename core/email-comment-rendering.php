<?php

class Prompt_Email_Comment_Rendering {

	public static function render( $comment, $args, $depth ) {

		echo html( 'div',
			array( 'class' => implode( ' ', get_comment_class( '', $comment, $comment->comment_post_ID ) ) ),
			html( 'div class="comment-header"',
				get_avatar( $comment ),
				html( 'div class="author-name"',
					$comment->comment_author
				),
				html( 'div class="comment-date"',
					get_comment_date( '', $comment ),
					' ',
					/* translators: word between date and time */ __( 'at', 'Postmatic' ),
					' ',
					mysql2date( get_option( 'time_format' ), $comment->comment_date )
				)
			),
			html( 'div class="comment-body"',
				wpautop( $comment->comment_content )
			)
		);
	}

}