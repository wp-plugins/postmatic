<?php
/**
* Template variables in scope:
* @var WP_User               $subscriber
* @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
* @var WP_Post               $latest_post   For site and author subscriptions, the latest relevant post
* @var array                 $comments      Comments so far for post subscriptions
*/
?>
<?php echo Prompt_Html_To_Markdown::h1( sprintf( __( 'Welcome, %s.', 'Postmatic' ), $subscriber->display_name ) ); ?>

<?php echo strip_tags( $object->subscription_description() ); ?>


<?php echo Prompt_Html_To_Markdown::h2( __( "What's next?", 'Postmatic' ) ); ?>

<?php
printf(
	__( 'Keep an eye on your inbox for content from %s.', 'Postmatic' ),
	strip_tags( $object->subscription_object_label() )
);
?>

<?php
if ( $latest_post ) :
	/* translators: %1$s is title, %2$s date */
	printf(
		__( 'We\'ve included the latest, %1$s from %2$s, below.', 'Postmatic' ),
		get_the_title(),
		get_the_date()
	);
elseif ( $comments ) :
	_e( 'The conversation so far is included below.', 'Postmatic' );
endif;
?> 


<?php if ( $latest_post ) : ?>
------

<?php echo Prompt_Html_To_Markdown::h1( get_the_title() . ' :: ' . get_the_date() ); ?>

<?php echo Prompt_Html_To_Markdown::convert( get_the_content() ); ?>


<?php _e( 'View this post online', 'Postmatic' ); ?> at <?php the_permalink(); ?>

<?php elseif ( $comments ) : ?>

<?php echo Prompt_Html_To_Markdown::h2( __( "Here is the discussion so far", 'Postmatic' ) ); ?>

<?php
wp_list_comments( array(
	'callback' => array( 'Prompt_Email_Comment_Rendering', 'render_text' ),
	'end-callback' => '__return_empty_string',
	'style' => 'div',
), $comments );
?>

<?php endif; ?>

<?php if ( $latest_post or $comments ) : ?>

<?php _e( '* To leave a comment simply reply to this email. *', 'Postmatic' ); ?>

<?php
printf(
	__(
		'Please note: Your reply will be published on %s.',
		'Postmatic'
	),
	get_bloginfo( 'name' )
);
?>
<?php endif; ?>

