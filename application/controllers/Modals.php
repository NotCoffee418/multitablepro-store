<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modals extends CI_Controller {
	public function login() {
		// Direct link protection
		$this->load->view('modals/direct_link_protect', array("redirect" => "/user/login"));

		// Load captcha
		$this->load->model("Recaptcha");
		$reCaptcha = $this->Recaptcha->get_recaptcha_html("user/login");

		// Load view
		$this->load->view('modals/login', array('recaptcha_html' => $reCaptcha));
	}
}
