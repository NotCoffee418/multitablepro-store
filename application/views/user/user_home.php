
<main role="main" class="container">
<div class="mt-5 pt-5">
	<h2>My Licenses</h2>
	<?php if (count($productLicenses) == 0): ?>
	<p class="text-justify">You have no active licenses.</p>
	<?php else: ?>
	<div class="table-responsive">
		<table class="table">
			<thead>
				<tr>
					<td>Product</td>
					<td>License Key</td>
					<td>Expires at</td>
					<td>Options</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($productLicenses as $prLic): ?>
				<tr>
					<td>
						<?php echo $prLic->product_name; ?>
						<small class="form-text text-muted"><?php echo $prLic->product_description; ?></small>
					</td>
					<td>
						<div class="input-group">
							<input type="text" id="license-key-<?php echo $prLic->license_id; ?>" class="form-control" readonly value="<?php echo $prLic->license_key; ?>">
							<span class="input-group-append">
								<button class="btn btn-outline-secondary" type="button" onclick="copyInputToClipboard('license-key-<?php echo $prLic->license_id; ?>');">
									<i class="fa fa-clipboard fa-lg"></i>
								</button>
							</span>
						</div>
					</td>
					<td><?php echo $prLic->expires_at == null ? "Never" : $prLic->expires_at; ?></td>
					<td>
						<a class="btn btn-success" href="/store/license-action/renew/<?php echo $prLic->license_id; ?>" role="button">Renew</a>
						<a class="btn btn-success" href="/store/license-action/upgrade/<?php echo $prLic->license_id; ?>" role="button">Upgrade</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<h2>Purchase History</h2>
	<?php if (count($purchaseHistory) == 0): ?>
		<p class="text-justify">You have no purchase history.</p>
	<?php else: ?>
		<div class="table-responsive">
			<table class="table">
				<thead>
				<tr>
					<td>Product</td>
					<td>Price</td>
					<td>Date</td>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($purchaseHistory as $purch): ?>
					<tr>
						<td><?php
							if ($purch->purchase_type == "UPGRADE")
								echo "Upgrade to ";
							else if ($purch->purchase_type == "RENEW")
								echo "Renew ";
							echo $purch->product_name;
							?>
						</td>
						<td><?php echo '$'.number_format($purch->price_paid, 2); ?></td>
						<td><?php echo $purch->time_purchased; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
