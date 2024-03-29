<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Versions extends CI_Model {
	public function get_version_info($productGroupShort, $branch, $version) {
		// get first product if none was given (since this site currently only has 1 product but may have more later on)
		if ($productGroupShort == null) {
			$productGroupShort = $this->Apcu->get("get_version_info_product_group_null");
			if ($productGroupShort == null) {
				$this->db->select("short_name");
				$this->db->from("product_groups");
				$row = $this->db->get()->row();
				if ($row == null) // invalid product group
					return null;
				$productGroupShort = $row->short_name;
				$this->Apcu->set("get_version_info_product_group_null", $productGroupShort);
			}
		}

		// Get latest version number
		if ($version == "latest") {
			$version = $this->Apcu->get("get_version_info_latest_{$productGroupShort}_{$branch}");
			if ($version == null) {
				$this->db->select("version");
				$this->db->from("version_info");
				$this->db->join("product_groups", "product_groups.id = version_info.product_group");
				$this->db->where("product_groups.short_name", $productGroupShort);
				$this->sql_branch_selector($branch);
				$this->db->order_by("version_info.id", "desc");
				$row = $this->db->get()->row();
				if ($row == null) // non-existent version
					return null;
				$version = $row->version;
				$this->Apcu->set("get_version_info_latest_{$productGroupShort}_{$branch}", $version);
			}
		}

		// Get version info
		$versionInfo = $this->Apcu->get("get_version_info_latest_{$productGroupShort}_{$branch}_{$version}");
		if ($versionInfo == null) {
			$this->db->select("version_info.id as version_id");
			$this->db->select("version_info.branch as branch");
			$this->db->select("version_info.version as version");
			$this->db->select("version_info.release_date as release_date");
			$this->db->select("version_info.changelog as changelog");
			$this->db->select("product_groups.id as product_group_id");
			$this->db->select("product_groups.full_name as product_group_full_name");
			$this->db->select("product_groups.short_name as product_group_short_name");
			$this->db->from("version_info");
			$this->db->join("product_groups", "product_groups.id = version_info.product_group");
			$this->db->where("product_groups.short_name", $productGroupShort);
			$this->db->where("version_info.version", $version);
			$this->sql_branch_selector($branch);
			$versionInfo["requested_version"] = $this->db->get()->row();

			// Get info about older version as well
			if ($versionInfo["requested_version"] == null) {
				return null;
			}
			else $versionInfo["older_versions"] = $this->get_previous_versions_info(
				$versionInfo["requested_version"]->version_id, $versionInfo["requested_version"]->branch, $productGroupShort);

			// APCU store result
			$this->Apcu->set("get_version_info_latest_{$productGroupShort}_{$branch}_{$version}", $versionInfo);
		}
		return $versionInfo;
	}

	public function get_previous_versions_info($version_id, $branch, $productGroupShort) {
		$this->db->select("version_info.id as version_id");
		$this->db->select("version_info.branch as branch");
		$this->db->select("version_info.version as version");
		$this->db->select("version_info.release_date as release_date");
		$this->db->select("version_info.changelog as changelog");
		$this->db->select("product_groups.id as product_group_id");
		$this->db->select("product_groups.full_name as product_group_full_name");
		$this->db->from("version_info");
		$this->db->join("product_groups", "product_groups.id = version_info.product_group");
		$this->db->where("product_groups.short_name", $productGroupShort);
		$this->db->where("version_info.id <", $version_id);
		$this->sql_branch_selector($branch);
		$this->db->order_by("version_info.id", "desc");
		return $this->db->get()->result();
	}

	public function publish_new($productGroupId, $version, $branch, $changelog) {
		$data = array(
			"product_group" => $productGroupId,
			"version" => $version,
			"branch" => $branch,
			"changelog" => $changelog
		);
		$this->db->insert("version_info", $data);

		// Return the ID
		return $this->db->get_where("version_info", $data)->row()->id;
	}

	// Returns changelogs from all versions newer than the $userVersion in $branch & $pGroupShort
	public function get_changelog_data($pGroupShort, $branch, $userVersion) {
		// Try to find user's version
		$userVersionId = 0;
		$this->db->select('version_info.id as id');
		$this->db->from('version_info');
		$this->db->join('product_groups', 'product_groups.id = version_info.product_group');
		$this->db->where('product_groups.short_name', $pGroupShort);
		$this->db->where('version_info.version', $userVersion);
		$this->sql_branch_selector($branch);
		$uVerInfoGet = $this->db->get();
		if ($uVerInfoGet->num_rows() > 0)
			$userVersionId = $uVerInfoGet->row()->id;

		// Get all newer changelogs
		$this->db->select('*');
		$this->db->from('version_info');
		$this->db->join('product_groups', 'product_groups.id = version_info.product_group');
		$this->db->where('product_groups.short_name', $pGroupShort);
		$this->db->where('version_info.id >', $userVersionId);
		$this->sql_branch_selector($branch);

		// order
		$this->db->order_by("version_info.id", "desc");

		// Return data
		return $this->db->get()->result();
	}

	private function sql_branch_selector($branch) {
		// Also include more stable branches
		$this->db->group_start();
		$this->db->where("version_info.branch", $branch);
		if (strtolower($branch) == 'internal') {
			$this->db->or_where("version_info.branch", 'BETA');
			$this->db->or_where("version_info.branch", 'RELEASE');
		}
		else if (strtolower($branch) == 'beta') {
			$this->db->or_where("version_info.branch", 'RELEASE');
		}
		$this->db->group_end();
	}
}
