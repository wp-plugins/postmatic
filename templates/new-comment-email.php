<?php
/**
* comment notification email template
* variables in scope:
* @var {WP_User} $comment_author
* @var {WP_User} $subscriber
* @var object $comment
* @var Prompt_Post $subscribed_post
*/

$commenter_name = $comment_author ? $comment_author->display_name : $comment->comment_author;
$commenter_name = $commenter_name ? $commenter_name : __( 'Anonymous' );
?>
<h1><span class="capitalize"><?php echo $commenter_name; ?></span> added a comment.</h1>
<h4>In reply to <em><?php echo get_the_title( $comment->comment_post_ID  ); ?></em> <strong class="capitalize"><?php echo $commenter_name; ?></strong> says:</h4>
<blockquote>
	<em><?php echo wpautop( $comment->comment_content ); ?></em>
</blockquote>

<p class="padding"><a href="<?php echo get_permalink( $comment->comment_post_ID ); ?>#comments" class="btn-secondary">View this comment online</a></p>

<h3>Add a Comment</h3>
<p>To <strong>add your own comment</strong> reply to this email.<br /><strong>Please note</strong>: Your comment will be published publicly and immediately on <?php bloginfo( 'name' ); ?></p>

<h4>Leave this conversation</h4>
<p>To no longer receive other comments on this thread to reply with the word 'unsubscribe'.</p>