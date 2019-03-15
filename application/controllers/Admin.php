<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function test() {
		if (!$this->Users->has_admin_permission()) {
			show_error("Forbidden", 403);
			return;
		}

		$this->load->model("Email");
		$data = array(
			"toEmail" => $this->Users->get_current_user()->email,
			"subject" => "Testing Noreply Subject",
			'input' => "Some Input"
		);
		//$this->Email->sendMail("account_verify", $data);

		$this->load->view('email_templates/mail_header', $data);
		$this->load->view('email_templates/account_verify', $data);
		$this->load->view('email_templates/mail_footer', $data);
	}

	public function index() {
		if (!$this->Users->has_admin_permission()) {
			show_error("Forbidden", 403);
			return;
		}

		// Set data
		$data['user'] = $this->Users->get_current_user();
		$data["has_permission"] = true; // In case views become accessible in the future
		$data["page_title"] = "Admin Panel";

		// APCu
		if ($this->config->item('apcu_enabled')){
			$data["apcu_cache_info"] = apcu_cache_info();
			$data["apcu_sma_info"] = apcu_sma_info();
		}

		// Load views
		$this->load->view('shared/header', $data);
		$this->load->view('admin/admin_home', $data);
		$this->load->view('shared/footer');
	}

	public function wipe_apcu_cache() {
		if (!$this->Users->has_admin_permission()) {
			show_error("Forbidden", 403);
			return;
		}

		// Empty cache
		apcu_clear_cache();

		// Back to admin home
		redirect("/admin", "refresh");
	}

	public function generate_license_for_user() {
		if (!$this->Users->has_admin_permission()) {
			show_error("Forbidden", 403);
			return;
		}

		// Check if input is valid, no validation beyond that
		if ($this->input->post("user") == null || $this->input->post("product") == null ||
				!is_numeric($this->input->post("user")) || !is_numeric($this->input->post("product")))
		{
			echo "User and product must be ID's";
			return;
		}

		// Create license for user
		$this->load->model("Licenses");
		$this->Licenses->set_user_license($this->input->post("user"), $this->input->post("product"), 'BUY');

		// Print info about user & new license
		echo "Created license<br></br>User:<br>";
		print_r($this->db->get_where("users", array('id' => $this->input->post("user")))->row());
		echo "<br><br>User's licenses<br>";
		print_r($this->db->get_where("licenses", array('owner_user' => $this->input->post("user"), 'product' => $this->input->post("product")))->row());
	}
}
