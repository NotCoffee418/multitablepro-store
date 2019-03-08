<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paypal extends CI_Model {

	// $products: array of products
	public function create_buy_order($product, $purchase_tokens) {
		// Prepare payer
		$payer = new PayPal\Api\Payer();
		$payer->setPaymentMethod("paypal");

		// List product
		$item = new PayPal\Api\Item();
		$item->setName($product->name)
			->setCurrency('USD')
			->setQuantity(1)
			->setSku($product->id)
			->setPrice($product->price);
		$itemList = new PayPal\Api\ItemList();
		$itemList->setItems(array($item));

		// set total amount
		$amount = new PayPal\Api\Amount();
		$amount->setCurrency("USD")
			->setTotal($product->price);

		// transaction description
		$timeString = ' ';
		if ($product->duration_days == null) {
			$now = time();
			$timeString = '(' .
				date("Y-m-d H:i:s", $now) .
				' to ' .
				date("Y-m-d H:i:s", $now + (86500 * $product->duration_days)) .
				')';
		}

		// prepare transaction
		$transaction = new PayPal\Api\Transaction();
		$transaction->setAmount($amount)
			->setItemList($itemList)
			->setDescription($product->name . $timeString)
			->setInvoiceNumber(uniqid());

		// prepare return urls
		$baseUrl = base_url();
		$redirectUrls = new PayPal\Api\RedirectUrls();
		$redirectUrls->setReturnUrl("{$baseUrl}complete_purchase/{$purchase_tokens->complete_token}")
			->setCancelUrl("{$baseUrl}cancel_purchase/{$purchase_tokens->cancel_token}");

		// Create payment
		$payment = new PayPal\Api\Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions(array($transaction));

		$request = clone $payment;
		try {
			$payment->create($this->get_api_context());
			// call $payment->getApprovalLink(); to get payment url
			return $payment;
		}
		catch (Exception $ex) {
			return null;
		}
	}

	public function execute_payment() {
		try {
			$paymentId = $this->input->get('paymentId');
			$payerId = $this->input->get('PayerID');
			$apiContext = $this->get_api_context();
			$payment = PayPal\Api\Payment::get($paymentId, $apiContext);

			// Prepare payment execution
			$execution = new \PayPal\Api\PaymentExecution();
			$execution->setPayerId($payerId);

			// Execute payment
			$result = $payment->execute($execution, $apiContext);

			if ($result->getState() == 'approved' || $result->getState() == 'APPROVED') {
				// return transaction id
				return $result->transactions[0]->related_resources[0]->sale->id;
			}
			else return false; // user cancelled, something went wrong, ..
		}
		catch (Exception $ex){
			show_error("Something went wrong while executing the payment. Try again or contact support.");
			return false;
		}
	}

	public function get_api_context() {
		// Get client and secret from database - no caching (too sensitive)
		if ($this->SiteSettings->get('paypal_debug', false) == true) { // (should be 1 for debug, 0 for live account)
			$client = $this->SiteSettings->get('paypal_debug_clientid', false);
			$secret = $this->SiteSettings->get('paypal_debug_secret', false);
			$mode = 'sandbox';
		}
		else { // live credentials
			$client = $this->SiteSettings->get('paypal_live_clientid', false);
			$secret = $this->SiteSettings->get('paypal_live_secret', false);
			$mode = 'live';
		}

		// Create api context
		$apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				$client,     // ClientID
				$secret     // ClientSecret
			)
		);

		// Set config live enviromnent if needed
		$apiContext->setConfig(array(
			'mode' => $mode
		));

		return $apiContext;
	}

}
