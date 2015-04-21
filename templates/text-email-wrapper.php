<?php
/**
 * Text email template
 *
 * @var string $brand_text
 * @var string $message
 * @var string $footer_text
 * @var string $unsubscribe_url
 */
?>

··· <?php echo $brand_text; ?> ···

<?php echo $message; ?>

<?php echo $footer_text; ?>


<?php if ( ! empty( $unsubscribe_url ) ) : ?>
<?php
printf(
	__( 'To stop receiving email from %s visit:', 'Postmatic' ),
	get_bloginfo()
);
?>

<?php echo $unsubscribe_url; ?>
<?php endif; ?>

<?php _e( 'Delivered by Postmatic.', 'Postmatic' ); ?>

http://gopostmatic.com



