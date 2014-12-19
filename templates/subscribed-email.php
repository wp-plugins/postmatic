<?php
/**
* Template variables in scope:
* @var WP_User               $subscriber
* @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
* @var WP_Post               $latest_post   For site and author subscriptions, the latest relevant post.
*/
?>
<h1>
	<?php printf( __( 'Welcome, <span class="capitalize">%s</span>.', 'Postmatic' ), $subscriber->display_name ); ?>
</h1>
<p><?php echo $object->subscription_description(); ?></p>
<h3><?php _e( "What's next?", 'Postmatic' ); ?></h3>
<p>
	<?php
	printf( __( 'Keep an eye on your inbox for content from %s.', 'Postmatic' ), $object->subscription_object_label() );
	?>

	<?php
	if ( $latest_post ) :
		/* translators: %1$s is title, %2$s date */
		printf(
			__( 'We\'ve included the latest, <em>%1$s</em> from %2$s, below.', 'Postmatic' ),
			the_title(),
			the_date()
		);
	endif;
	?>

</p>

<?php if ( $latest_post ) : ?>
	<hr />
	<h2><?php the_title(); ?><br /><small><?php the_date(); ?></small></h2>
	<div>
		<?php the_content(); ?>
		<p class="aligncenter">
			<a href="<?php the_permalink(); ?>" class="btn-secondary"><?php _e( 'View this post online', 'Postmatic' ); ?></a>
		</p>
	</div>

	<ul>
		<li><?php _e( 'To <strong>leave a comment</strong> simply reply to this email.', 'Postmatic' ); ?></li>
		<li>
			<?php
			printf(
				__(
					'<strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
					'Postmatic'
				),
				get_bloginfo( 'name' )
			);
			?>
		</li>
	</ul>
<?php endif; ?>

<p>
	<?php printf( __( 'To unsubscribe at any time visit %s', 'Postmatic' ), $object->subscription_url() ); ?>
</p>