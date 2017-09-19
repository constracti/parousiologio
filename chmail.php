<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . SITE_URL );
	exit;
}

$field_success = TRUE;
$fields = [
	'email_address' => new field( 'email_address', [
		'type' => 'email',
		'placeholder' => 'καινούρια διεύθυνση email',
		'required' => TRUE,
		'value' => $cuser->email_address,
	] ),
];

$field_success && ( function( array $fields ) {
	global $cuser;
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return;
	$email_address = $fields['email_address']->value();
	$user = user::select_by( 'email_address', $email_address );
	if ( !is_null( $user ) && $user->role_id === user::ROLE_UNVER ) {
		$user->delete();
		$user = NULL;
	}
	if ( !is_null( $user ) ) {
		if ( $user->user_id === $cuser->user_id )
			return page_message_add( 'Η καινούρια διεύθυνση email είναι ίδια με την παλιά.', 'error' );
		else
			return page_message_add( 'Υπάρχει άλλος εγγεγραμμένος χρήστης με αυτή τη διεύθυνση email.', 'error' );
	}
	$vlink = vlink::write( $cuser->user_id, 'chmail', $email_address );
	require_once SITE_DIR . 'php/mailer.php';
	$mail = new mailer();
	$mail->addAddress( $email_address );
	$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'αλλαγή διεύθυνσης email' );
	$mail->msgHTML( implode( mailer::NEWLINE, [
		sprintf( '<p>Για να επαναφέρεις τη διεύθυνση email του λογαριασμού σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', SITE_URL, SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'αλλαγή διεύθυνσης email' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	return page_message_add( 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να αλλάξεις τη διεύθυνση email του λογαριασμού σου.', 'success' );
} )( $fields );

page_title_set( 'Διεύθυνση email' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>settings.php" title="ρυθμίσεις">
	<span class="fa fa-cog"></span>
	<span class="w3-hide-small">ρυθμίσεις</span>
</a>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>chmail.php" title="διεύθυνση email">
	<span class="fa fa-envelope"></span>
	<span class="w3-hide-small w3-hide-medium">διεύθυνση email</span>
</a>
<?php
} );

page_body_add( 'form_html', $fields );

page_html();