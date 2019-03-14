<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$data['user'] = $this->Users->get_current_user();
		$this->load->view('shared/header', $data);
		$this->load->view('home');
		$this->load->view('shared/footer');
	}

	public function eula() {
		$data['user'] = $this->Users->get_current_user();
		$data["page_title"] = "End User License Agreement";
		$this->load->view('shared/header', $data);
		$this->load->view('eula');
		$this->load->view('shared/footer');
	}

	public function privacy_policy() {
		$data['user'] = $this->Users->get_current_user();
		$data["page_title"] = "Privacy Policy";
		$this->load->view('shared/header', $data);
		$this->load->view('privacy-policy');
		$this->load->view('shared/footer');
	}
}
