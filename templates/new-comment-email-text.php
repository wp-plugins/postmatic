<?php
/**
* comment notification email template
* variables in scope:
* @var {WP_User} $comment_author
* @var {WP_User} $subscriber
* @var object $comment
* @var Prompt_Post $subscribed_post
* @var array $previous_comments
*/
?>
<?php
echo Prompt_Html_To_Markdown::h2(
	sprintf(
		__( '%s added a comment on %s', 'Postmatic' ),
		$commenter_name ,
		get_the_title( $comment->comment_post_ID )
	)
);
?>


<?php echo $comment->comment_content; ?>

<?php if ( count( $previous_comments ) > 1 ) : ?>

<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>

<?php
printf(
	__(
		'You\'re invited to respond by replying to this email. If you do, it may be published immediately or held for moderation, depending on the comment policy of %s.',
		'Postmatic'
	),
	get_the_title( $comment->comment_post_ID )
);
?>



<?php echo Prompt_Html_To_Markdown::h1( __( 'Recently in this conversation...', 'Postmatic' ) ); ?>

<?php
wp_list_comments( array(
	'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
	'end-callback' => '__return_empty_string',
	'style' => 'div',
), $previous_comments );
?>


<?php endif; ?>
<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>

<?php
printf(
	__(
		'Please note: Your reply will be published publicly and immediately on %s.',
		'Postmatic'
	),
	get_the_title( $comment->comment_post_ID )
);
?>


<?php
_e(
	"To no longer receive other comments on this thread reply with the word 'unsubscribe'.",
	'Postmatic'
);
?>
