<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store extends CI_Controller {

	// routed: /store/short-name-here
	public function view_product_group($shortName) {
		// Determine if the page is valid
		$productGroups = $this->db->get_where("product_groups", array("short_name" => $shortName))->result();

		// Not a valid product group, 404
		if (count($productGroups) == 0) {
			show_404();
			return; // Never remove this since we're calling views dynamically
		}

		// Get products in this group
		$productsWhere = array(
			"product_group" => $productGroups[0]->id,
			"is_public" => true,
		);
		$products = $this->db->get_where("products", $productsWhere)->result();

		// Data for views
		$data["page_title"] = $productGroups[0]->full_name . " - Store";
		if (isset($productGroups[0]->full_name))
			$data["page_description"] = $productGroups[0]->seo_description;
		$data["group_name"] = $productGroups[0]->full_name;
		$data["products"] = $products;

		// Load views
		$this->load->view('shared/header', $data);
		$this->load->view('store/product-group', $data);
		if (file_exists(APPPATH . '/views/store/' . $shortName."-extra.php")) // Optional include a view with more details
			$this->load->view('store/'.$shortName.'-extra', $data);
		$this->load->view('shared/footer');
	}
}
