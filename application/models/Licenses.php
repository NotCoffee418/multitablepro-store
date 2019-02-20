<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Licenses extends CI_Model {

	// $product prefix must be 5 characters
	public function generate_new_key($productPrefix) {

		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$segment_chars = 5;
		$num_segments = 5;
		$key_string = $productPrefix.'-';
		do {
			for ($i = 0; $i < $num_segments; $i++) {
				$segment = '';
				for ($j = 0; $j < $segment_chars; $j++) {
					$segment .= $tokens[rand(0, 35)];
				}
				$key_string .= $segment;
				if ($i < ($num_segments - 1)) {
					$key_string .= '-';
				}
			}
		} while ($this->find_license($key_string) != null); // try again if license already exists somehow
		return $key_string;
	}

	// Returns license or null
	public function find_license($key) {
		$r = $this->db->get_where("licenses", array("license_key" => $key))->result();
		return $r == null ? null : $r[0];
	}

	// Returns licenses for display - userId null returns ALL licenses
	public function get_user_product_licenses($userId = null, $include_expired = false) {
		// get data
		$this->db->select("licenses.id as license_id");
		$this->db->select("licenses.license_key as license_key");
		$this->db->select("DATE_FORMAT(licenses.expires_at, '%Y-%m-%d') as expires_at");
		$this->db->select("expires_at > NOW() as is_expired");
		$this->db->select("licenses.owner_user as owner_user");
		$this->db->select("products.name as product_name");
		$this->db->select("products.description as product_description");
		$this->db->from("licenses");
		$this->db->join("products", 'products.id = licenses.product');
		if ($userId != null)
			$this->db->where("licenses.owner_user", $userId);

		// handle expired filtering
		if (!$include_expired)
			$this->db->where("expires_at IS NULL OR expires_at > NOW()");

		return $this->db->get()->result();
	}
}
