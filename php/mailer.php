<?php

class mailer extends PHPMailer\PHPMailer\PHPMailer {

	const NEWLINE = "\r\n";

	public function __construct() {
		parent::__construct();
		$this->isSMTP();
		$this->SMTPDebug = 0;
		$this->Host = MAIL_HOST;
		$this->Port = 465;
		$this->SMTPSecure = 'ssl';
		$this->SMTPAuth = TRUE;
		$this->Username = MAIL_USER;
		$this->Password = MAIL_PASS;
		$this->CharSet = 'UTF-8';
		$this->setFrom( MAIL_USER, SITE_NAME );
	}

	public function save() {
		$mailbox = sprintf( '{%s:993/imap/ssl}INBOX.Sent', MAIL_HOST );
		$imap_stream = imap_open( $mailbox, MAIL_USER, MAIL_PASS );
		imap_append( $imap_stream, $mailbox, $this->getSentMIMEMessage() );
		imap_close( $imap_stream );
	}

	public function send() {
		$success = parent::send();
		$this->save();
		return $success;
	}
}
