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
		return $this->by_email($email);
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
		$r = $this->db->get_where("users", array("email" => $email), 1)->result();
		return $r == null ? null : $r[0];
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
		$r = $this->db->get_where("users", array('id' => $userId))->result();
		return count($r) > 0 ? $r[0]->role : 0;
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
}
