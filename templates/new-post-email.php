<?php
/**
 * HTML post notification email template
 *
 * Post globals are set so template tags like the_title() and the_content() will work.
 *
 * @see prompt/post_email/template_data
 *
 * @var array $featured_image_src
 * @var Prompt_Interface_Subscribable $subscribed_object
 */
?>

	<h1 class="padded" id="the_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
<?php if ( $featured_image_src ) : ?>
	<img src="<?php echo $featured_image_src[0]; ?>"
	     width="<?php echo intval( $featured_image_src[1] / 2 ); ?>"
	     alt="featured image"
	     class="aligncenter featured"/>
<?php endif; ?>

<div class="padded">
<div id="the_content">
	<?php the_content(); ?>
	<p id="button"><a href="<?php the_permalink(); ?>"
	                  class="btn-secondary"><?php _e( 'View this post online', 'Postmatic' ); ?></a></p>
</div>


<?php if ( comments_open() ) : ?>

	<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" />
		<h3 class="reply">
			<?php _e( 'Reply to this email to add a comment. Your email address will not be shown.', 'Postmatic' ); ?><br />
			<small>
				<?php
				printf(
					__(
						'<strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
						'Postmatic'
					),
					get_bloginfo( 'name' )
				);
				?>
			</small>
		</h3>
	</div>
</div>

	<div class="footnote padded gray">

		<h3><?php _e( 'Get the latest comments and stay in the loop', 'Postmatic' ); ?></h3>

		<p>
			<?php
			_e(
				'To subscribe to comments on this post and receive a copy of the conversation so far reply with the word <strong>subscribe</strong>.',
				'Postmatic'
			);
			?>
		</p>

		<h4><?php _e( 'Manage your subscription', 'Postmatic' ); ?></h4>
		<p>
			<?php
			printf(
				__( "To <strong>unsubscribe</strong> to %s reply with the word 'unsubscribe'.", 'Postmatic' ),
				$subscribed_object->subscription_object_label()
			);
			?>
		</p>
			<h3 class="noforward"><?php _e( 'Please do not forward this email', 'Postmatic' ); ?></h3>
			<p>
				<?php
				printf(
					__(
						'This email was meant specifically for you and any replies sent to it will post as a comment in your name. If you would like to share this post please use this url: %s.',
						'Postmatic'
					),
					get_permalink()
				);
				?>
			</p>
	</div>

<?php elseif ( !comments_open() ) : ?>

	<h4><?php _e( 'Comments on this post are closed', 'Postmatic' ); ?> </h4>

	<p><?php _e( 'You can reply to this email to send a note directly to the post author.', 'Postmatic' ); ?></p>

<?php endif; ?>


