<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modals extends CI_Controller {
	public function login() {
		$this->load->view('modals/login');
	}
}
