<?php
/*
* Template variables in scope:
* WP_User  $user       The new user
* string   $password   The new user plaintext password
*/
?>
<h1>Welcome to <?php echo get_option( 'blogname' ); ?>.</h1>
<p>It's not required, but you can access some extra features on our site by <a href="<?php echo wp_login_url(); ?>">logging in</a>.</p>
<h2>Your Account Information:</h2>
<p>
<strong>Username</strong>: <?php echo stripslashes( $user->user_login ); ?><br />
<strong>Password</strong>: <?php echo $password; ?>
</p>
<p>You may log in by visiting <?php echo wp_login_url(); ?>.</p>