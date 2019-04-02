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
		return $this->db->get_where("licenses", array('license_key' => $key))->row();
	}

	// Returns license or null
	public function find_license_by_id($id) {
		return $this->db->get_where("licenses", array("id" => $id))->row();
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
		$targetProduct = $this->db->get_where('products', array('id' => $productId))->row();
		if ($targetProduct == null) {
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
		$this->db->where('licenses.owner_user', $userId);
		$this->db->where('licenses.product', $productId);
		$this->db->where('licenses.expires_at IS NULL OR expires_at > NOW()');
		$fUserLicenseProduct = $this->db->get()->row();

		// Grab the license id for mail
		$licenseId = null;

		// Change buy to renew if user already has a license in the same product_group
		if ($action == 'BUY' && $fUserLicenseProduct != null) {
			$action = 'RENEW';
		}

		// BUY or requested UPGRADE/RENEW but no active license was found - create new license
		if ($action == 'BUY' || $fUserLicenseProduct == null) { //
			if ($action != 'BUY') {
				$action = 'BUY';
				// todo: log this tried to !BUY but no license found, buying instead
			}

			// Determine product group's prefix & duration
			$this->db->select('products.duration_days as duration_days');
			$this->db->select('product_groups.license_prefix as license_prefix');
			$this->db->from('products');
			$this->db->join('product_groups', 'product_groups.id = products.product_group');
			$this->db->where('products.id', $productId);
			$newLicenseInfo = $this->db->get()->row();

			// Generate new license key with correct prefix
			$license_key = $this->generate_new_key($newLicenseInfo->license_prefix);

			// Set issue date & expiration date
			$issueTimestamp = time();
			$expireTimestamp = $issueTimestamp + ($newLicenseInfo->duration_days * 86400);

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

			// Grab license id we just inserted (BUY)
			$licenseId = $this->db->get_where('license_key', $license_key)->row()->id;
		}

		// UPGRADE - Change product
		if ($action == 'UPGRADE') {
			$newExpirationTimestamp = time() +
				($targetProduct->duration_days * 86400); // 86400 is a day

			$this->db->set('product', $productId);
			$this->db->set('expires_at', date("Y-m-d H:i:s", $newExpirationTimestamp));
			$this->db->where('id',$fUserLicenseProduct->license_id);
			$this->db->update('licenses');

			// Grab license id we just inserted (UPGRADE)
			$licenseId = $licenseId = $this->db->get_where('license_key', $license_key)->row()->id;
		}

		// RENEW - Change expires_at
		if ($action == 'RENEW') {
			if ($fUserLicenseProduct->expires_at == null) {
				show_error("Attempting to renew a product that doesn't expire on it or buy a product you already have.", 500);
				return; // todo: write these in a log
			}

			// Determine new expiration date
			$newExpirationTimestamp = $fUserLicenseProduct->expires_at_unix +
				($targetProduct->duration_days * 86400); // 86400 is a day

			// Save new expiration date
			$this->db->set('expires_at', date("Y-m-d H:i:s", $newExpirationTimestamp));
			$this->db->where('id', $fUserLicenseProduct->license_id);
			$this->db->update('licenses');

			// Grab license id we just inserted (RENEW)
			$licenseId = $licenseId = $this->db->get_where('license_key', $license_key)->row()->id;
		}

		// Clear user's license cache
		$this->Apcu->delete('get_user_product_licenses-'.$userId);

		// todo: log what happened

		// Send email with license info
		$this->sendLicenseMail($licenseId);
		return $action; // may need to be updated
	}

	// Determines if the user already owns a license for the specified product group
	// However, if the user owns the $requestProduct, we also return false
	// This is used to prevent cheap upgrades and renewals, intentional or unintentional
	public function user_owns_different_license_in_product_group($requestProductId, $userId) {
		$this->db->select('licenses.product');
		$this->db->from('licenses');
		$this->db->join('products', 'products.id = licenses.product');
		$this->db->join('product_groups', 'product_groups.id = products.product_group');
		$this->db->where('owner_user', $userId);
		$this->db->where('licenses.expires_at >', 'NOW()');
		$this->db->where('licenses.product !=', $requestProductId);
		$this->db->group_by('product_groups.id');
		return $this->db->get()->num_rows() == 0 ? false : true;
	}


	// Returns license info or false
	public function user_owns_license($userId, $licenseId) {
		$this->db->select('*');
		$this->db->from('licenses');
		$this->db->where('id', $licenseId);
		$this->db->where('owner_user', $userId);
		return $this->db->get()->row();
	}

	public function get_trial_status($macAddress, $productGroupId) {
		// See if mac is known
		$q = $this->db->get_where("trials", array("mac_address" => $macAddress));

		// Create new trial if needed
		if ($q->num_rows() == 0) {
			$trialEndTime = date("Y-m-d H:i:s", time() + (30 * 86400)); // month trial
			$insertData = array(
				"mac_address" => $macAddress,
				"expires_at" => $trialEndTime,
				"product_group" => $productGroupId,
			);
			$this->db->insert("trials", $insertData);
		}

		// Get response
		$this->db->select('expires_at');
		$this->db->select('expires_at > CURRENT_TIMESTAMP() AS is_valid');
		$this->db->from("trials");
		$this->db->where('mac_address', $macAddress);
		$this->db->where('product_group', $productGroupId);
		$r = $this->db->get()->row();
		return array(
			"expires_at" => $r->expires_at,
			"is_valid" => $r->is_valid == true // since db returns 0 or 1
		);
	}

	// called by api/validate_license
	public function validate_license($licenseKey, $productGroupId) {
		// Get info
		$this->load->model('Products');
		$this->db->select('licenses.expires_at > CURRENT_TIMESTAMP() || licenses.expires_at IS NULL AS is_valid'); // 0 or 1 -
		$this->db->select('licenses.expires_at as expires_at');
		$this->db->select('products.id as product_id');
		$this->db->select('products.name as product_name');
		$this->db->select('products.description as product_description');
		$this->db->select('users.first_name as first_name');
		$this->db->select('users.last_name as last_name');
		$this->db->select('users.email as email');
		$this->db->from('licenses');
		$this->db->join('users', 'users.id = licenses.owner_user');
		$this->db->join('products', 'products.id = licenses.product');
		$this->db->join('product_groups', 'product_groups.id = products.product_group');
		$this->db->where('licenses.license_key', $licenseKey);
		$this->db->where('product_groups.id', $productGroupId);
		$r = $this->db->get()->row();

		// Return invalid license
		if ($r == null)
			return array(
				'is_valid' => 0,
				'license_status_message' => 'Invalid or expired license'
			);

		// Get restrictions
		$restrictions = $this->Products->get_product_restrictions_array($r->product_id);

		// Return results
		return array(
			'is_valid' => $r->is_valid,
			'expires_at' => $r->expires_at,
			'product_name' => $r->product_name,
			'product_description' => $r->product_description,
			'first_name' => $r->first_name,
			'last_name' => $r->last_name,
			'email' => $r->email,
			'restrictions' => $restrictions,
			'license_status_message' => 'Active license',
		);
	}

	// Send e-mail with license key to the user based on license id
	public function sendLicenseMail($licenseId) {
		// Get the data we need
		$this->db->select('licenses.license_key as license_key');
		$this->db->select('licenses.expires_at as expires_at');
		$this->db->select('users.email as email');
		$this->db->select('products.name as product_name');
		$this->db->select('product_groups.short_name as short_name');
		$this->db->from('licenses');
		$this->db->join('users', 'users.id = licenses.owner_user');
		$this->db->join('products', 'products.id = licenses.product');
		$this->db->join('product_groups', 'product_groups.id = products.product_group');
		$this->db->where('licenses.id', $licenseId);
		$data = $this->db->get()->row();

		// Send the e-mail
		$this->load->model("Email");
		$mailData = array(
			'subject' => "Your {$data->product_name} License Key",
			'toEmail' => $data->email,
			'license_key' => $data->license_key,
			'expires_at' => $data->expires_at,
			'product_name' => $data->product_name,
			'short_name' => $data->short_name,
		);
		$this->Email->sendMail('license_created', $mailData);
	}

}
