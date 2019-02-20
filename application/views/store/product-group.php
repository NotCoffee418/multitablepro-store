<div class="mt-5 pt-5">
	<div class="col-md-6">

		<div class="form-group">
		<?php
		// Determine which radiobutton to check
		$checkProduct = 'product_0'; // if no products are found somehow
		if (set_value('product') != null) // or ""
			$checkProduct = set_value('product');
		else if (count($products) > 0)
			$checkProduct = 'product_'.$products[0]->id;

		// Display all product RBs
		foreach ($products as $p):
			// Set inputData for RB
			$inputData = array(
				'class' => 'form-check-input',
				'name' => 'product',
				'id' => 'product_'.$p->id,
			);
			// Check the correct box
			if ($checkProduct == 'product_'.$p->id)
				$inputData['checked'] = 'checked';
		?>
			<!-- Default unchecked -->
			<div class="form-group radio">
				<label class="form-check-label" for="<?php echo 'product_'.$p->id; ?>">
					<?php echo form_radio($inputData); ?>
					<?php echo '$'.number_format($p->price, 2) . " - " .$p->name; ?>
					<small class="form-text text-muted"><?php echo $p->description; ?></small>
				</label>
			</div>
		<?php endforeach; ?>
		</div>
	</div>
</div>
