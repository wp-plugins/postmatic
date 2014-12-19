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

<h1>
	<?php printf( __( '<span class="capitalize">%s</span> added a comment.', 'Postmatic' ), $commenter_name ); ?>
</h1>

<h4 class="inreply">
	<?php
	printf(
		__( 'In reply to: %s.', 'Postmatic' ),
		'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' .  get_the_title( $comment->comment_post_ID ) . '</a>'
	);
	?>
</h4>

<div class="primary-comment comment">
	<div class="comment-header">
		<?php echo get_avatar( $comment ); ?>
		<div class="author-name"><?php echo $commenter_name; ?></div>
			<div class="comment-date">
				<?php comment_date( '', $comment->comment_ID ); ?>
				<?php /* translators: word between date and time */ _e( 'at', 'Postmatic' ); ?>
				<?php echo mysql2date( get_option( 'time_format' ), $comment->comment_date ); ?>
			</div>
		</div>
		
		<div class="comment-body">
			<?php echo wpautop( $comment->comment_content ); ?>
		</div>
	</div>
</div>

<?php if ( count( $previous_comments ) > 1 ) : ?>

	<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" />
		<h3 class="reply">
			<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
		</h3>
		<p>
			<?php
			printf(
				__(
					'<strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
					'Postmatic'
				),
				'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' .  get_the_title( $comment->comment_post_ID ) . '</a>'
			);
			?>
		</p>
	</div>

	<h3><?php _e( 'Recently in this conversation...', 'Postmatic' ); ?></h3>

	<div class="previous-comments">
		<?php foreach( $previous_comments as $previous_comment ) : $previous_index--; ?>
			<div class="previous-comment-<?php echo $previous_index; ?> comment">
				<div class="comment-header">
					<?php echo get_avatar( $previous_comment ); ?>
					<div class="author-name"><?php echo $previous_comment->comment_author; ?></div>
					<div class="comment-date">
						<?php comment_date( '', $previous_comment->comment_ID ); ?>
						<?php /* translators: word between date and time */ _e( 'at', 'Postmatic' ); ?>
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

<div class="reply-prompt">
	<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" />
	<h3 class="reply">
		<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
	</h3>
	<p>
		<?php
		printf(
			__(
				'<strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
				'Postmatic'
			),
			'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' .
				get_the_title( $comment->comment_post_ID ) . '</a>'
		);
		?>
	</p>
</div>

<h4><?php _e( 'Leave this conversation', 'Postmatic' ); ?></h4>
<p>
	<?php
	_e(
		"To no longer receive other comments on this thread to reply with the word 'unsubscribe'.",
		'Postmatic'
	);
	?>
</p>
