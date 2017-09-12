<?php

require_once COMPOSER_DIR . 'phpmailer/vendor/autoload.php';

# TODO mailer in separate thread

class mailer extends PHPMailer\PHPMailer\PHPMailer {

	const NEWLINE = "\r\n";

	public function __construct() {
		parent::__construct();
		$this->isSMTP();
		$this->SMTPDebug = 0;
		$this->Host = MAIL_HOST;
		$this->Port = 587;
		$this->SMTPSecure = 'tls';
		$this->SMTPAuth = TRUE;
		$this->Username = MAIL_USER;
		$this->Password = MAIL_PASS;
		$this->CharSet = 'UTF-8';
		$this->setFrom( MAIL_USER, SITE_NAME );
	}
}