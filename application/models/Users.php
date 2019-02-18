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

	// returns false if not logged in
	public function get_current_user() {
		$this->session->has_userdata("user") ?
			$this->session->userdata("user") : false;
	}
}
