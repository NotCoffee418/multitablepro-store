<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SiteSettings extends CI_Model {
	public function get($name, $cache = true) {
		// Check cache
		$value = $this->Apcu->get('setting-'.$name);

		// Get from DB is needed
		if ($value == null) {
			$value = $this->db->get_where('settings', array('name' => $name))->row()->value;
			if ($value == null)
				return show_error("SiteSetting '$name' does not exist.", 500);

			// Store if allowed
			if ($cache === true)
				$this->Apcu->set('setting-'.$name, $value);
		}
		return $value;
	}
}
