<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Model {
	public function register($email, $plain_pass, $first_name, $last_name) {
		// all validation should have already been run in controller

		$data = array(
			"email" => $email,
			"pass_hash" => $this->hash_password($plain_pass),
			"first_name" => $first_name,
			"last_name" => $last_name
		);
		$this->db->insert("users", $data);
		$user = $this->by_email($email);
		$this->request_email_verification($user);
		return $user;
	}

	public function request_email_verification($user) {
		// See if user already has a pending verification request
		$this->db->select('user');
		$this->db->select('verification_token');
		$this->db->select('TIMESTAMPADD(MINUTE, 5, last_request_time) < CURRENT_TIMESTAMP() AS send_request_allowed');
		$this->db->where('user', $user->id);
		$q = $this->db->get("email_verifications");

		$mailSendAllowed = true;
		$token = '';
		if ($q->num_rows() > 0) {
			$row = $q->row();
			$token = $row->verification_token;
			$mailSendAllowed = $row->send_request_allowed == 1;
		}
		else {
			// Generate token & insert to DB
			$token = $this->generate_email_verify_token();
			$data = array(
				'user' => $user->id,
				'verification_token' => $token
			);
			$this->db->insert('email_verifications', $data);
		}

		// User recently requested a verification email - show error
		if (!$mailSendAllowed) {
			show_error('You are only allow to request an account verification e-mail every 5 minutes.<br>'.
			'Please check your spam folder and try again in a few minutes.');
			return false;
		}
		else {
			// Send the account verification mail
			$mailData = array(
				'toEmail' => $user->email,
				'first_name' => $user->first_name,
				'subject' => 'Verify your account',
				'verification_token' => $token
			);
			$this->load->model("Email");
			$this->Email->sendMail('account_verify', $mailData);

			// Update last request time
			$this->db->set('last_request_time',  date("Y-m-d H:i:s", time()));
			$this->db->where('verification_token', $token);
			$this->db->update('email_verifications');
			return true;
		}
	}

	public function try_verify_account($token) {
		$q = $this->db->get_where("email_verifications", array('verification_token' => $token));

		// Check if verification request exists
		if ($q->num_rows() == 0)
			return false;

		// get user & verify request data
		$verify_row = $q->row();
		$userRow = $this->by_id($verify_row->user);

		// Check if user exist
		if ($userRow == null){
			show_error("User for this token does not exist. Contact support.");
			return false;
		}

		// Set user's account to verified
		$this->db->set('email_verified', 1);
		$this->db->where('id', $verify_row->user);
		$this->db->update('users');

		// Remove the verification request
		$this->db->delete('email_verifications', array('verification_token' => $token));

		// Return success
		return true;
	}

	public function login($email, $plain_pass) {
		$user = $this->by_email($email);
		return $user == null || !password_verify($plain_pass, $user->pass_hash) ?
			null : $this->create_user_session($user);
	}

	public function create_user_session($user_data) {
		$this->session->set_userdata(array("user" => $user_data));
		return $this->session->userdata("user");
	}

	public function email_exists($email) {
		return $this->by_email($email) == null ? false : true;
	}

	// intended to change password later on
	public function set_password($user_id, $plain_pass) {
		$this->db->set("pass_hash", $this->hash_password($plain_pass));
		$this->db->where("id", $user_id);
		$this->db->update("users");
	}

	public function hash_password($plain_pass) {
		return password_hash($plain_pass, CRYPT_BLOWFISH); // - returns false on fail
	}

	public function by_email($email) {
		return $this->db->get_where("users", array("email" => $email), 1)->row();
	}

	public function by_id($id) {
		return $this->db->get_where("users", array("id" => $id), 1)->row();
	}

	// returns null if not logged in
	public function get_current_user() {
		return $this->session->userdata("user");
	}

	public function get_user_role($userId = null) {
		// Determine userId, return 0 if quest
		if ($userId == null) {
			$user = $this->get_current_user();
			if ($user == null)
				return 0; // not logged in, rank 0
			else $userId = $user->id;
		}

		// get user's role
		$r = $this->db->get_where("users", array('id' => $userId));
		return $r->num_rows() == 0 ? 0 : $r->row()->role;
	}

	public function has_vip_permission($userId = null) {
		return $this->get_user_role($userId) >= 2;
	}
	public function has_support_permission($userId = null) {
		return $this->get_user_role($userId) >= 3;
	}
	public function has_developer_permission($userId = null) {
		return $this->get_user_role($userId) >= 4;
	}
	public function has_admin_permission($userId = null) {
		return $this->get_user_role($userId) >= 5;
	}

	// Used to pass a return url on login or register
	function base64_url_encode($input) {
		return strtr(base64_encode($input), '+/=', '._-');
	}
	function base64_url_decode($input) {
		$url = base64_decode(strtr($input, '._-', '+/='));

		// Confirm that url is a url
		// Prevents user being able to edit the visible display of site by inserting html
		if (filter_var($url, FILTER_VALIDATE_URL) === FALSE)
			return null;
		else return $url;
	}

	public function generate_email_verify_token() {
		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$length = 32;
		do {
			$token = "";
			for ($i = 0; $i < $length; $i++) {
				$token .= $tokens[rand(0, 35)];
			}

			// Check if token already exists
			$this->db->select('*');
			$this->db->from('email_verifications');
			$this->db->where('verification_token', $token);
			$foundTokens = $this->db->get()->num_rows();
		} while ($foundTokens > 0); // try again if token already exists somehow
		return $token;
	}
}
