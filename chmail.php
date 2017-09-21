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
		sprintf( '<p>Για να επαναφέρεις τη διεύθυνση email του λογαριασμού σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', SITE_URL, SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'αλλαγή διεύθυνσης email' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	success( [
		'alert' => 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να αλλάξεις τη διεύθυνση email του λογαριασμού σου.',
		'location' => SITE_URL . 'settings.php',
	] );
}

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

page_body_add( 'form_section', $fields );

page_html();