<main role="main" class="container mt-5 pt-5">
	<div class="text-center">
		<?php
		$lowerCaseBranch = strtolower($versionInfo["requested_version"]->branch);
		$downloadLink = "/download/{$versionInfo["requested_version"]->product_group_short_name}/".
			"$lowerCaseBranch/{$versionInfo["requested_version"]->version}/setup"
		?>
		<h2 class="text-center">Download <?php echo $versionInfo["requested_version"]->product_group_full_name . $branchDisplay; ?></h2>
		<img class="img-fluid m-3" src="/img/product-group-preview/<?php echo $versionInfo["requested_version"]->product_group_short_name; ?>.webp"
			 alt="<?php echo $versionInfo["requested_version"]->product_group_full_name; ?> software preview"><br>
		<a role="button" class="btn btn-lg btn-success" href="<?php echo $downloadLink; ?>">Download Latest Version</a>
	</div>

	<h3 class="mt-5">Latest Version <?php echo "v{$versionInfo["requested_version"]->version}".$branchDisplay; ?></h3>
	<div class="card">
		<div class="card-body">
			<h4 class="card-title">v<?php echo $versionInfo["requested_version"]->version . $branchDisplay; ?></h4>
			<p class="card-text">
				<strong>Release date: </strong><?php echo date('Y-m-d',strtotime($versionInfo["requested_version"]->release_date)); ?><br>
				<strong>Changes:</strong><br>
				<?php echo nl2br($versionInfo["requested_version"]->changelog); ?>
			</p>
			<a href="<?php echo $downloadLink ?>" class="card-link">Download v<?php echo $versionInfo["requested_version"]->version; ?></a>
		</div>
	</div>



<?php if (count($versionInfo["older_versions"]) > 0): ?>
	<h3 class="mt-5">Older Versions</h3>
	<?php foreach ($versionInfo["older_versions"] as $versionData):
		$vDownloadLink = "/download/{$versionInfo["requested_version"]->product_group_short_name}/".
			"$lowerCaseBranch/{$versionData->version}/setup"
		?>

	<div class="card">
		<div class="card-body">
			<h4 class="card-title">v<?php echo $versionData->version . $branchDisplay; ?></h4>
			<p class="card-text">
				<strong>Release date: </strong><?php echo date('Y-m-d',strtotime($versionData->release_date)); ?><br>
				<strong>Changes:</strong><br>
				<?php echo nl2br($versionData->changelog); ?>
			</p>
			<a href="<?php echo $vDownloadLink ?>" class="card-link">Download v<?php echo $versionData->version; ?></a>
		</div>
	</div>
	<?php
	endforeach;
endif;
?>
</main>
