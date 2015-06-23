<?php
/**
 * comment flood notification email
 *
 * @var {WP_User} $subscriber
 * @var Prompt_Post $post
 */
?>
<?php
echo Prompt_Html_To_Markdown::h1(
	sprintf(
		__( 'Heads up: the conversation around %s is heating up.', 'Postmatic' ),
		get_the_title( $post->id() )
	)
);
?>


<?php
_e(
	'You love email. But maybe not this much. We\'re going to pause notifications for you to prevent a flood in your inbox. You will no longer receive new comments on this post.',
	'Postmatic'
);
?>


<?php
_e(
	'If you really do want to keep up with this thread, reply to this email with the word \'rejoin\'. We\'ll send you a recap and renew your subscription.',
	'Postmatic'
);
?>


<?php _e( 'View this post online', 'Postmatic' ); ?> at <?php echo get_permalink( $post->id() ); ?>
