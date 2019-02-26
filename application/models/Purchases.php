<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases extends CI_Model {

	// Returns licenses for display - userId null returns ALL licenses
	public function get_user_product_purchases($userId = null) {
		$r = $this->Apcu->get('get_user_product_purchases-'.$userId);
		if ($r == null) {
			// get data
			$this->db->select("purchases.id as purchase_id");
			$this->db->select("DATE_FORMAT(purchases.time_purchased, '%Y-%m-%d') as time_purchased");
			$this->db->select("purchases.price_paid as price_paid");
			$this->db->select("purchases.purchase_type as purchase_type");
			$this->db->select("products.name as product_name");
			$this->db->select("products.description as product_description");
			$this->db->from("purchases");
			$this->db->join("products", 'products.id = purchases.product');
			$this->db->where('purchases.is_complete', true);
			if ($userId != null)
				$this->db->where("purchases.user", $userId);
			$r = $this->db->get()->result();

			// Only cache if it's not an admin request for the whole list
			if ($userId != null)
				$this->Apcu->set('get_user_product_purchases-'.$userId, $r);
		}
		return $r;
	}

	// Creates a new, incomplete purchase
	// Validation should happen in controller
	public function create_purchase($userId, $productId, $purchase_type = 'BUY', $payment_method = 'FREE', $payment_reference = null) {
		// Determine price paid based on current product price
		$price_paid = $this->db->get_where('products', array('id' => $productId))->row()->price;

		// Create row in purchases table
		$purchasesData = array(
			'user' => $userId,
			'product' => $productId,
			'price_paid' => $price_paid,
			'purchase_type' => $purchase_type,
			'payment_method' => $payment_method,
			'payment_reference' => $payment_reference
		);
		if ($payment_method == 'FREE')
			$purchasesData['is_complete'] = true;
		$this->db->insert('purchases', $purchasesData);
		// todo: log this

		// Find the purchase we created
		$this->db->select('*');
		$this->db->from('purchases');
		$this->db->where('user', $userId);
		$this->db->where('product', $productId);
		$this->db->like('price_paid', $price_paid); // floats need like
		$this->db->where('purchase_type', $purchase_type);
		$this->db->order_by('id', 'desc');
		$result['purchase'] = $this->db->get()->row();
		$purchaseId = $result['purchase']->id;

		// Create row in purchase_tokens table if needed
		if ($payment_method != 'FREE') {
			// Insert the row
			$twoCharPrefix = $payment_method == 'PAYPAL' ? 'PP' : 'UK'; // paypal or unknown for now
			$complete_token = $this->generate_purchase_token($twoCharPrefix);
			$cancel_token = $this->generate_purchase_token($twoCharPrefix);
			$purchaseTokensData = array(
				'purchase' => $purchaseId,
				'complete_token' => $complete_token,
				'cancel_token' => $cancel_token,
			);
			$this->db->insert('purchase_tokens', $purchaseTokensData);
			// todo: log this

			// Find the tokens we inserted
			$this->db->select('*');
			$this->db->from('purchase_tokens');
			$this->db->where('purchase', $purchaseId);
			$result['purchase_tokens'] = $this->db->get()->row();
		}

		return $result;
	}

	// generates a purchase token to ID the callback
	// two-char-prefix indicates the payment method (eg. PP)
	public function generate_purchase_token($twoCharPrefix) {
		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$length = 30; // 32 but two char prefix
		do {
			$token = $twoCharPrefix;
			for ($i = 0; $i < $length; $i++) {
				$token .= $tokens[rand(0, 35)];
			}

			// Check if token already exists
			$this->db->select('*');
			$this->db->from('purchase_tokens');
			$this->db->where('complete_token', $token);
			$this->db->or_where('cancel_token', $token);
			$foundTokens = $this->db->get()->num_rows();
		} while ($foundTokens > 0); // try again if token already exists somehow
		return $token;
	}

	// using the purchase_tokens table - find a purchases row
	// $token - input token
	// $token_type - must be 'complete_token' or 'cancel_token'
	public function find_incomplete_purchase_by_token($token, $token_type) {
		// Grab the purchase_tokens row
		$ptr = $this->db->get_where('purchase_tokens', array($token_type => $token))->row();
		if ($ptr == null)
			return null; // invalid token, do nothing

		// Grab the purchases row
		$where = array(
			'id' => $ptr->purchase,
			'is_complete' => false,
		);
		$pur = $this->db->get_where('purchases', $where)->row();
		if ($pur == null) { // this really shouldn't happen
			// todo: log this
			show_error("Purchase for the given token does not exist. Please contact support with this error.", 500);
			return null;
		}
		else return $pur;
	}

	// should be called when purchase is complete or cancelled
	// setting $is_complete = false will also remove the purchase from purchases table (indicates cancelled)
	public function finish_purchase($token, $is_complete) {
		// load models
		$this->load->model('Licenses');

		// get the purchase
		$tokenType = $is_complete ? 'complete_token' : 'cancel_token';
		$purchase = $this->find_incomplete_purchase_by_token($token, $tokenType);

		if ($purchase == null) { // invalid token entered
			show_404();
			return;
		}

		// remove token
		$this->db->delete('purchase_tokens', array($tokenType => $token));

		if ($is_complete) {
			// generate license key or change it's properties
			$new_purchase_type = $this->Licenses->set_user_license(
				$purchase->user, $purchase->product, $purchase->purchase_type);

			// Mark the order as complete
			$this->db->set('is_complete', true);
			$this->db->where('id', $purchase->id);
			$this->db->update('purchases');

			// handle changed order type
			if ($new_purchase_type != $purchase->purchase_type) {
				// Update database with new purchase type
				$this->db->set('purchase_type', $new_purchase_type);
				$this->db->where('id', $purchase->id);
				$this->db->update('purchases');

				//todo: log this
			}
			// todo: log purchase complete
		}
		else { // if !$is_complete (order cancelled)
			// remove purchase if cancelled
			$this->db->delete('purchases', array('id' => $purchase->id));
			// todo: log this
		}

		// todo: Send email with license

		// Clear user cache (for up-to-date purchases to delete)
		$this->Apcu->delete('get_user_product_purchases-'.$purchase->user);

		// Clear user's unpaid purchases & tokens
		$this->db->select('id');
		$this->db->from('purchases');
		$this->db->where('user', $purchase->user);
		$this->db->where('is_complete', false);
		$remainingUnpaidPurchases = $this->db->get()->result();
		foreach ($remainingUnpaidPurchases as $purToDel) {
			// Delete from purchases
			$this->db->delete('purchases', array('id' => $purToDel->id));

			// Delete purchase tokens
			$this->db->delete('purchase_tokens', array('purchase' => $purToDel->id));
		}

		// Clear user's purchase cache again (with unpaid purchases gone)
		$this->Apcu->delete('get_user_product_purchases-'.$purchase->user);

		// Redirect the user to the appropriate page
		switch ($tokenType) {
			// Redirect back to product
			case 'cancel':
				$this->load->model('Products');
				$pInfo = $this->Products->product_and_group_by_id();
				return '/store/' . $pInfo['product_group']->short_name;
			// Redirect to user panel
			case 'complete':
				return '/user';
				break;
		}
	}
}
