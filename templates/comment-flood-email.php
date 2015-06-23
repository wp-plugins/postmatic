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
			__( 'Heads up: the conversation around %s is heating up.', 'Postmatic' ),
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
			'You love email -- but maybe not this much. We\'re going to pause notifications for you to prevent a flood in your inbox.',
			'Postmatic'
		);
		?>
	</p>

	<p>
		<?php
		_e(
			'You won\'t receive new comments on this post, unless you reply to this email with the word \'rejoin\'. We\'ll send you a recap and renew your subscription.',
			'Postmatic'
		);
		?>
	</p>

	<p id="button">
		<a href="<?php get_permalink( $post->id() ); ?>"
	       class="btn-secondary"><?php _e( 'Continue the conversation online', 'Postmatic' ); ?></a>
	</p>
</div>