<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apcu extends CI_Model {

	public function get($key) {
		if ($this->config->item('apcu_enabled')) {
			$sitePrefix = $this->config->item('apcu_site_prefix');
			return apcu_exists($sitePrefix . $key) ?
				apcu_fetch($sitePrefix . $key) : null;
		}
		else return null;
	}

	public function set($key, $value, $ttl = null) {
		if ($this->config->item('apcu_enabled')) {
			if ($ttl == null)
				$ttl = $this->config->item('apcu_ttl');
			apcu_store($this->config->item('apcu_site_prefix') .$key, $value, $ttl);
		}
	}

	public function delete($key) {
		if ($this->config->item('apcu_enabled'))
			apcu_delete($this->config->item('apcu_site_prefix') . $key);
	}
}
