<?php
/**
 * comment notification email template
 * variables in scope:
 * @var {WP_User} $comment_author
 * @var string $commenter_name
 * @var {WP_User} $subscriber
 * @var object $comment
 * @var Prompt_Post $subscribed_post
 * @var array $previous_comments
 * @var bool $is_api_delivery
 */

$previous_index = count( $previous_comments );
?>
<div class="padded">
	<p>
		<?php
		/* translators: %1$s is commenter name, %2$s is post title */
		printf(
			__( '%1$s added a comment in reply to %2$s.', 'Postmatic' ),
			'<span style="text-tranform: capitalize;" class="capitalize">' . $commenter_name . '</span>',
			'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>'
		);
		?>
	</p>
	
	<div class="primary-comment comment">
		<div class="comment-header">
			<?php echo $is_api_delivery ? get_avatar( $comment ) : ''; ?>
			<div class="author-name">
				<?php if ( $is_api_delivery and $comment->comment_author_url ) : ?>
					<a href="<?php echo esc_url( $comment->comment_author_url ); ?>">
						<?php echo $commenter_name; ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="comment-body">
				<?php echo wpautop( $comment->comment_content ); ?>
			</div>
		</div>
	</div>


	<?php if ( count( $previous_comments ) > 1 and $is_api_delivery ) : ?>

		<div class="reply-prompt">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>

			<h3 class="reply">
				<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
				<small>
					<br/>
					<?php
					printf(
						__(
							'<strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
							'Postmatic'
						),
						'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>'
					);
					?>
				</small>
			</h3>
		</div>

	</div>


	<div class="padded gray">
		<h3><?php _e( 'Recently in this conversation...', 'Postmatic' ); ?></h3>

		<div class="previous-comments">
			<?php foreach ( $previous_comments as $previous_comment ) : $previous_index--; ?>
				<div class="previous-comment-<?php echo $previous_index; ?> comment">
					<div class="comment-header">
						<?php echo get_avatar( $previous_comment ); ?>
						<div class="author-name">
							<?php if ( $previous_comment->comment_author_url ) : ?>
								<a href="<?php echo $previous_comment->comment_author_url; ?>">
									<?php echo $previous_comment->comment_author; ?>
								</a>
							<?php else : ?>
								<?php echo $previous_comment->comment_author; ?>
							<?php endif; ?>
						</div>
						<div class="comment-date">
							<?php comment_date( '', $previous_comment->comment_ID ); ?>
							<?php /* translators: word between date and time */
							_e( 'at', 'Postmatic' ); ?>
							<?php echo mysql2date( get_option( 'time_format' ), $previous_comment->comment_date ); ?>
						</div>
						<div class="comment-body">
							<em><?php echo wpautop( $previous_comment->excerpt ); ?></em>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png'; ?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>

		<h3 class="reply">
			<?php printf( __( 'Reply to this email to reply to %s.', 'Postmatic' ), $commenter_name ); ?>
			<small>
				<?php
				printf(
					__(
						'<br /><strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
						'Postmatic'
					),
					'<a href="' . get_permalink( $comment->comment_post_ID ) . '">' .
					get_the_title( $comment->comment_post_ID ) . '</a>'
				);
				?>
			</small>
		</h3>
	</div>
</div>

<div class="padded gray">
	<h4><?php _e( 'Want to leave this conversation?', 'Postmatic' ); ?></h4>

	<p>
		<?php
		_e(
			"To no longer receive other comments on this thread reply with the word 'unsubscribe'.",
			'Postmatic'
		);
		?>
	</p>
</div>

