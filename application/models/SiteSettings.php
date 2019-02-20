<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SiteSettings extends CI_Model {
	public function get($name, $cache = true) {
		// Check cache
		$r = $this->Apcu->get('setting-'.$name);

		// Get from DB is needed
		if ($r == null) {
			$qr = $this->db->get_where('settings', array('name' => $name))->result();
			if (count($qr) == 0)
				return show_error("SiteSetting '$name' does not exist.", 500);
			$r = $qr[0]->value;

			// Store if allowed
			if ($cache)
				$this->Apcu->set('setting-'.$name, $r);
		}
		return $r;
	}
}
