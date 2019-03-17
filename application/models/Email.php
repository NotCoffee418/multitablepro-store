<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email extends CI_Model {
	// Send mails using a template
	// $viewData must contain toEmail and subject + all view variables
	public function sendMail($viewName, $viewData) {
		// Generate html
		$html = $this->load->view('email_templates/mail_header', $viewData, true) .
			$this->load->view('email_templates/'.$viewName, $viewData, true) .
			$this->load->view('email_templates/mail_footer', $viewData, true);

		// Send the mail
		$this->sendMailDirect($viewData["toEmail"], $viewData["subject"], $html);
	}

	// Used to send mails without calling a template
	public function sendMailDirect($toEmail, $subject, $htmlBody) {
		$this->load->library('email');

		$mailConfig = array(
			'smtp_host' => '127.0.0.1',
			'smtp_port' => '587',
			'smtp_user' => $this->SiteSettings->get("email_user"),
			'smtp_user' => $this->SiteSettings->get("email_pass"),
			$config['protocol'] = 'sendmail',
			$config['mailpath'] = '/usr/sbin/sendmail',
			'mailtype' => 'html',
			'charset' => 'utf-8',
		);
		$this->email->initialize($mailConfig);
		$this->email->from(
			$this->SiteSettings->get("email_user"),
			$this->SiteSettings->get("email_sender_name")
		);
		$this->email->to($toEmail);
		$this->email->subject($subject);
		$this->email->message($htmlBody);
		$this->email->send();
	}

}
