<?php
/**
 * Created by PhpStorm.
 * User: night
 * Date: 2019/02/17
 * Time: 21:53
 */

class Users extends CI_Model
{
	public function register($email, $plain_pass, $first_name, $last_name) {
		// all validation should have already been run in controller

		$pass_hash = $this->hash_password($plain_pass);
		$email = $email == "" ? null : $email; // set to null if empty
		$this->db->query(
			"INSERT INTO users (email, pass_hash, first_name, last_name) VALUES (?, ?, ?, ?);",
			array($email, $pass_hash, $first_name, $last_name)
		);
		return $this->by_email($email);
	}

	public function login($email, $plain_pass) {
		$user = $this->by_email($email);
		if ($user == null || !password_verify($plain_pass, $user->pass_hash))
			return null;
		else return $this->create_user_session($user);
	}

	public function create_user_session($user_data) {
		$this->session->set_userdata(array("user" => $user_data));
		return $this->session->userdata("user");
	}

	public function email_exists($email) {
		$r = $this->db->query("SELECT COUNT(*) as count FROM users WHERE email = ?;", $email)->result();
		if ($r[0]->count == 1)
			return true;
		else return false;
	}

	// intended to change password later on
	public function set_password($user_id, $plain_pass) {
		$pass_hash = $this->hash_password($plain_pass);
		$this->db->query("UPDATE users SET pass_hash = ? WHERE id = ?", array($pass_hash, $user_id));
	}

	public function hash_password($plain_pass) {
		return password_hash($plain_pass, CRYPT_BLOWFISH); // - returns false on fail
	}

	public function by_email($email) {
		$r = $this->db->query("SELECT * FROM users WHERE email = ?;", $email)->result();
		if (count($r) == 0)
			return null;
		else return $r[0];
	}

	// returns false if not logged in
	public function get_current_user() {
		if ($this->session->has_userdata("user"))
			return false;
		else return $this->session->userdata("user");
	}
}
