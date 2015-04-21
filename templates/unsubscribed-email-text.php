<?php
/**
 * Template variables in scope:
 * @var WP_User  $subscriber
 * @var Prompt_Interface_Subscribable   $object         The thing being subscribed to
 */
?>
<?php Prompt_Html_To_Markdown::h1( __( 'You have unsubscribed', 'Postmatic' ) ); ?>

<?php
printf(
	__( "You'll no longer receive email notices for %s.", 'Postmatic' ),
	strip_tags( $object->subscription_object_label() )
);
?>


<?php _e( 'To re-subscribe visit:', 'Postmatic' ); ?> <?php echo $object->subscription_url(); ?>