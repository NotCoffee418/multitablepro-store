<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Model {

	public function product_group_by_shortname($shortName) {
		$pGroup = $this->Apcu->get("product_group_by_shortname-".$shortName);
		if ($pGroup == null) {
			$r = $this->db->get_where("product_groups", array("short_name" => $shortName))->result();
			$pGroup = count($r) > 0 ? $r[0] : null;
			$this->Apcu->set("product_group_by_shortname-".$shortName, $pGroup);
		}
		return $pGroup;
	}


	public function product_and_group_by_id($productId) {
		$result = $this->Apcu->get("product_and_group_by_id-".$productId);
		if ($result == null) {
			// get product
			$r = $this->db->get_where('products', array('id' => $productId))->result();
			if (count($r) == 0)
				return null;
			$result['product'] = $r[0];

			// Get product's group
			$result['product_group'] = ($this->db->get_where("product_groups",
				array("id" => $result['product']->product_group))->result())[0];

			// Set cache
			$this->Apcu->set("product_and_group_by_id-".$productId, $result);
		}
		return $result;
	}

	public function products_in_group($groupId) {
		$products = $this->Apcu->get("products_in_group-".$groupId);
		if ($products == null) {
			$data = array(
				"product_group" => $groupId,
				"is_public" => true,
			);
			$products = $this->db->get_where("products", $data)->result();
			$this->Apcu->set("products_in_group-".$groupId, $products);
		}
		return $products;
	}

	// Determine if the product is available for purchase (assume user can input any value!)
	public function purchasing_allowed($productId) {
		$this->db->select('*');
		$this->db->from('products');
		$this->db->where('id', $productId);
		$this->db->where('is_public', true);
		$r = $this->db->get()->result();
		return count($r) == 0 ? false : true;
	}
}
