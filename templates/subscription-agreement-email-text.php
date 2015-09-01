<?php
/**
 * @var string $email_address
 * @var Prompt_Interface_Subscribable $object
 * @var boolean $resending
 * @var array $user_data May be empty or contain any WP_User fields that were collected.
 * @var string $invite_introduction Only present for invite emails.
 */
$recipient = esc_html(
	!empty( $user_data['display_name'] ) ? $user_data['display_name'] : $email_address
);
?>
<?php if ( !empty( $invite_introduction  ) ) : ?>
<?php echo Prompt_Html_To_Markdown::h1( sprintf( __( 'An invitation from %s', 'Postmatic' ), get_bloginfo( 'name' ) ) ); ?>

<?php echo strip_tags( $invite_introduction ); ?>
<?php else : ?>
<?php
	echo Prompt_Html_To_Markdown::h1(
		sprintf(
			__( "There's one more step to finish your subscription to %s.", 'Postmatic' ),
			get_bloginfo( 'name' )
		)
	);
?>
<?php endif; ?>

<?php if ( $resending ) : ?>
<?php echo Prompt_Html_To_Markdown::h2( __( 'Important Notice', 'Postmatic' ) ); ?>

<?php
printf(
	__(
		'You recently signed up for updates from %s. We sent you an email asking for verification but you did not reply correctly. Please read the following:',
		'Postmatic'
	),
	get_bloginfo( 'name' )
);
?>


<?php endif; ?>

<?php
_e(
	'If you understand these guidelines and you\'re ready to complete your subscription, *please reply to this email with the word agree*.',
	'Postmatic'
);
?>


<?php
printf(
	__(
		'If you did not initiate this subscription please ignore this email or forward it to %s.',
		'Postmatic'
	),
	Prompt_Core::ABUSE_EMAIL
);
?>