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

	public function register($returnUrlEncoded = '') {
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


			// Determine where to redirect to
			$redirectUrl = $this->input->post('redirect_url');
			if ($redirectUrl == null || $redirectUrl == '')
				$redirectUrl = '/user';
			redirect($redirectUrl, 'refresh');
		}
		else { // Form had errors or is fresh
			// Prepare captcha
			$this->load->model('Recaptcha');
			$data["recaptcha"] = $this->Recaptcha->get_recaptcha_html("user/register");
			$data["page_title"] = "Register";
			if ($returnUrlEncoded != '') {
				$data['redirect_url'] = $this->Users->base64_url_decode($returnUrlEncoded);
				$data['redirect_url_encoded'] = $returnUrlEncoded;
			}

			// Load views
			$this->load->view('shared/header', $data);
			$this->load->view('user/register', $data);
			$this->load->view('shared/footer', $data);
		}
	}

	public function login($returnUrlEncoded = '') {
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
			$redirectUrl = $this->input->post('redirect_url');
			if ($redirectUrl == null || $redirectUrl == '')
				$redirectUrl = '/user';

			// Redirect
			redirect($redirectUrl);
		}
		else { // form had errors or is fresh
			$data["recaptcha_html"] = $this->Recaptcha->get_recaptcha_html("user/login");
			$data["page_title"] = "Login";
			if ($returnUrlEncoded != '') {
				$data['redirect_url'] = $this->Users->base64_url_decode($returnUrlEncoded);
				$data['redirect_url_encoded'] = $returnUrlEncoded;
			}

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

	public function verify_account($token = null) {
		if ($token == null) {
			$user = $this->Users->get_current_user();
			if ($user == null) {
				show_error('You must be logged in to request an account verification email.');
			}
			else if ($user->email_verified) {
				show_error('Your account is already verified.');
			}
			else {
				$this->Users->request_email_verification($user);
				redirect('/user'); // lazy no-notify redirect
			}
		}
		else { // Attempt to verify the account through input token
			$success = $this->Users->try_verify_account($token);
			if ($success) {
				redirect('/user/#account-verified');
			}
			else {
				show_error("Invalid account verification token. The token does not exist or the account has already been verified".
					"<a href='/user'>Click here</a> to return to the user panel");
			}
		}
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
