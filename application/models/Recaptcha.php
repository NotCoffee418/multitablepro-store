<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recaptcha extends CI_Model {

	/*
	 * REFERENCE FUNCTION!! Don't call directly!!
	 * This must be pasted in any controller using recaptcha to use form validation like so:
	 * $this->form_validation->set_rules('action', 'Captcha', 'callback_valid_captcha');
	*/
	function valid_captcha($action) {
		$this->load->model('Recaptcha');
		$r = $this->Recaptcha->validate($action);
		if ($r === true)
			return true;
		else {
			$this->form_validation->set_message('valid_captcha', 'ReCaptcha Error: ' . $r);
			return false;
		}
	}

	// Since it's quite an odd situation, use this to get the HTML, pass it in $data to a form
	// Place it near the end of your form
	public function get_recaptcha_html($action) {
		$data = array(
			"recaptcha_public" => $this->config->item("recaptcha_public"),
			"action" => $action
		);
		return $this->load->view('shared/recaptcha', $data, true);
	}

	// returns true or error string
	public function validate($action) {
		// No response
		if ($this->input->post('g-recaptcha-response') == null) // null or '' implied
			return "Failed to contact ReCaptcha server. Try again in a few seconds or disable any Google-blocking.";

		// Validate captcha
		try {
			$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.
				$this->config->item("recaptcha_private").'&response='.$this->input->post('g-recaptcha-response'));
			$responseData = json_decode($verifyResponse);
			return $responseData->success == true && $responseData->action == $action ?
				true : "(".join($responseData->{'error-codes'}).")";
		}
		catch (Exception $ex){
			return "Failed to contact ReCaptcha server. Misconfigured?"; // something went wrong, reject
		}
	}
}
