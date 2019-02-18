<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modals extends CI_Controller {
	public function login() {
		$this->load->view('modals/direct_link_protect', array("redirect" => "/user/login"));
		$this->load->view('modals/login');
	}
}
