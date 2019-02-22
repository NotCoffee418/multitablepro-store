<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
	public function test($action) {
		$this->load->model(array('Licenses', 'Purchases'));
		switch($action) {
			case 'create_purchase':
				$this->Purchases->create_purchase(1, 3, 'BUY', 'PAYPAL', 'test_killme');
				var_dump("look into this, it failed twice");
				break;
		}
	}
}
