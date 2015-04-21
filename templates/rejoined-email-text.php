<?php
/**
 * Template variables in scope:
 * @var WP_User               $subscriber
 * @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
 * @var array                 $comments      Comments since flood control
 */
?>
<?php echo Prompt_Html_To_Markdown::h1( sprintf( __( 'Welcome back, %s.', 'Postmatic' ), $subscriber->display_name ) ); ?>

<?php echo strip_tags( $object->subscription_description() ); ?>


<?php if ( $comments ) : ?>
------

<?php _e( 'Here\'s a recap of the conversation. You\'ll see a marker below showing you what\'s new. Reply to add your thoughts.', 'Postmatic' ); ?>


<?php
wp_list_comments( array(
	'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
	'end-callback' => '__return_empty_string',
	'style' => 'div',
), $comments );
?>


<?php _e( 'View this conversation online', 'Postmatic' ); ?>
<?php echo get_the_permalink( $object->id() ); ?>#comments


<?php _e( 'To leave a comment simply reply to this email.', 'Postmatic' ); ?>


<?php
printf(
	__(
		'Please note: Your reply will be published publicly and immediately on %s.',
		'Postmatic'
	),
	get_bloginfo( 'name' )
);
?>

<?php endif; ?>


<?php printf( __( 'To unsubscribe at any time visit %s', 'Postmatic' ), $object->subscription_url() ); ?>
