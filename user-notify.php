<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$user = user::request();

require_once SITE_DIR . 'php/mailer.php';

$mail = new mailer();
$mail->addAddress( $user->email_address );
$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'ενημέρωση δικαιωμάτων πρόσβασης' );
$mail->msgHTML( implode( mailer::CRLF, [
	sprintf( '<p>Τα δικαιώματα πρόσβασης του λογαριασμού σου στο <a href="%s">%s</a> έχουν ενημερωθεί.</p>', site_href(), SITE_NAME ),
] ) );
$mail->send();

success( [
	'alert' => 'Ο χρήστης ειδοποιήθηκε μέσω ηλεκτρονικού μηνύματος.',
] );
