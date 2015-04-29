<?php
/**
 * comment flood notification email
 *
 * @var {WP_User} $subscriber
 * @var Prompt_Post $post
 */
?>

<div class="padded">
	<h3>
		<?php
		printf(
			__( 'Heads up: the conversation around %s is getting out of hand.', 'Postmatic' ),
			html( 'a',
				array( 'href' => get_permalink( $post->id() ) ),
				get_the_title( $post->id() )
			)
		);
		?>
	</h3>

	<p>
		<?php
		_e(
			'You love email. But maybe not this much. We\'re going to shut things down from this point on to prevent a flood in your inbox. You will no longer receive new comments on this post.',
			'Postmatic'
		);
		?>
	</p>

	<p>
		<?php
		_e(
			'If you really do want to keep up with this thread, reply to this email with the word <strong>rejoin</strong>. We\'ll send you a recap and renew your subscription.',
			'Postmatic'
		);
		?>
	</p>

	<p id="button">
		<a href="<?php get_permalink( $post->id() ); ?>"
	       class="btn-secondary"><?php _e( 'Continue the conversation online', 'Postmatic' ); ?></a>
	</p>
</div>