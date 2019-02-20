<?php
// Admin view protection
if (!isset($has_permission) || !$has_permission)
	exit('Wutcha doin\' there buddy? Trying to access the admin views?');
?>
<div class="mt-5 pt-5">
	<div class="col-6">
		<div class="card">
			<div class="card-header">
				APCu
			</div>
			<div class="card-body">
				<?php if (isset($apcu_cache_info)) : ?>
				<label>Memory Type:</label> <?php echo $apcu_cache_info["memory_type"]; ?><br>
				<label>Start Time:</label> <?php echo gmdate("Y-m-d H:i:s", $apcu_cache_info["start_time"]); ?><br>
				<label>Segments:</label> <?php echo $apcu_sma_info["num_seg"] ?><br>
				<label>Segments Size:</label> <?php echo $apcu_sma_info["seg_size"] >> 10; ?> kb<br>
				<label>Available Memory:</label> <?php echo $apcu_sma_info["avail_mem"] >> 10; ?> kb<br>
				<a class="btn btn-primary" href="/admin/wipe_apcu_cache" role="button">Wipe APCu Cache (affects all sites)</a>
				<?php else: ?>
				APCu is disabled in config-extra.php
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>