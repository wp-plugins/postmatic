<?php
/**
 * HTML post notification email template
 *
 * Post globals are set so template tags like the_title() and the_content() will work.
 *
 * @see prompt/post_email/template_data
 *
 * @var array                           $featured_image_src
 * @var bool                            $excerpt_only
 * @var string                          $alternate_versions_menu
 * @var Prompt_Interface_Subscribable   $subscribed_object
 * @var bool                            $is_api_delivery
 * @var bool                            $will_strip_content
 */
?>



<h1 class="padded" id="the_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
<?php echo $alternate_versions_menu; ?>

<?php if ( $featured_image_src and $is_api_delivery ) : ?>
	<img src="<?php echo $featured_image_src[0]; ?>"
	     width="<?php echo intval( $featured_image_src[1] / 2 ); ?>"
	     alt="featured image"
	     class="aligncenter featured"/>
<?php endif; ?>

<div class="padded">
<div id="the_content">
	<?php $excerpt_only ? the_excerpt() : the_content(); ?>
	<p id="button"><a href="<?php the_permalink(); ?>"
	                  class="btn-secondary"><?php _e( 'View this post online', 'Postmatic' ); ?></a></p>
</div>

<?php if ( $will_strip_content ) : ?><hr /><?php endif; ?>

<?php if ( comments_open() and ! $excerpt_only ) : ?>

	<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;"/>
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
	</div>

<?php elseif ( !comments_open() ) : ?>

	<h4><?php _e( 'Comments on this post are closed', 'Postmatic' ); ?> </h4>

	<p><?php _e( 'You can reply to this email to send a note directly to the post author.', 'Postmatic' ); ?></p>

<?php endif; ?>


