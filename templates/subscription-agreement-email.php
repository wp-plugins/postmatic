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
	<h3>An invitation from <?php bloginfo( 'name' ); ?></h3>
<p><?php echo $invite_introduction; ?></p>
<?php else : ?>
<h4 class="alert"><strong>There’s one more step to finish your subscription to <?php bloginfo( 'name' ); ?>.</strong></h4>
<?php endif; ?>

<?php if ( $resending ) : ?>
<h2>Important Notice</h2>
<p>You recently signed up for updates from <?php bloginfo( 'name' ); ?>. We sent you an email asking for verification but you did not reply correctly. Please read the following:</p>
<?php endif; ?>

<h1>First, there are some important things you should know</h1>
<ol>
	<li>As a subscriber to <?php bloginfo( 'name' ); ?>, you'll receive new posts or comments directly to your inbox as soon as they are published.</li>
	<li>You will be invited to reply to these emails.</li>
	<li><strong>Your reply to those emails will be immediately published to the web as a comment.</strong></li>
</ol>
<p>Please note that your email replies will be seen by anyone visiting the post on <?php bloginfo( 'url' ); ?>. That means your email replies are subject to the privacy policy and terms of service of <?php bloginfo( 'name' ); ?>. We just wanted to make sure you knew :)</p>
<h2>Now it’s time to confirm your subscription</h2>
<p>If you understand these guidelines and you’re ready to complete your subscription, please <strong class="alert">reply to this email with the word agree</strong>. </p> If you did not initiate this subscription please ignore this email or forward it to abuse@gopostmatic.com.
<p>Thanks!</p>
