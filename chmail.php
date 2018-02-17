<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

$fields = [
	'email_address' => new field_email( 'email_address', [
		'placeholder' => 'καινούρια διεύθυνση email',
		'required' => TRUE,
		'value' => $cuser->email_address,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$email_address = $fields['email_address']->post();
	$user = user::select_by_email_address( $email_address );
	if ( !is_null( $user ) )
			failure( sprintf( 'Η διεύθυνση email %s είναι δεσμευμένη.', $email_address ) );
	$vlink = vlink::write( $cuser->user_id, 'chmail', $email_address );
	require_once SITE_DIR . 'php/mailer.php';
	$mail = new mailer();
	$mail->addAddress( $email_address );
	$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'αλλαγή διεύθυνσης email' );
	$mail->msgHTML( implode( mailer::NEWLINE, [
		sprintf( '<p>Για να επαναφέρεις τη διεύθυνση email του λογαριασμού σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', site_href(), SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'αλλαγή διεύθυνσης email' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	success( [
		'alert' => sprintf( 'Ακολούθησε το σύνδεσμο που εστάλη στη διεύθυνση %s για να την αποθηκεύσεις στο λογαριασμό σου. Έλεγξε και την ανεπιθύμητη αλληλογραφία.', $email_address ),
		'location' => site_href( 'settings.php' ),
	] );
}

page_title_set( 'Διεύθυνση email' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'settings.php' ),
	'text' => 'ρυθμίσεις',
	'icon' => 'fa-cog',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'chmail.php' ),
	'text' => 'διεύθυνση email',
	'icon' => 'fa-envelope',
] );

page_body_add( 'form_section', $fields );

page_html();
