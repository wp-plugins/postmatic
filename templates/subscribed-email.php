<?php
/**
* Template variables in scope:
* @var WP_User               $subscriber
* @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
* @var WP_Post               $latest_post   For site and author subscriptions, the latest relevant post.
*/
?>
<h1>Welcome, <span class="capitalize"><?php echo $subscriber->display_name; ?></span>.</h1>
<p><?php echo $object->subscription_description(); ?></p>
<h3>What's next?</h3>
<p>Keep an eye on your inbox for content from <?php echo $object->subscription_object_label(); ?>.

	<?php if ( $latest_post ) : ?>
		We've included the latest, <em><?php the_title(); ?></em> from <?php the_date(); ?>, below.
		Reply to this email to leave a comment!
	<?php endif; ?>

</p>

<?php if ( $latest_post ) : ?>
	<hr />
	<h2><?php the_title(); ?><br /><small><?php the_date(); ?></small></h2>
	<div>
		<?php the_content(); ?>
		<p class="aligncenter"><a href="<?php the_permalink(); ?>" class="btn-secondary">View this post online</a></p>
	</div>

	<ul>
		<li>To <strong>leave a comment</strong> simply reply to this email.</li>
		<li><strong>Please note</strong>: Your comment will be published publicly and immediately on <?php bloginfo( 'name' ); ?></li>
	</ul>
<?php endif; ?>

<p>To unsubscribe at any time visit <?php echo $object->subscription_url(); ?></p>