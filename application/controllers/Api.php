<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
	public function test($returnUrl) {
		$this->db->select('licenses.product');
		$this->db->from('licenses');
		$this->db->join('products', 'products.id = licenses.product');
		$this->db->join('product_groups', 'product_groups.id = products.product_group');
		$this->db->where('owner_user', 1);
		$this->db->where('licenses.expires_at >', 'NOW()');
		$this->db->where('licenses.product !=', 4);
		$this->db->group_by('product_groups.id');
		$r = $this->db->get();
		var_dump( $r->num_rows() == 0 ? false : true);
		var_dump($r->result());
	}
}
