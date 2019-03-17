<h1>Your License Key</h1>
<p>Thank you for purchasing <?php echo $product_name ?>!</p>
<p>
	<strong>Here is your license key:</strong><br>
	<?php echo $license_key; ?>
</p>
<p>
	This license key wil remain valid until <?php echo $expires_at == null ? 'forever' : $expires_at; ?>.
</p>
<h3>Instructions</h3>
<ol>
	<li>If you have not downloaded the software yet, you can do so by <a href="<?php echo site_url("/download/$short_name"); ?>">clicking here</a> and installing the software</li>
	<li>Paste the above license key into the application when asked.</li>
	<li>Start using the program!</li>
</ol>

<p>
	You may be able to find additional information to help you get started <a href="<?php echo site_url(); ?>">on our website</a>.<br>
	If you have any additional questions, please don't hesitate to contact us through our <a href="<?php echo site_url('/support'); ?>">support page</a>.
</p>
