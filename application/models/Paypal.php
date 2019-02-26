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
		//try { disable catch for testing
			$payment->create($this->get_api_context());
		//}
		//catch (Exception $ex) {

		//}

		// call $payment->getApprovalLink(); to get payment url
		return $payment;
	}

	public function get_api_context() {
		// Get client and secret from database - no caching (too sensitive)
		if ($this->SiteSettings->get('paypal_debug', false) == true) { // (should be 1 for debug, 0 for live account)
			$client = $this->SiteSettings->get('paypal_debug_clientid', false);
			$secret = $this->SiteSettings->get('paypal_debug_secret', false);
		}
		else { // live credentials
			$client = $this->SiteSettings->get('paypal_live_clientid', false);
			$secret = $this->SiteSettings->get('paypal_live_secret', false);
		}

		return new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				$client,     // ClientID
				$secret     // ClientSecret
			)
		);
	}

}
