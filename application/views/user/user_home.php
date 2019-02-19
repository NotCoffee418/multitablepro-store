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
					<td><?php echo $prLic->product_name; ?></td>
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
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>
