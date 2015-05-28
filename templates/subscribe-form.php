<?php
/**
 * Variables in scope:
 * @var string               $widget_id         The widget generating this form
 * @var array                $instance          The widget instance data
 * @var Prompt_Interface_Subscribable  $object         Object of subscription
 * @var object               $user              Logged in user or null
 * @var string               $action            'subscribe' or 'unsubscribe'
 * @var array                $defaults          Default form values
 * @var string               $loading_image_url
 * @var string               $unsubscribe_prompt
 */



?>
<form class="prompt-subscribe" method="post">

	<div class="loading-indicator" style="display: none; margin:3% 0;min-height:70px;">
		<img src="<?php echo $loading_image_url; ?>" alt="Loading..." />
	</div>

	<p class="message" style="display:none;"></p>

	<div class="inputs">
		<input id="<?php echo $widget_id; ?>-nonce" name="subscribe_nonce" type="hidden" />

		<input id="<?php echo $widget_id; ?>-action" name="action" type="hidden" value="<?php echo Prompt_Subscribing::SUBSCRIBE_ACTION; ?>" />

		<input id="<?php echo $widget_id; ?>-type" name="object_type" type="hidden" value="<?php echo get_class( $object ); ?>" />

		<input id="<?php echo $widget_id; ?>-object-id" name="object_id" type="hidden" value="<?php echo $object->id(); ?>" />

		<label class="prompt-topic" for="subscribe_topic">
			<?php _e( 'This field is intentionally empty', 'Postmatic' ); ?> *
			<input id="<?php echo $widget_id; ?>-topic" name="subscribe_topic" type="text" value="" />
		</label>

		<?php if ( 'unsubscribe' == $action ) : ?>
			<div class="unsubscribe prompt">
				<?php printf( __( 'You are already subscribed to %s.', 'Postmatic' ), $object->subscription_object_label() ); ?>
			</div>

			<input id="<?php echo $widget_id; ?>-confirm-unsubscribe" name="confirm_unsubscribe" type="hidden" value="1" />
		<?php endif; ?>

		<div class="subscribe primary prompt"></div>

		<div class="unsubscribe prompt"><?php echo $unsubscribe_prompt; ?></div>

		<?php if ( !is_user_logged_in() ) : ?>

			<?php if ( $instance['collect_name'] and 'unsubscribe' != $action ) : ?>
				<input id="<?php echo $widget_id; ?>-name"
					   name="subscribe_name"
					   type="text"
					   placeholder="<?php _e( 'Name (optional)', 'Postmatic' ); ?>"
					   value="<?php echo esc_attr( $defaults['subscribe_name'] ); ?>" />
			<?php endif; ?>

			<input id="<?php echo $widget_id; ?>-email"
			       name="subscribe_email"
			       type="text"
			       placeholder="<?php _e( 'Email', 'Postmatic' ); ?>"
			       value="<?php echo esc_attr( $defaults['subscribe_email'] ); ?>" />

		<?php endif; ?>

		<input id="<?php echo $widget_id; ?>-submit" name="subscribe_submit" class="submit" style="font-size: 90%; text-transform: capitalize;" type="submit" value="<?php echo $action; ?>" />

		<?php if ( !is_user_logged_in() and empty( $defaults['subscribe_email' ] ) ) : ?>
			<p class="subscribe prompt">
				<a class="show-unsubscribe" href="#<?php echo $widget_id; ?>-submit"><small><?php _e( 'Want to unsubscribe?', 'Postmatic' ); ?></small></a>
			</p>
			<p class="unsubscribe prompt">
				<a class="cancel" href="#<?php echo $widget_id; ?>-submit" ><small><?php _e( 'Cancel', 'Postmatic' ); ?></small></a>
			</p>
		<?php endif; ?>
	</div>

</form>
