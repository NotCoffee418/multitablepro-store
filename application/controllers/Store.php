<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends CI_Controller {

	// routed: /store/short-name-here
	public function view_product_group($shortName) {
		$this->load->model('Products');
		// Determine if the page is valid
		$productGroup = $this->Products->product_group_by_shortname($shortName);

		// Not a valid product group, 404
		if ($productGroup == null) {
			show_404();
			return; // Never remove this since we're calling views dynamically
		}

		// Data for views
		$data["page_title"] = $productGroup->full_name . " - Store";
		$data['user'] = $this->Users->get_current_user();
		if (isset($productGroups[0]->full_name))
			$data["page_description"] = $productGroup->seo_description;
		$data["group_name"] = $productGroup->full_name;
		$data["products"] = $this->Products->products_in_group($productGroup->id);

		// Load views
		$this->load->view('shared/header', $data);
		$this->load->view('store/product-group', $data);
		if (file_exists(APPPATH . '/views/store/' . $shortName."-extra.php")) // Optional include a view with more details
			$this->load->view('store/'.$shortName.'-extra', $data);
		$this->load->view('shared/footer');
	}
}
