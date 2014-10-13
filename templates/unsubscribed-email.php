<?php
/**
* Template variables in scope:
* @var WP_User  $subscriber
* @var Prompt_Interface_Subscribable   $object         The thing being subscribed to
*/
?>
<h1>You have unsubscribed</h1>
<p>You'll no longer receive email notices for <?php echo $object->subscription_object_label(); ?>.</p>
<p>To re-subscribe visit: <?php echo $object->subscription_url(); ?></p>