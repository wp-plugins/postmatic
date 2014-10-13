<?php
/**
* comment rejected email template
* variables in scope:
* @var WP_User $comment_author
* @var WP_Post $post False if the post no longer exists.
* @var string $post_title Post title or placeholder if post no longer exists
*/
?>
<h1>We're sorry.</h1>
<p>
Your reply to <em><?php echo $post_title; ?> </em>cannot be published because the post cannot be found or the
discussion has been closed.</p>
<?php if ( $post ) : ?>
<p>
Please visit <a href="<?php echo get_permalink( $post ); ?>"><?php echo get_permalink( $post ); ?></a>
for more information.</p>
<a href="<?php echo get_permalink( $post ); ?>#comments" class="btn-primary">More Information</a>
<?php endif; ?>