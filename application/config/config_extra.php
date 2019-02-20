<?php
/*
|--------------------------------------------------------------------------
| ReCaptcha public & private key
|--------------------------------------------------------------------------
*/
$config['recaptcha_public'] = "6Ld2XJIUAAAAAOpWKXYcZFDY5xb2Gz5bIeP96Kw9";
$config['recaptcha_private'] = "6Ld2XJIUAAAAAHy38ODrfsnqfBJmmaRrWdDtRamm";

/*
|--------------------------------------------------------------------------
| APCu Config
|--------------------------------------------------------------------------
*/
$config['apcu_enabled'] = true; // fast wipe in /admin
$config['apcu_site_prefix'] = "mtp_";
$config['apcu_default_ttl'] = 3600; // seconds
