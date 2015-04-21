<?php
/**
 * Text post notification email template
 *
 * Note that PHP swallows a newline with closing ?> tags, so two blank lines will be rendered as one.
 *
 * @see prompt/post_email/template_data
 *
 * @var Prompt_Interface_Subscribable   $subscribed_object
 * @var string                          $the_text_content
 * @var bool                            $excerpt_only
 */
?>
<?php echo Prompt_Html_To_Markdown::h1( get_the_title() ); ?>


<?php echo $the_text_content; ?>


<?php _e( 'View this post online', 'Postmatic' ); ?> at <?php the_permalink(); ?>

<?php if ( !comments_open() or $excerpt_only ) return; ?>

<?php _e( '* Reply to this email to add a comment. * ', 'Postmatic' ); ?>

<?php
printf(
	__(
		"Please note: Your reply will be published publicly and immediately on %s.",
		'Postmatic'
	),
	get_bloginfo( 'name' )
);
?>



<?php echo Prompt_Html_To_Markdown::h2( __( 'Stay in the Loop', 'Postmatic' ) ); ?>

<?php
_e(
	"To receive comments on this post directly in your inbox reply with the word * subscribe *.",
	'Postmatic'
);
?>


<?php echo Prompt_Html_To_Markdown::h2( __( 'Manage your subscription', 'Postmatic' ) ); ?>

<?php
printf(
	__( "To unsubscribe to %s reply with the word * unsubscribe *.", 'Postmatic' ),
	strip_tags( $subscribed_object->subscription_object_label() )
);
?>