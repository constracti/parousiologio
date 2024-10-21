<?php

class mailer extends PHPMailer\PHPMailer\PHPMailer {

	public function __construct() {
		parent::__construct();
		$this->isSMTP();
		$this->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
		$this->Host = MAIL_HOST;
		$this->Port = PHPMailer\PHPMailer\SMTP::DEFAULT_SECURE_PORT;
		$this->SMTPSecure = self::ENCRYPTION_SMTPS;
		$this->SMTPAuth = TRUE;
		$this->Username = MAIL_USER;
		$this->Password = MAIL_PASS;
		$this->CharSet = self::CHARSET_UTF8;
		$this->setFrom( MAIL_USER, SITE_NAME );
	}

	private function save() {
		$mailbox = sprintf( '{%s:993/imap/ssl}INBOX.Sent', MAIL_HOST );
		$imap_stream = imap_open( $mailbox, MAIL_USER, MAIL_PASS );
		if ( $imap_stream === FALSE )
			return;
		imap_append( $imap_stream, $mailbox, $this->getSentMIMEMessage() );
		imap_close( $imap_stream );
	}

	public function send() {
		$success = parent::send();
		$this->save();
		return $success;
	}
}
