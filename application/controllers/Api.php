<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function validate_license() {
		$macAddr = $this->input->post("macaddr");
		$licenseKey = $this->input->post("license_key");
		$requestProductGroup = $this->input->post("request_product_group");

		$data = $this->get_output_template();
		$this->load->model(array('Licenses', 'Products'));

		// Validate input
		if ($macAddr == null || $licenseKey == null || $requestProductGroup == null) {
			$data["errors"][] = "Invalid input - Missing input data.";
		}

		// Check for trial
		else if ($licenseKey == 'TRIAL')
		{
			$this->load->model('Licenses');
			$data["result"] = $this->Licenses->get_trial_status($macAddr, $requestProductGroup);
		}

		// Validate license
		else {
			try {
				$data["result"] = $this->Licenses->validate_license($licenseKey, $requestProductGroup);
			}
			catch (Exception $ex) {
				$data["errors"][] = $ex->getMessage();
			}
		}

		// Output
		$this->display_json($data);
	}

	public function get_latest_version($productGroupShort = null, $branch = null) {
		$data = $this->get_output_template();
		if (!isset($productGroupShort) || !isset($branch)) {
			$data["errors"][] = "Missing params. Should be called like so: /api/get_latest_version/product-group-short/release";
		}
		else {
			$this->load->model("Versions");
			$data['result'] = $this->Versions->get_version_info($productGroupShort, $branch, "latest");
			if ($data['result'] == null)
				$data["errors"][] = "There were no published versions in this branch.";
		}
		$this->display_json($data);
	}

	public function publish_new_version() {
		$data = $this->get_output_template();
		if ($this->input->post("access_token") !== $this->SiteSettings->get("api_admin_token")) {
			$data["errors"][] = "Invalid access token";
		}

		// publish new version
		$this->load->model("Versions");
		$data["result"]["version_id"] = $this->Versions->publish_new(
			$this->input->post("product_group"), $this->input->post("version"), $this->input->post("branch"), $this->input->post("changelog"));

		$this->display_json($data);
	}

	private function display_json($data) {
		$this->output->set_content_type('application/json');
		$this->output->set_output(json_encode($data));
	}

	private function get_output_template() {
		return array(
			'result' => array(),
			'errors' => array(),
		);
	}
}
