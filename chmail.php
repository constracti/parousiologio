<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . HOME_URL );
	exit;
}

( function() {
	global $cuser;
	if ( !array_key_exists( 'email_address', $_POST ) )
		return;
	$email_address = filter_var( $_POST['email_address'], FILTER_VALIDATE_EMAIL );
	if ( $email_address === FALSE )
		return page_message_add( 'Πληκτρολόγησε μία έγκυρη διεύθυνση email.', 'error' );
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
		sprintf( '<p>Για να επαναφέρεις τη διεύθυνση email του λογαριασμού σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', HOME_URL, SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'αλλαγή διεύθυνσης email' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	return page_message_add( 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να αλλάξεις τη διεύθυνση email του λογαριασμού σου.', 'success' );
} )();

page_title_set( 'Διεύθυνση email' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>settings.php" title="ρυθμίσεις">
	<span class="fa fa-cog"></span>
	<span class="w3-hide-small">ρυθμίσεις</span>
</a>
<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>chmail.php" title="διεύθυνση email">
	<span class="fa fa-envelope"></span>
	<span class="w3-hide-small w3-hide-medium">διεύθυνση email</span>
</a>
<?php
} );

page_body_add( function() {
	global $cuser;
?>
<section class="w3-panel w3-content">
	<form class="w3-container w3-card-4 w3-round w3-theme-l4" method="post">
		<div class="w3-section">
			<label>καινούρια διεύθυνση email *</label>
			<input class="w3-input" name="email_address" type="email" required="required" placeholder="καινούρια διεύθυνση email" value="<?= $_POST['email_address'] ?? $cuser->email_address ?? '' ?>" />
		</div>
		<div class="w3-section">
			<button class="w3-button w3-round w3-theme-action" type="submit">
				<span class="fa fa-floppy-o"></span>
				<span>αποθήκευση</span>
			</button>
		</div>
	</form>
</section>
<?php
} );

page_html();