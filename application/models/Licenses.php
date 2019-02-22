<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Licenses extends CI_Model {

	// $product prefix must be 5 characters
	public function generate_new_key($productPrefix) {

		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$segment_chars = 5;
		$num_segments = 5;
		do {
			$key_string = $productPrefix.'-';
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
		$r = $this->Apcu->get('get_user_product_licenses-'.$userId);
		if ($r == null) {
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

			// run query
			$r = $this->db->get()->result();

			// only cache if not admin request
			if (!$userId == null && !$include_expired)
				$this->Apcu->set('get_user_product_licenses-'.$userId, $r);
		}
		return $r;
	}

	// Create, upgrade or renew user license
	// Uses product_group to determine upgrades (NIY)
	// $userId
	// $productId
	// $action - 'BUY', 'RENEW' or 'UPGRADE'
	public function set_user_license($userId, $productId, $action) {
		// Get product row
		$targetProducts = $this->db->get_where('products', array('id' => $productId))->result();
		if (count($targetProducts) == 0) {
			show_error("Requested product does not exist.", 500);
			return;
		}

		// Grab (active only) license and product data we need for UPGRADE/RENEW
		$this->db->select('licenses.id as license_id');
		$this->db->select('licenses.product as product_id');
		$this->db->select('licenses.expires_at as expires_at');
		$this->db->select('UNIX_TIMESTAMP(licenses.expires_at) as expires_at_unix');
		$this->db->select('products.product_group as product_group');
		$this->db->select('products.duration_days as duration_days');
		$this->db->select('product_groups.license_prefix as license_prefix');
		$this->db->from('licenses');
		$this->db->join('products', 'products.id = licenses.product');
		$this->db->join('product_groups', 'product_groups.id = products.product_group');
		$this->db->where('licenses.owner_user', 1);
		$this->db->where('licenses.product', 3);
		$this->db->where('licenses.expires_at IS NULL OR expires_at > NOW()');
		$fUserLicenseProduct = $this->db->get()->result();

		// Change buy to renew if user already has a license in the same product_group
		if ($action == 'BUY' && count($fUserLicenseProduct) > 0) {
			$action = 'RENEW';
		}

		// BUY or requested UPGRADE/RENEW but no active license was found - create new license
		if ($action == 'BUY' || count($fUserLicenseProduct) == 0) { //
			if ($action != 'BUY') {
				// todo: log this tried to !BUY but no license found, buying instead
			}

			// Determine product group's prefix & duration
			$this->db->select('products.duration_days as duration_days');
			$this->db->select('product_groups.license_prefix as license_prefix');
			$this->db->from('products');
			$this->db->join('product_groups', 'product_groups.id = products.product_group');
			$this->db->where('products.id', $productId);
			$newLicenseInfo = $this->db->get()->result();

			// Generate new license key with correct prefix
			$license_key  = $this->generate_new_key($newLicenseInfo[0]->license_prefix);

			// Set issue date & expiration date
			$issueTimestamp = time();
			$expireTimestamp = $issueTimestamp + ($newLicenseInfo[0]->duration_days * 86400);

			// Insert the license key
			$insertData = array(
				'license_key' => $license_key,
				'product' => $productId,
				'owner_user' => $userId,
				'issued_at' => date("Y-m-d H:i:s", $issueTimestamp),
				'expires_at' => date("Y-m-d H:i:s", $expireTimestamp),
			);
			$this->db->insert('licenses', $insertData);
			// todo: log this
		}

		// UPGRADE - Change product
		if ($action == 'UPGRADE') {
			$this->db->set('product', $productId);
			$this->db->where('license_id',$fUserLicenseProduct[0]->license_id);
			$this->db->update('licenses');
		}

		// RENEW - Change expires_at
		// Upgrade also renews
		if ($action == 'RENEW' || $action == 'UPGRADE') {
			if ($fUserLicenseProduct[0]->expires_at == null) {
				show_error("Attempting to renew a product that doesn't expire on it or buy a product you already have.", 500);
				return; // todo: write these in a log
			}

			// Determine new expiration date
			$newExpirationTimestamp = $fUserLicenseProduct[0]->expires_at_unix +
				($targetProducts[0]->duration_days * 86400); // 86400 is a day

			// Save new expiration date
			$this->db->set('expires_at', date("Y-m-d H:i:s", $newExpirationTimestamp));
			$this->db->where('id', $fUserLicenseProduct[0]->license_id);
			$this->db->update('licenses');
		}

		// Clear user's license cache
		$this->Apcu->delete('get_user_product_licenses-'.$userId);

		// todo: log what happened
		return $action; // may need to be updated
	}
}
