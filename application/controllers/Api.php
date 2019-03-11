<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	public function validate_trial() {
		$macAddr = $this->input->post("macaddr");
		$data = $this->get_output_template();

		// Validate input
		if ($macAddr == null) {
			$data["errors"][] = "Invalid input - Missing mac address.";
		}
		else // Get result
		{
			$this->load->model('Licenses');
			$data["result"] = $this->Licenses->get_trial_status($macAddr);
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
