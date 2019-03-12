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
			$data["result"] = $this->Licenses->get_trial_status($macAddr);
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
