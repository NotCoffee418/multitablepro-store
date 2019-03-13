<?php echo form_open(current_url()) ?>

<main role="main" class="container">
	<div class="mt-5 pt-5 row">
			<?php if ($discount > 0): ?>
				<h1>Upgrade your license</h1>
				<div class="alert alert-warning">
					The value of the remaining time (<strong>$<?php echo $discount ?></strong>) is subtracted from the upgrade price and is reflected in the prices below.<br>
				</div>
			<div class="form-group">
				<?php
				// list upgradable products
				$dislayedCount = 0;
				$filteredProducts = array();
				foreach ($products as $p) {
					// Only show upgrades, not downgrades
					if ($p->price - $discount <= 0 || $p->id == $license->product)
						continue;
					else {
						$dislayedCount++;
						$filteredProducts[] = $p;
					}
				}

				// Determine which radiobutton to check
				$checkProduct = 'product_0'; // if no products are found somehow
				if (set_value('product') != null) // or ""
					$checkProduct = set_value('product');
				else if (count($products) > 0)
					$checkProduct = 'product_'.$filteredProducts[0]->id;

				// Display all product RBs
				foreach ($filteredProducts as $p):
					// Set inputData for RB
					$inputData = array(
						'class' => 'form-check-input',
						'name' => 'product',
						'id' => 'product_'.$p->id,
						'value' => 'product_'.$p->id,
						'data-price' => $p->price
					);
					// Check the correct box
					if ($checkProduct == 'product_'.$p->id)
						$inputData['checked'] = 'checked';
					?>
					<!-- Default unchecked -->
					<div class="form-group radio">
						<label class="form-check-label" for="<?php echo 'product_'.$p->id; ?>">
							<?php echo form_radio($inputData); ?>
							<?php echo '$'.number_format($p->price - $discount, 2) . " - " .$p->name; ?>
							<small class="form-text text-muted"><?php echo $p->description; ?></small>
						</label>
					</div>
				<?php endforeach; ?>




				<?php
				$data = array(
					'class' => 'btn btn-lg btn-success',
					'value' => 'Upgrade',
					'id' => 'buyBtn',
					'onclick' => 'disableElement(\'#buyBtn\')'
				);

				?>

				<?php if ($dislayedCount == 0): ?>
					<div class="alert alert-danger">
						This license cannot currently be upgraded. Contact support if you believe this to be in error.
					</div>
				<?php else:
					echo form_submit($data);
				endif; ?>
			</div>
			<?php else: ?>
			<div class="alert alert-danger">
				This product cannot be upgraded.
			</div>
			<?php endif; ?>
	</div>
</main><!-- /.container -->
<?php echo form_close(); ?>
