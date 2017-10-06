<?php

require_once 'php/core.php';

logout();

$fields = [
	'last_name' => new field( 'last_name', [
		'placeholder' => 'επώνυμο',
		'value' => $cuser->last_name,
	] ),
	'first_name' => new field( 'first_name', [
		'placeholder' => 'όνομα',
		'value' => $cuser->first_name,
	] ),
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
	$last_name = $fields['last_name']->post();
	$first_name = $fields['first_name']->post();
	$email_address = $fields['email_address']->post();
	$password = $fields['password']->post();
	$user = user::select_by_email_address( $email_address );
	if ( !is_null( $user ) )
		failure( 'Χρησιμοποίησε μία διαφορετική διεύθυνση email.' );
	$user = new user();
	$user->email_address = $email_address;
	$user->password_hash = password_hash( $password, PASSWORD_DEFAULT );
	$user->last_name = $last_name;
	$user->first_name = $first_name;
	$user->role = user::ROLE_UNVER;
	$user->reg_tm = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
	$user->reg_ip = $_SERVER['REMOTE_ADDR'];
	$user->insert();
	$vlink = vlink::write( $user->user_id, 'register' );
	require_once SITE_DIR . 'php/mailer.php';
	$mail = new mailer();
	$mail->addAddress( $user->email_address );
	$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'εγγραφή' );
	$mail->msgHTML( implode( mailer::NEWLINE, [
		sprintf( '<p>Για να ολοκληρώσεις την εγγραφή σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', site_href(), SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'εγγραφή' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	success( [
		'alert' => 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να ολοκληρώσεις την εγγραφή σου.',
		'location' => site_href(),
	] );
}

page_title_set( 'Εγγραφή' );

page_body_add( 'form_section', $fields, [
	'responsive' => 'w3-col m6 s12',
	'submit_icon' => 'fa-power-off',
	'submit_text' => 'εγγραφή',
] );

page_html();