<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {
/*
 * /Download page
 * $productGroupShort - Null will grab the first product in database
 * $branch - RELEASE, BETA, or INTERNAL (not case sensitive
 * $version - Assembly version eg 1.0.0.5
 * $downloadType -
 * 		"view" display information about the version
 * 		"setup" direct access to the version's setup file
 * 		"update" direct access to the version's update files
 *
 * examples:
 * /download - displays download info for latest release version
 * /download/release/latest/setup - downloads the setup file
 * /download/beta/1.0.1.200 - displays info for a specific beta build if it's available
 */
	public function index($productGroupShort = null, $branch = "release", $version = "latest", $downloadType = "view") {
		// Require VIP+ role to download internal builds on view only
		if (strtolower($branch) == "internal" && strtolower($downloadType) == "view" && !$this->Users->has_vip_permission()) {
			show_error("You do not have permission to view internal builds. Make sure that you are logged in and have VIP permissions.", 403);
			return;
		}

		// Display some info about latest public build
		$this->load->model("Versions");
		$vInfo = $this->Versions->get_version_info($productGroupShort, $branch, $version);
		switch ($downloadType) {
			case "view":
				$this->display_view($vInfo);
				break;
			case "setup":
				$this->get_file($vInfo, "setup");
				break;
			case "update":
				$this->get_file($vInfo, "update");
				break;
			default:
				show_404();
				break;
		}
	}

	private function display_view($versionInfo) {
		if ($versionInfo == null){
			show_404();
			return;
		}

		$data["page_title"] = "Download " . $versionInfo["requested_version"]->product_group_full_name;
		$data["versionInfo"] = $versionInfo;
		$data['user'] = $this->Users->get_current_user();
		$this->load->view('shared/header', $data);
		$this->load->view('download', $data);
		$this->load->view('shared/footer');
	}

	private function get_file($versionInfo, $sub) {
		$this->load->helper('download');
		if ($versionInfo == null){
			show_404();
			return;
		}

		// define ext & mime
		if ($sub == "update") {
			$ext = "zip";
			$mime = "zip";
		}
		else {
			$ext = "msi";
			$mime = "exe";
		}

		// Get local path to request file
		$dir = $this->SiteSettings->get("download_files_dir") . "/$sub/";
		$file_path = "$dir{$versionInfo["requested_version"]->version_id}.$ext";

		// output or error
		if (!file_exists($file_path)) {
			show_error("File was not found. Please contact support with the url you accessed.",500);
			return;
		}

		// Define the output file name
		$output_name = $versionInfo["requested_version"]->product_group_full_name ." ".
			($versionInfo["branch"] == "RELEASE" ? "" : strtolower($versionInfo["requested_version"]->branch)) .
			" v". $versionInfo["requested_version"]->version .".$ext";

		force_download($output_name, file_get_contents($file_path), $mime);
	}
}
