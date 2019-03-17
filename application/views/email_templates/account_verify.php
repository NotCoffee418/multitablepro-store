<?php
	$verify_url = site_url('/user/verify_account/'.$verification_token);
?>
<h1>Welcome, <?php echo $first_name; ?>!</h1>
<p>Please confirm your email address to finish creating your account.</p>
<p><a href="<?php echo $verify_url ?>">Click here to verify your email address</a></p>
<p>Alternatively, you can copy the following url into your browser: <br>
	<?php echo $verify_url ?></p>
<p>If you did not register an account, you can simply ignore this message.</p>
