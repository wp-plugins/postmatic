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
	<?php if ( !empty( $invite_introduction ) ) : ?>
		<h3><?php printf( __( 'An invitation from %s', 'Postmatic' ), get_bloginfo( 'name' ) ); ?></h3>
		<p><?php echo $invite_introduction; ?></p>
	<?php else : ?>
		<h4>
			<strong>
				<?php
				printf(
					__( "There’s one more step to finish your subscription to %s.", 'Postmatic' ),
					$object->subscription_object_label()
				);
				?>
			</strong>
		</h4>
	<?php endif; ?>

	<?php if ( $resending ) : ?>
		<h3><?php _e( 'Important Notice', 'Postmatic' ); ?></h3>
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


	<p>
		<?php
		_e(
			'<span class="alert">To confirm your subscription, please reply to this email with the word <strong>agree</strong></span>.',
			'Postmatic'
		);
		?>
	</p>
</div>

<div class="padded gray">

	<p class="abuse">
		<?php
		printf(
			__(
				'If you did not initiate this subscription please ignore this email or forward it to %s.',
				'Postmatic'
			),
			Prompt_Core::ABUSE_EMAIL
		)
		?>
	</p>

</div>