<?php

class Licenses extends CI_Model {

	public function generate_new_key($productPrefix) {

		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$segment_chars = 5;
		$num_segments = 5;
		$key_string = $productPrefix.'-';
		do {
			for ($i = 0; $i < $num_segments; $i++) {
				$segment = '';
				for ($j = 0; $j < $segment_chars; $j++) {
					$segment .= $tokens[rand(0, 35)];
				}
				$key_string .= $segment;
				if ($i < ($num_segments - 1)) {
					$key_string .= '-';
				}
			}
		} while ($this->find_license($key_string) != null); // try again if license already exists somehow
		return $key_string;
	}

	// Returns license or null
	public function find_license($key) {
		$r = $this->db->get_where("licenses", array("license_key" => $key))->result();
		return $r == null ? null : $r[0];
	}
}
