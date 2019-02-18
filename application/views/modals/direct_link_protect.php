<script>
	// check if jQuery and body exist - if not, redirect. Only occurs when user directly accesses modal url.
	try { if (!window.jQuery || $("body").length == 0) throw "Direct access to modal not allowed. Redirecting."; }
	catch (e) { window.location="<?php echo isset($redirect) ? $redirect : "/"; ?>"; }
</script>
