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
}
