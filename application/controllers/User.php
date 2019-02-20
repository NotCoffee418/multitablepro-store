<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function index() {
		// Redirect to login form when not logged in
		if ($this->Users->get_current_user() == null) {
			redirect('/user/login', 'refresh');
			return;
		}

		// Load models
		$this->load->model(array(
			"Licenses",
			"Purchases"
		));

		// Prepare $data
		$data["page_title"] = "User Control Panel";
		$data["user"] = $this->Users->get_current_user();
		$data["productLicenses"] = $this->Licenses->get_user_product_licenses($data["user"]->id);
		$data["purchaseHistory"] = $this->Purchases->get_user_product_purchases($data["user"]->id);

		// Load views
		$this->load->view('shared/header', $data);
		$this->load->view('user/user_home', $data);
		$this->load->view('shared/footer', $data);
	}

	public function register() {
		// already logged in
		if ($this->Users->get_current_user() != null) {
			redirect('/user', 'refresh');
			return;
		}

		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('fname', 'First Name', 'trim|required|max_length[64]');
		$this->form_validation->set_rules('lname', 'Last Name', 'trim|required|max_length[64]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]|max_length[128]');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[5]|max_length[64]');
		$this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[password]');
		$this->form_validation->set_rules('action', 'Captcha', 'callback_valid_captcha');

		// Form is valid
		if ($this->form_validation->run() === true){

			// Create user
			$user = $this->Users->register(
				$this->input->post('email'),
				$this->input->post('password'),
				$this->input->post('fname'),
				$this->input->post('lname')
			);

			// log in & redirect
			if ($user != null)
				$this->Users->create_user_session($user);
			redirect('/user', 'refresh');
		}
		else { // Form had errors or is fresh
			// Prepare captcha
			$this->load->model('Recaptcha');
			$data["recaptcha"] = $this->Recaptcha->get_recaptcha_html("user/register");
			$data["page_title"] = "Register";

			// Load views
			$this->load->view('shared/header', $data);
			$this->load->view('user/register', $data);
			$this->load->view('shared/footer', $data);
		}
	}

	public function login() {
		// already logged in
		if ($this->Users->get_current_user() != null) {
			redirect('/user');
			return;
		}

		// Load captcha
		$this->load->model("Recaptcha");
		$reCaptcha = $this->Recaptcha->get_recaptcha_html("user/login", "modal-login-form");

		// validate form
		$this->load->library('form_validation');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]|max_length[128]');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[5]|max_length[64]');
		$this->form_validation->set_rules('action', 'Captcha', 'callback_valid_captcha');
		$this->form_validation->set_rules('email', 'Login', 'callback_valid_login'); // callback handles login

		// login is valid
		if ($this->form_validation->run() === true) {
			// Determine where to redirect to
			$url = $this->input->post('redirect');
			if ($url == null || strpos($url, '/user'))
				$url = '/user';

			// Redirect
			redirect($url);
		}
		else { // form had errors or is fresh
			$data["recaptcha_html"] = $this->Recaptcha->get_recaptcha_html("user/login");
			$data["page_title"] = "Login";

			// Load views
			$this->load->view('shared/header', $data);
			$this->load->view('user/login', $data);
			$this->load->view('shared/footer', $data);
		}
	}

	public function logout() {
		if ($this->session->has_userdata("user"))
			$this->session->set_userdata(array("user" => null));
		redirect('/', 'refresh');
	}

	function valid_captcha($action) {
		$this->load->model('Recaptcha');
		$r = $this->Recaptcha->validate($action);
		if ($r === true)
			return true;
		else {
			$this->form_validation->set_message('valid_captcha', 'ReCaptcha Error: ' . $r);
			return false;
		}
	}

	function valid_login($irrelevant) {
		$user = $this->Users->login(
			$this->input->post('email'),
			$this->input->post('password')
		);
		if ($user !== null)
			return true;
		else {
			$this->form_validation->set_message('valid_login', 'Could not log in with the specified email or password.');
			return false;
		}
	}
}
