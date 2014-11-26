<?php
/**
 * comment notification email template
 * variables in scope:
 * @var WP_User $comment_author
 * @var WP_User $subscriber
 * @var object $comment
 * @var Prompt_Post $subscribed_post
 * @var array $previous_comments
 * @var WP_User $parent_author
 * @var object $parent_comment
 */
$commenter_name = $comment_author ? $comment_author->display_name : $comment->comment_author;
$commenter_name = $commenter_name ? $commenter_name : __( 'Anonymous' );

$parent_author_name = $parent_author ? $parent_author->display_name : $parent_comment->comment_author;
$parent_author_name = $parent_author_name ? $parent_author_name : __( 'Anonymous' );

$previous_index = count( $previous_comments );
?>

<?php if ( $parent_author and $parent_author->ID == $subscriber->ID ) : ?>
<h1><span class="capitalize"><?php echo $commenter_name; ?></span> replied to your comment.</h1>
<div class="previous-comment-1 the-reply">
<h4 class="inreply">On <?php comment_date( '', $parent_comment->comment_ID ); ?> at
			<?php echo mysql2date( get_option( 'time_format' ), $parent_comment->comment_date ); ?> you made the following comment on
	<a href="<?php echo get_permalink( $comment->comment_post_ID ); ?>"><?php echo get_the_title( $comment->comment_post_ID  ); ?></a>:</h4>
<div class="quote">
	<?php echo get_avatar( $parent_comment ); ?>
<div class="reply-content"><em><?php echo wpautop( $parent_comment->comment_content ); ?></em></div>
</div>
</div>

<div class="new-reply">
	<h4 class="inreply">Just now <?php echo $commenter_name; ?> replied:</h4>
	<?php echo get_avatar( $comment ); ?>
	<div class="reply-content"><?php echo wpautop( $comment->comment_content ); ?></div>
</div>

<?php else : ?>


<h1><span class="capitalize"><?php echo $commenter_name; ?></span> left a reply to a comment by <?php echo $parent_author_name; ?></h1>

<h4 class="inreply">
	While discussing
	<a href="<?php echo get_permalink( $comment->comment_post_ID ); ?>"><?php echo get_the_title( $comment->comment_post_ID  ); ?></a>
	<?php echo $parent_author_name; ?> wrote:
</h4>

<div class="previous-comments">
	<div class="comment-header">
		<?php echo get_avatar( $parent_comment ); ?>
		<div class="author-name"><?php echo $parent_author_name; ?></div>
		<div class="comment-date">
			<?php comment_date( '', $parent_comment->comment_ID ); ?> at
			<?php echo mysql2date( get_option( 'time_format' ), $parent_comment->comment_date ); ?>
		</div>
	</div>

	<div class="comment-body">
		<?php echo wpautop( $parent_comment->comment_content ); ?>
	</div>
</div>

<div class="new-reply">
<h4 id="inreply">Just now <?php echo $commenter_name; ?> replied:</h4>

<div class="primary-comment comment">
	<div class="comment-header">
		<?php echo get_avatar( $comment ); ?>
		<div class="author-name"><?php echo $commenter_name; ?></div>
		<div class="comment-date">
			<?php comment_date( '', $comment->comment_ID ); ?> at
			<?php echo mysql2date( get_option( 'time_format' ), $comment->comment_date ); ?>
		</div>
	</div>

	<div class="comment-body">
		<?php echo wpautop( $comment->comment_content ); ?>
	</div>
</div>
</div>
<?php endif; ?>

<div class="reply-prompt"><img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" /> <h3 class="reply">Reply to this email to reply to <?php echo $commenter_name; ?>.</h3>
<p><strong>Please note</strong>: Your reply will be published publicly and immediately on <?php bloginfo( 'name' ); ?>.</p></div>


<h4>Leave this conversation</h4>
<p>To no longer receive other comments or replies in this discussion to reply with the word 'unsubscribe'.</p>
