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
		if (isset($productGroup->full_name))
			$data["page_description"] = $productGroup->seo_description;
		$data["group_name"] = $productGroup->full_name;
		$data["group_short"] = $productGroup->short_name;
		$data["products"] = $this->Products->products_in_group($productGroup->id);
		$data["payment_methods"] = array( // hardcoded
			'PAYPAL' => 'PayPal'
		);

		// Load views
		$this->load->view('shared/header', $data);
		$this->load->view('store/product-group', $data);
		if (file_exists(APPPATH . '/views/store/' . $shortName."-extra.php")) // Optional include a view with more details
			$this->load->view('store/'.$shortName.'-extra', $data);
		$this->load->view('shared/footer');
	}

	// todo: needs work, only paypal for now
	public function license_action($action, $licenseId) {
		$this->load->model(array('Licenses', 'Purchases', 'Paypal', 'Products'));
		$userId = $this->Users->get_current_user()->id;

		// verify that user owns the license
		$licenseData = $this->Licenses->user_owns_license($userId, $licenseId);
		if ($licenseData === null) {
			show_error("You are not logged in or you do not own this license. Please log in and try again");
			return;
		}

		// Product data
		$pgData = $this->Products->product_and_group_by_id($licenseData->product);

		switch ($action) {
			case 'renew':
				// Create purchase
				$purInfo = $this->Purchases->create_purchase($userId, $licenseData->product, $purchase_type = 'RENEW', $payment_method = 'PAYPAL');

				// Create payment
				$redirectUrl = '';
				$payment = $this->Paypal->create_buy_order($pgData["product"], $purInfo['purchase_tokens']);
				$redirectUrl = $payment->getApprovalLink();
				redirect($redirectUrl, 'refresh');
				break;
			case 'upgrade':
				$toProductId = str_replace('product_', '', $this->input->post('product'));
				$discount = $this->Purchases->calculate_upgrade_discount($licenseId);

				// Display form with upgradables
				if ($toProductId == null) {
					$data["license"] = $licenseData;
					$data["discount"] = $discount;
					$data["products"] = $this->Products->products_in_group($pgData['product_group']->id);

					// Display form
					$data["page_title"] = "Upgrade " . $pgData["product_group"]->full_name;
					$this->load->view('shared/header', $data);
					$this->load->view('store/license_upgrade', $data);
					$this->load->view('shared/footer', $data);
				}
				else {
					$this->load->library('form_validation');
					//$this->form_validation->set_rules('payment_method', 'Payment Method', 'callback_valid_payment_method'); NIY on upgrades
					$this->form_validation->set_rules('product', 'Product', 'callback_valid_product');

					// Validate form
					if ($this->form_validation->run() === false) {
						echo validation_errors();
						//redirect('/store/'.$pInfo['product_group']->short_name);
						return;
					}

					// Load target product
					$newPgdata = $this->Products->product_and_group_by_id($toProductId);

					// Create the purchase & purchase tokens
					$purInfo = $this->Purchases->create_purchase(
						$userId,
						$newPgdata['product']->id,
						'UPGRADE',
						'PAYPAL', //$this->input->post('payment_method') again, NIY
						$newPgdata['product']->price - $discount
					);

					// Request paymen,t
					$payment = $this->Paypal->create_buy_order($newPgdata['product'], $purInfo['purchase_tokens'], $discount);
					$redirectUrl = $payment->getApprovalLink();
					redirect($redirectUrl, 'refresh');
					break;
				}
				break;
		}
	}

	public function request_purchase() {
		$this->load->model(array(
			'Purchases',
			'Products',
			'Licenses'
		));

		// Get product, product group and current user
		$productIdInput = str_replace('product_', '', $this->input->post('product'));
		$pInfo = $this->Products->product_and_group_by_id($productIdInput);
		$currentUser = $this->Users->get_current_user();

		// -- Invalid requests
		// Check for invalid input. This shouldn't happen, simply redirect to product page
		$this->load->library('form_validation');
		$this->form_validation->set_rules('payment_method', 'Payment Method', 'callback_valid_payment_method');
		$this->form_validation->set_rules('product', 'Product', 'callback_valid_product');
		if ($this->form_validation->run() === false) {
			echo validation_errors();
			//redirect('/store/'.$pInfo['product_group']->short_name);
			return;
		}

		// User must be logged in to proceed
		if ($currentUser == null) {
			$returnUrlEncoded = $this->Users->base64_url_encode(base_url().'store/'.$pInfo['product_group']->short_name);
			show_error('Must be logged in to purchase a product.');
			redirect('/user/login/'.$returnUrlEncoded);
			return;
		}

		// User cannot buy a product within the same group
		if ($this->Licenses->user_owns_different_license_in_product_group($pInfo['product']->id, $currentUser->id)) {
			show_error("You cannot buy a license for a different product in a product group for which you already own a license.<br>" .
				"Upgrade or renew your license in the Customer Portal instead.<br>" .
				"If you wish to buy multiple licenses, create additional accounts or contact support for a bulk order.");
			return;
		}

		// -- Valid requests
		// Create the purchase & purchase tokens
		$purInfo = $this->Purchases->create_purchase(
			$currentUser->id,
			$pInfo['product']->id,
			'BUY',
			$this->input->post('payment_method')
		);

		// Create payment
		$redirectUrl = '';
		switch ($this->input->post('payment_method')) {
			case 'PAYPAL':
				$this->load->model('Paypal');
				$payment = $this->Paypal->create_buy_order($pInfo['product'], $purInfo['purchase_tokens']);
				$redirectUrl = $payment->getApprovalLink();
				redirect($redirectUrl, 'refresh');
				break;
		}
	}

	// Called paypal at /cancel_purchase or /complete_purchase/token - see routes
	// is_complete (true or false, 0 or 1) false: order was cancelled. true: order was paid
	// purchase_token: 32 char (for purchase_tokens)
	// paypal also passes it's token, eg: cancel_purchase/OURTOKEN?token=EC-6XK68085EP892640D&country.x=US&locale.x=en_US
	public function handle_purchase_token($is_complete, $purchaseToken) {
		//var_dump($this->input->get('token')); <- this works for pp token
		// mark purchase as complete
		$this->load->model('Purchases');
		$purchaseToken = substr($purchaseToken, 0, 32);
		$redirectUrl = $this->Purchases->finish_purchase($purchaseToken, $is_complete);
		redirect($redirectUrl);
	}

	function valid_payment_method($input) {
		// All supported and allowed payment methods must be manually listed here
		$allowedPaymentMethods = array(
			'PAYPAL',
		);
		$this->form_validation->set_message('payment_method', 'Invalid payment method selected.');
		return in_array($input, $allowedPaymentMethods);
	}
	function valid_product($input) {
		// ensures that the product exist and is_public
		$this->load->model('Products');
		$productId = str_replace('product_', '', $input);
		if (!$this->Products->purchasing_allowed($productId)) {
			// Can occur when user messes with html, cache was not cleared after making a product un-public or no radiobox was checked
			$this->form_validation->set_message('product', 'No valid product was selected or product is no longer available for purchase.');
			return false;
		}
		// Additional checks can go here (eg. upgradables)

		// Valid product
		return true;
	}
}
