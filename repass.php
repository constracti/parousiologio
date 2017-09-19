<?php

require_once 'php/page.php';

logout();

$field_success = TRUE;
$fields = [
	'email_address' => new field( 'email_address', [
		'type' => 'email',
		'placeholder' => 'διεύθυνση email',
		'required' => TRUE,
	] ),
	'password' => new field( 'password', [
		'type' => 'password',
		'placeholder' => 'κωδικός πρόσβασης',
		'required' => TRUE,
	] ),
];

$field_success && ( function( array $fields ) {
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return;
	$email_address = $fields['email_address']->value();
	$password = $fields['password']->value();
	$user = user::select_by( 'email_address', $email_address );
	if ( is_null( $user ) )
		return page_message_add( 'Δεν υπάρχει εγγεγραμμένος χρήστης με αυτή τη διεύθυνση email.', 'error' );
	if ( $user->role_id === user::ROLE_UNVER )
		return page_message_add( 'Ακολούθησε πρώτα τον σύνδεσμο επαλήθευσης από τα εισερχόμενά σου.', 'error' );
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
	return page_message_add( 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να επαναφέρεις τον κωδικό πρόσβασης του λογαριασμού σου.', 'success' );
} )( $fields );

page_title_set( 'Επαναφορά κωδικού πρόσβασης' );

page_body_add( 'form_html', $fields, [
	'submit_icon' => 'fa-lock',
	'submit_text' => 'επαναφορά',
] );

page_html();