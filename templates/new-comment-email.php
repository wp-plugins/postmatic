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

$commenter_name = $comment_author ? $comment_author->display_name : $comment->comment_author;
$commenter_name = $commenter_name ? $commenter_name : __( 'Anonymous' );

$previous_index = count( $previous_comments );
?>

<h1><span class="capitalize"><?php echo $commenter_name; ?></span> added a comment.</h1>
<h4 id="inreply">In reply to: <?php echo get_the_title( $comment->comment_post_ID  ); ?>.</h4>

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

<?php if ( count( $previous_comments ) > 1 ) : ?>

	<img class="reply-icon" src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" /> <h3 class="reply">Reply to this email to add a comment.</h3>

	<h3>Recently in this conversation...</h3>

	<div class="previous-comments">
		<?php foreach( $previous_comments as $previous_comment ) : $previous_index--; ?>
			<div class="previous-comment-<?php echo $previous_index; ?> comment">
				<div class="comment-header">
					<?php echo get_avatar( $previous_comment ); ?>
					<div class="author-name"><?php echo $previous_comment->comment_author; ?></div>
					<div class="comment-date">
						<?php comment_date( '', $previous_comment->comment_ID ); ?> at
						<?php echo mysql2date( get_option( 'time_format' ), $previous_comment->comment_date ); ?>
					</div>
				</div>
				<div class="comment-body">
					<em><?php echo wpautop( $previous_comment->excerpt ); ?></em>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

<?php endif; ?>

	<img class="reply-icon" src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" /> <h3 class="reply">Reply to this email to add a comment.</h3>
<p>To <strong>add your own comment</strong> reply to this email.<br /><strong>Please note</strong>: Your comment will be published publicly and immediately on <?php bloginfo( 'name' ); ?></p>

<h4>Leave this conversation</h4>
<p>To no longer receive other comments on this thread to reply with the word 'unsubscribe'.</p>
