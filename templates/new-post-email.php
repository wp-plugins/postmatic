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
<h1><?php the_title(); ?></h1>
<?php if ( $featured_image_src ) : ?>
	<img src="<?php echo $featured_image_src[0]; ?>"
	     width="<?php echo intval( $featured_image_src[1] / 2 ); ?>"
	     alt="featured image"
	     class="aligncenter"/>
<?php endif; ?>

<div>
	<?php the_content(); ?>
	<p id="button"><a href="<?php the_permalink(); ?>"
	                  class="btn-secondary"><?php _e( 'View this post online', 'Postmatic' ); ?></a></p>
</div>


<?php if ( comments_open() ) : ?>

	<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" />
		<p class="reply">
			<?php _e( 'Reply to this email to reply to add a comment.', 'Postmatic' ); ?><br />
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
		</p>
	</div>

	<div class="footnote">

		<h3><?php _e( 'Stay in the Loop', 'Postmatic' ); ?></h3>

		<p>
			<?php
			_e(
				'To receive comments on this post directly in your inbox reply with the word <strong>subscribe</strong>.',
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
	</div>

<?php endif; ?>

