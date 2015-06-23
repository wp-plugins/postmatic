<?php
/**
* Template variables in scope:
* @var WP_User               $subscriber
* @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
* @var WP_Post               $latest_post   For site and author subscriptions, the latest relevant post
* @var array                 $comments      Comments so far for post subscriptions
*/
?>
<div class="padded">
	<h3>
	<?php printf( __( 'Welcome, <span class="capitalize">%s</span>.', 'Postmatic' ), $subscriber->display_name ); ?>
</h3>
<p><?php echo $object->subscription_description(); ?></p>

	
	<h3>
	<?php
	if ( $latest_post ) :
		/* translators: %1$s is title, %2$s date */
		printf(
			__( 'The most recent post is below. Reply to this email to leave a comment.', 'Postmatic' ),
			get_the_title(),
			get_the_date()
		);
	elseif ( $comments ) :
		_e( 'Here is what others have to say. Reply to add your thoughts.', 'Postmatic' );
	endif;
	?>
</h3>

<?php if ( $latest_post ) : ?>
	<h2><?php the_title(); ?></h2>
	<div>
		<?php the_content(); ?>
		<p class="aligncenter">
			<a href="<?php the_permalink(); ?>" class="btn-secondary"><?php _e( 'View this post online', 'Postmatic' ); ?></a>
		</p>
	</div>

	<p id="button"><a href="<?php the_permalink(); ?>#comments" class="btn-secondary">
			<?php _e( 'View this conversation online', 'Postmatic' ); ?></a>
	</p>
<?php elseif ( $comments ) : ?>

	<h3><?php __( "Want to catch up? Here are the 30 most recent comments:", 'Postmatic' ); ?></h3>

	<div class="previous-comments">
		<?php
		wp_list_comments( array(
			'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
			'style' => 'div',
		), $comments );
		?>
	</div>

	<p id="button"><a href="<?php echo get_the_permalink( $object->id() ); ?>#comments" class="btn-secondary">
			<?php _e( 'View this conversation online', 'Postmatic' ); ?></a>
	</p>
<?php endif; ?>

<?php if ( $latest_post or $comments ) : ?>


		<div class="reply-prompt">
		<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" align="left" style="float: left; margin-right: 10px;" />
		<p class="reply">
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
		</p>
	</div>
<?php endif; ?>

<p>
	<?php printf( __( 'To unsubscribe at any time reply with the word <strong>unsubscribe</strong>.', 'Postmatic' ), $object->subscription_url() ); ?>
</p>

</div>