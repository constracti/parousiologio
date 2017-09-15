<?php

require_once 'php/page.php';

logout();

( function() {
	if ( !array_key_exists( 'email_address', $_POST ) )
		return;
	if ( !array_key_exists( 'password', $_POST ) )
		return;
	$email_address = filter_var( $_POST['email_address'], FILTER_VALIDATE_EMAIL );
	if ( $email_address === FALSE )
		return page_message_add( 'Πληκτρολόγησε μία έγκυρη διεύθυνση email.', 'error' );
	$user = user::select_by( 'email_address', $email_address );
	if ( is_null( $user ) )
		return page_message_add( 'Δεν υπάρχει εγγεγραμμένος χρήστης με αυτή τη διεύθυνση email.', 'error' );
	if ( $user->role_id === user::ROLE_UNVER )
		return page_message_add( 'Ακολούθησε πρώτα τον σύνδεσμο επαλήθευσης από τα εισερχόμενά σου.', 'error' );
	$vlink = vlink::write( $user->user_id, 'repass', password_hash( $_POST['password'], PASSWORD_DEFAULT ) );
	require_once SITE_DIR . 'php/mailer.php';
	$mail = new mailer();
	$mail->addAddress( $user->email_address );
	$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'επαναφορά κωδικού πρόσβασης' );
	$mail->msgHTML( implode( mailer::NEWLINE, [
		sprintf( '<p>Για να επαναφέρεις τον κωδικό πρόσβασης του λογαριασμού σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', HOME_URL, SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'επαναφορά κωδικού πρόσβασης' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	return page_message_add( 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να επαναφέρεις τον κωδικό πρόσβασης του λογαριασμού σου.', 'success' );
} )();

page_title_set( 'Επαναφορά κωδικού πρόσβασης' );

page_body_add( function() {
?>
<section class="w3-panel w3-content">
	<form class="w3-container w3-card-4 w3-round w3-theme-l4" method="post">
		<div class="w3-section">
			<label>διεύθυνση email *</label>
			<input class="w3-input" name="email_address" type="email" required="required" placeholder="διεύθυνση email" value="<?= $_POST['email_address'] ?? '' ?>" />
		</div>
		<div class="w3-section">
			<label>καινούριος κωδικός πρόσβασης *</label>
			<input class="w3-input" name="password" type="password" required="required" placeholder="καινούριος κωδικός πρόσβασης" />
		</div>
		<div class="w3-section">
			<button class="w3-button w3-round w3-theme-action" type="submit">
				<span class="fa fa-lock"></span>
				<span>επαναφορά</span>
			</button>
		</div>
	</form>
</section>
<?php
} );

page_html();