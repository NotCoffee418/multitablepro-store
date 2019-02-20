<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
	public function index() {
		if (!$this->Users->has_admin_permission()) {
			show_error("Forbidden", 403);
			return;
		}

		// Set data
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
}
