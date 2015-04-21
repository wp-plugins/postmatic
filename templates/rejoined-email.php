<?php
/**
 * Template variables in scope:
 * @var WP_User               $subscriber
 * @var Prompt_Interface_Subscribable   $object        The thing being subscribed to
 * @var array                 $comments      Comments since flood control
 */
?>
<div class="padded">
	<h3>
		<?php
		printf( __( 'Welcome back, <span class="capitalize">%s</span>.', 'Postmatic' ), $subscriber->display_name );
		?>
	</h3>



	<?php if ( $comments ) : ?>
		<h4><?php _e( 'Here\'s a recap of the conversation.', 'Postmatic' ); ?></h4>

		<div class="previous-comments rejoin">
			<?php
			wp_list_comments( array(
				'callback' => array( 'Prompt_Email_Comment_Rendering', 'render' ),
				'style' => 'div',
			), $comments );
			?>
		</div>

		<p id="button"><a href="<?php echo get_the_permalink( $object->id() ); ?>#comments" class="btn-secondary">
				<?php _e( 'View this conversation online', 'Postmatic' ); ?></a>
		</p>

		<div class="reply-prompt">
			<img src="<?php echo Prompt_Core::$url_path . '/media/reply-comment-2x.png' ;?>" width="30" height="30" />
			<p class="reply">
				<?php
				_e( 'Reply to this email to add a comment. Your email address will not be shown.', 'Postmatic' );
				?><br />
				<small>
					<?php
					printf(
						__(
							'<strong>Please note</strong>: Your reply will be published publicly and immediately on %s.',
							'Postmatic'
						),
						get_bloginfo( 'name' )
					);
					?>
				</small>
			</p>
		</div>
	<?php endif; ?>

	<p>
		<?php
		printf(
			__(
				'To unsubscribe at any time visit %s or reply with the word <strong>unsubscribe</strong>.',
				'Postmatic'
			),
			$object->subscription_url()
		);
		?>
	</p>

</div>