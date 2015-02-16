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

<div class="padded">
	<?php if ( !empty( $invite_introduction  ) ) : ?>
	<h3><?php printf( __( 'An invitation from %s', 'Postmatic' ), get_bloginfo( 'name' ) ); ?></h3>
	<p><?php echo $invite_introduction; ?></p>
<?php else : ?>
	<h4 class="alert">
		<strong>
			<?php
			printf(
				__( "There’s one more step to finish your subscription to %s.", 'Postmatic' ),
				get_bloginfo( 'name' )
			);
			?>
		</strong>
	</h4>
<?php endif; ?>

<?php if ( $resending ) : ?>
	<h2><?php _e( 'Important Notice', 'Postmatic' ); ?></h2>
	<p>
		<?php
		printf(
			__(
				'You recently signed up for updates from %s. We sent you an email asking for verification but you did not reply correctly. Please read the following:',
				'Postmatic'
			),
			get_bloginfo( 'name' )
		);
		?>
	</p>
<?php endif; ?>


<h1><?php _e( 'First, there are some important things you should know', 'Postmatic' ); ?></h1>
<ol>
	<li>
		<?php
		printf(
			__(
				"As a subscriber to %s, you'll receive new posts or comments directly to your inbox as soon as they are published.",
				'Postmatic'
			),
			get_bloginfo( 'name' )
		);
		?>
	</li>
	<li><?php _e( 'You will be invited to reply to these emails.', 'Postmatic' ); ?></li>
	<li>
		<strong>
			<?php _e( 'Your reply to those emails will be immediately published to the web as a comment.', 'Postmatic' ); ?>
		</strong>
	</li>
</ol>
<p>
	<?php
	printf(
		__(
			"Please note that your email replies will be seen by anyone visiting the post on %s. That means your email replies are subject to the privacy policy and terms of service of %s. We just wanted to make sure you knew :)",
			'Postmatic'
		),
		get_bloginfo( 'url' ),
		get_bloginfo( 'name' )
	);
	?>
</p>
<h2><?php _e( "Now it’s time to confirm your subscription", 'Postmatic' ); ?></h2>
<p>
	<?php
	_e(
		'If you understand these guidelines and you’re ready to complete your subscription, please <strong class="alert">reply to this email with the word agree</strong>.',
		'Postmatic'
	);
	?>
</p>
</div>

<div class="padded gray">
	
<p>
	<?php
	_e(
		'If you did not initiate this subscription please ignore this email or forward it to abuse@gopostmatic.com.',
		'Postmatic'
	);
	?>
</p>

<p><?php _e( 'Thanks!', 'Postmatic' ); ?></p>
</div>