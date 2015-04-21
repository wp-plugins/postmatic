<?php
/**
 * comment moderation text template
 *
 * @var object $comment
 * @var string $commenter_name
 * @var string $type
 * @var string $comment_author_domain
 * @var WP_Post $post
 */

?>
<?php
echo Prompt_Html_To_Markdown::h1(
	sprintf(
		__( 'There is a new comment to moderate from %s on %s.', 'Postmatic' ),
		$commenter_name,
		get_the_title( $comment->comment_post_ID )
	)
);
?>


"<?php echo Prompt_Html_To_Markdown::convert( $comment->comment_content ); ?>"


<?php echo Prompt_Html_To_Markdown::h1( __( 'Details about the comment', 'Postmatic' ) ); ?>

- <?php  echo __( 'Author', 'Postmatic' ) . ': ' . $comment->comment_author; ?>

- <?php echo __( 'Email', 'Postmatic' ) . ': ' . $comment->comment_author_email; ?>

- <?php echo __( 'IP Address', 'Postmatic' ) . ': http://whois.arin.net/rest/ip/' . $comment->comment_author_IP; ?>

- <?php echo __( 'Domain', 'Postmatic' ) . ':  http://' . $comment_author_domain; ?>


<?php echo Prompt_Html_To_Markdown::h2( __( 'Approve?', 'Postmatic' ) ); ?>

<?php _e( 'Reply to this email with a blank message or the word \'approve.\'', 'Postmatic' ); ?>

<?php echo admin_url( 'comment.php?action=approve&c=' . $comment->comment_ID ); ?>


<?php echo prompt_html_to_markdown::h2( __( 'Trash?', 'postmatic' ) ); ?>

<?php _e( 'Reply to this email with the word \'trash.\'', 'Postmatic' ); ?>

<?php echo admin_url( 'comment.php?action=trash&c=' . $comment->comment_ID ); ?>


<?php echo prompt_html_to_markdown::h2( __( 'Spam?', 'Postmatic' ) ); ?>

<?php _e( 'Reply to this email with a the word \'spam.\'', 'Postmatic' ); ?>

<?php echo admin_url( 'comment.php?action=spam&c=' . $comment->comment_ID ); ?>