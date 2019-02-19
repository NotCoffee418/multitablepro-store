<div class="container">
	<h4>Log in</h4>
	<?php
	// prepare known data & style
	$bootstrapInputClasses = "form-control";

	// Display errors
	if (validation_errors() != null)
		echo '<div class="alert alert-danger">' . validation_errors() . '</div>';

	// create our form
	echo form_open('user/login', array('id' => 'modal-login-form'));
	?>
	<div class="form-group">
		<label for="email">Email address</label>
		<?php

		// email
		$data = array(
			'class' => $bootstrapInputClasses,
			'name' => 'email',
			'type' => 'email',
			'placeholder' => 'Email address',
			'maxlength' => 128,
			'value' => set_value('email'),
		);
		echo form_input($data);

		?>
	</div>
	<div class="form-group">
		<label for="password">Password</label>
		<?php

		// Password
		$data = array(
			'class' => $bootstrapInputClasses,
			'name' => 'password',
			'placeholder' => 'Password',
			'maxlength' => 64,
		);
		echo form_password($data);

		?>
	</div>
	<input type='hidden' id='redirect' name='redirect' />
	<script>
		// Return to this page when logged in
		$('#redirect').val(window.location.href);
	</script>
	<?php
		echo $recaptcha_html;

		// Submit button
		$data = array(
			'id' => 'submit',
			'class' => 'btn btn-lg btn-primary btn-block',
			'disabled' => 'disabled', // renabled when captcha gets a reply
		);
		echo form_submit('submit', 'Log in', $data);
		echo form_close();
	?>

	<hr class="divider">
	<h4>Don't have an account yet?</h4>
	<a type="button" class="btn btn-success" href="/user/register">Create an account</a>
</div>
