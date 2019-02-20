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
			if ($userId != null)
				$this->db->where("purchases.user", $userId);
			$r = $this->db->get()->result();

			// Only cache if it's not an admin request for the whole list
			if ($userId != null)
				$this->Apcu->set('get_user_product_purchases-'.$userId, $r);
		}
		return $r;
	}

	public function create_purchase() {
		// Clear user's related cache
		//$this->Apcu->delete('get_user_product_purchases-'.$userId);
		//$this->Apcu->delete('get_user_product_licenses-'.$userId);
	}
}
