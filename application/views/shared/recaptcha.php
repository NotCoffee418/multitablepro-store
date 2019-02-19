<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
<input type="hidden" name="action" value="<?php echo $action; ?>">

<script src="https://www.google.com/recaptcha/api.js?render=<?php echo $recaptcha_public; ?>"></script>
<script>
	// get captcha token after async load
	whenAvailable("grecaptcha", function(t) {
		// Make captcha request
		grecaptcha.ready(function() {
			grecaptcha.execute('<?php echo $recaptcha_public; ?>', {action: '<?php echo $action; ?>'}).then(function(token) {
				<?php if (isset($specifySubId)): // Use to look for a captcha within a certain ID (eg. modal) ?>
					var sub = document.getElementById('<?php echo $specifySubId; ?>');
				<?php else: ?>
					var sub = document;
				<?php endif; ?>
				sub.querySelector('[id=g-recaptcha-response]').value = token;
				sub.querySelector('[id=submit]').removeAttribute('disabled'); // Enable buttons
			});
		});
	});

	whenAvailableCount = []; // If fail after 3 seconds, give up and let server explain that captcha is broken
	function whenAvailable(name, callback) {
		var interval = 100; // ms
		window.setTimeout(function() {
			if (window[name] || whenAvailableCount[name] > 30) {
				callback(window[name]);
			} else {
				whenAvailableCount[name]++;
				window.setTimeout(arguments.callee, interval);
			}
		}, interval);
	}
</script>
