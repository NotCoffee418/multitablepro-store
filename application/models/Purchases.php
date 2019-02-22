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
		$price_paid = ($this->db->get_where('products', array('id' => $productId))->result())[0];

		// Insert to database
		$data = array(
			'user' => $userId,
			'product' => $productId,
			'price_paid' => $price_paid,
			'purchase_type' => $purchase_type,
			'payment_method' => $payment_method,
			'payment_reference' => $payment_reference
		);
		$this->db->insert('purchases', $data);

		// todo: Send email relative to $purchase_type
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
		} while (count($this->db->get_where('purchase_tokens', array('token' => $token))) > 0); // try again if token already exists somehow
		return $token;
	}

	// using the purchase_tokens table - find a purchases row
	// $token - input token
	// $token_type - must be 'cancel' or 'complete'
	public function find_incomplete_purchase_by_token($token) {
		// Grab the purchase_tokens row
		$ptr = $this->db->get_where('purchase_tokens', array('token' => $token))->result();
		if (count($ptr) == 0)
			return null; // invalid token, do nothing

		// Grab the purchases row
		$data = array(
			'id' => $ptr[0]->purchase,
			'is_complete' => false,
		);
		$r = $this->db->get_where('purchases', $data);
		if (count($r) == 0) { // this really shouldn't happen
			// todo: log this
			show_error("Purchase for the given token does not exist. Please contact support with this error.", 500);
			return null;
		}
		else return $r[0];
	}

	// should be called when purchase is complete or cancelled
	// setting $is_complete = false will also remove the purchase from purchases table (indicates cancelled)
	public function finish_purchase($token, $is_complete) {
		// load models
		$this->load->model('Licenses');

		// get the purchase
		$purchase = $this->find_incomplete_purchase_by_token($token);

		// remove token
		$this->db->delete('purchase_tokens', array('token' => $token));

		if ($is_complete) {
			// generate license key or change it's properties
			$new_purchase_type = $this->Licenses->set_user_license(
				$purchase->user, $purchase->product, $purchase->purchase_type);
			if ($new_purchase_type != $purchase->purchase_type) {
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

		// Clear user's purchase cache
		$this->Apcu->delete('get_user_product_purchases-'.$purchase->user);
	}
}
