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
  <p><a href="<?php the_permalink(); ?>" class="btn-secondary">View this post online</a></p>
</div>


<div class="reply-prompt"><img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" /> <p class="reply">Reply to this email to add a comment. <br /><small><strong>Please note</strong>: Your comment will be published publicly and immediately on <?php bloginfo( 'name' ); ?></small></p>



<div class="footnote">

  <h3>Stay in the Loop</h3>
    <p>To receive comments on this post directly in your inbox reply with the word <strong>subscribe</strong>.</p>

  <h4>Manage your subscription</h4>
    <p>To <strong>unsubscribe</strong> to <?php echo $subscribed_object->subscription_object_label(); ?> reply with the word 'unsubscribe'.</p>
</div>