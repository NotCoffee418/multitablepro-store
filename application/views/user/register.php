
<main role="main" class="container">
	<div class="mt-5 pt-5">
		<h1>Register</h1>
		<?php
		// prepare known data & style
		$bootstrapInputClasses = "form-control";

		// Display errors
		if (validation_errors() != null)
		 echo '<div class="alert alert-danger">' . validation_errors() . '</div>';

		// create our form
		echo form_open('user/register');

		?>
		<div class="form-group">
		<label for="fname">First Name</label>
		<?php
		// first name
		$data = array(
			'class' => $bootstrapInputClasses,
			'name' => 'fname',
			'placeholder' => 'First name',
			'maxlength' => 64,
			'value' =>  set_value('fname'),
		);
		echo form_input($data);

		?>
		</div>
		<div class="form-group">
		<label for="lname">Last Name</label>
		<?php

		// last name
		$data = array(
			'class' => $bootstrapInputClasses,
			'name' => 'lname',
			'placeholder' => 'Last name',
			'maxlength' => 64,
			'value' => set_value('lname'),
		);
		echo form_input($data);

		?>
		</div>
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
		<small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
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
		<div class="form-group">
		<label for="passconf">Confirm Password</label>
		<?php

		// Confirm password
		$data = array(
			'class' => $bootstrapInputClasses,
			'name' => 'passconf',
			'placeholder' => 'Confirm password',
			'maxlength' => 64,
		);
		echo form_password($data);
		?>
		</div>
		<div class="custom-control custom-checkbox">
			<?php
			$data = array(
				'class' => 'custom-control-input',
				'name' => 'accepteula',
				'id' => 'accepteula',
			);
			echo form_checkbox($data);
			?>
			<label class="custom-control-label" for="accepteula">I have read and accept the <a href="/eula">EULA</a></label>
		</div>

		<?php

		// Captcha HTML
		echo $recaptcha;

		// Add redirect url
		if (isset($redirect_url))
			echo form_hidden('redirect_url', $redirect_url);
		else if (set_value('redirect_url') != '')
			echo form_hidden('redirect_url', set_value('redirect_url'));

		// Submit button
		$data = array(
			'id' => 'submit',
			'class' => 'btn btn-success mt-4',
			'disabled' => 'disabled', // renabled when captcha gets a reply
		);
		echo form_submit('submit', 'Create account', $data);
		echo form_close();
		?>
	</div>
</main>
