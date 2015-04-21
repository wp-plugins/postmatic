<?php
/**
 * comment notification email template
 * variables in scope:
 * @var WP_User $comment_author
 * @var WP_User $subscriber
 * @var object $comment
 * @var string $commenter_name
 * @var Prompt_Post $subscribed_post
 * @var string $subscribed_post_author_name
 * @var string $subscribed_post_title_link
 * @var array $previous_comments
 * @var WP_User $parent_author
 * @var string $parent_author_name
 * @var object $parent_comment
 */
?>
<?php if ( $parent_author and $parent_author->ID == $subscriber->ID ) : ?>
<?php
echo Prompt_Html_To_Markdown::h1(
	sprintf(
		__( '%s replied to your comment on %s:', 'Postmatic' ),
		$commenter_name,
		get_the_title( $comment->comment_post_ID )
	)
);
?>
<?php else : ?>
<?php
echo Prompt_Html_To_Markdown::h1(
	sprintf(
		__( '%s left a reply to a comment by %s on %s:', 'Postmatic' ),
		$commenter_name,
		$parent_author_name,
		get_the_title( $comment->comment_post_ID )
	)
);
?>
<?php endif; ?>

<?php echo Prompt_Html_To_Markdown::convert( $comment->comment_content ); ?>


<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>





<?php echo Prompt_Html_To_Markdown::h2( __( 'Here\'s a recap of this post and conversation:', 'Postmatic' ) ); ?>

<?php
/* translators: %1$s is post title, %2$s date, %3$s time, %4$s author */
printf(
	__( '%1$s was published on %2$s by %4$s.' ),
	get_the_title( $subscribed_post->id() ),
	get_the_date( '', $subscribed_post->get_wp_post() ),
	get_the_time( '', $subscribed_post->get_wp_post() ),
	$subscribed_post_author_name
);
?>

<?php echo $subscribed_post->get_excerpt(); ?>


<?php
printf(
	__( 'There were %d comments previous to this. Here is this reply in context:', 'Postmatic' ),
	wp_count_comments( $subscribed_post->id() )->approved
);
?>

<?php
wp_list_comments( array(
	'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
	'end-callback' => '__return_empty_string',
	'style' => 'div',
), $previous_comments );
?>



<?php printf( __( '* Reply to this email to reply to %s. *', 'Postmatic' ), $commenter_name ); ?>



<?php
_e(
	"To no longer receive other comments or replies in this discussion reply with the word 'unsubscribe'.",
	'Postmatic'
);
?>
