<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Model {

	public function product_group_by_shortname($shortName) {
		$pGroup = $this->Apcu->get("product_group_by_shortname-".$shortName);
		if ($pGroup == null) {
			$pGroup = $this->db->get_where("product_groups", array("short_name" => $shortName))->row();
			$this->Apcu->set("product_group_by_shortname-".$shortName, $pGroup);
		}
		return $pGroup;
	}

	public function product_and_group_by_id($productId) {
		$result = $this->Apcu->get("product_and_group_by_id-".$productId);
		if ($result == null) {
			// get product
			$result['product'] = $this->db->get_where('products', array('id' => $productId))->row();
			if ($result['product'] == null)
				return null;

			// Get product's group
			$result['product_group'] = $this->db->get_where("product_groups",
				array("id" => $result['product']->product_group))->row();

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
		return $this->db->get()->num_rows() == 0 ? false : true;
	}

	public function get_product_restrictions_array($productId) {
		$result = $this->Apcu->get("get_product_restrictions_array_$productId");
		if ($result == null) {
			$result = array();
			$row = $this->db->get_where("products", array('id' => $productId))->row();

			// Check for valid product
			if($row == null) {
				throw new Exception("Product for the license was not found. Contact support!"); // shouldnt happen unless manually messing with licenses
			}

			// Define $result
			foreach (explode(',', $row->restrictions) as $ruleStr) {
				$rule = explode(':', $ruleStr);
				$result[$rule[0]] = $rule[1];
			}

			// Cache it
			$this->Apcu->set("get_product_restrictions_array_$productId", $result);
		}
		return $result;
	}

	public function list_product_groups() {
		$this->db->select('*');
		$this->db->from('product_groups');
		return $this->db->get()->result();
	}
}
