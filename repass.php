<?php

require_once 'php/core.php';

logout();

$fields = [
	'email_address' => new field_email( 'email_address', [
		'placeholder' => 'διεύθυνση email',
		'required' => TRUE,
	] ),
	'password' => new field_password( 'password', [
		'placeholder' => 'κωδικός πρόσβασης',
		'required' => TRUE,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$email_address = $fields['email_address']->post();
	$password = $fields['password']->post();
	$user = user::select_by_email_address( $email_address );
	if ( is_null( $user ) )
		failure( 'Δεν υπάρχει εγγεγραμμένος χρήστης με αυτή τη διεύθυνση email.' );
	$hash = password_hash( $password, PASSWORD_DEFAULT );
	$vlink = vlink::write( $user->user_id, 'repass', $hash );
	require_once SITE_DIR . 'php/mailer.php';
	$mail = new mailer();
	$mail->addAddress( $user->email_address );
	$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'επαναφορά κωδικού πρόσβασης' );
	$mail->msgHTML( implode( mailer::NEWLINE, [
		sprintf( '<p>Για να επαναφέρεις τον κωδικό πρόσβασης του λογαριασμού σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', SITE_URL, SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'επαναφορά κωδικού πρόσβασης' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	success( [
		'alert' => 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να επαναφέρεις τον κωδικό πρόσβασης του λογαριασμού σου.',
		'location' => SITE_URL,
	] );
}

page_title_set( 'Επαναφορά κωδικού πρόσβασης' );

page_body_add( 'form_section', $fields, [
	'submit_icon' => 'fa-lock',
	'submit_text' => 'επαναφορά',
] );

page_html();