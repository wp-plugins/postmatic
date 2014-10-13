<?php
/**
* HTML post notification email template
*
* Post globals are set so template tags like the_title() and the_content() will work.
*
* @see prompt/post_email/template_data
*/
?>
<h1><?php the_title(); ?></h1>
<?php if ( $featured_image_src ) : ?>
  <img src="<?php echo $featured_image_src[0]; ?>"
       width="<?php echo intval( $featured_image_src[1]/2 ); ?>"
       alt="featured image"
       class="aligncenter" />
<?php endif; ?>
<div>
  <?php the_content(); ?>
    <p class="aligncenter"><a href="<?php the_permalink(); ?>" class="btn-secondary">View this post online</a></p>
</div>

<div class="footnote">

  <h1>Leave a Comment</h1>
  <ul>
    <li>To <strong>leave a comment</strong> simply reply to this email.</li>
    <li><strong>Please note</strong>: Your comment will be published publicly and immediately on <?php bloginfo( 'name' ); ?></li>
  </ul>
  <h3>Stay in the Loop</h3>
  <ul>
    <li>To receive comments on this post directly in your inbox reply with the word <strong>subscribe</strong>.</li>
  </ul>
  <h4>Manage your subscription</h4>
  <ul>
    <li>To <strong>unsubscribe</strong> to <?php echo $subscribed_object->subscription_object_label(); ?> reply with the word 'unsubscribe'.</li>
  </ul>
</div>