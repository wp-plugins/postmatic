<?php
/**
* comment notification email template
* variables in scope:
* @var {WP_User} $comment_author
* @var {WP_User} $parent_author
* @var object $comment
* @var Prompt_Post $subscribed_post
*/
$commenter_name = $comment_author ? $comment_author->display_name : $comment->comment_author;
$commenter_name = $commenter_name ? $commenter_name : __( 'Anonymous' );
?>
<h1>Comment Notification</h1>
<p>This is a <strong>one-way message</strong> to let you know that <?php echo $commenter_name; ?> replied to your comment on
<a href="<?php echo get_permalink( $subscribed_post->id() ); ?>"><?php echo $subscribed_post->get_wp_post()->post_title; ?></a>. <strong>Please do not reply to this email.</strong></p>
<h2><?php echo $commenter_name; ?> says:</h2>
<blockquote>
	<em><?php echo wpautop( $comment->comment_content ); ?></em>
</blockquote>
<p><strong>Please note</strong> that replies to this email will not be sent to <?php echo $commenter_name; ?> nor posted online. To continue this conversation please visit <?php echo get_comment_link( $comment ); ?>.
</p>
<p><a href="<?php echo get_comment_link( $comment ); ?>" class="btn-primary">Reply Online</a></p>