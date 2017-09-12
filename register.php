<?php

require_once 'php/page.php';

logout();

( function() {
	if ( !array_key_exists( 'email_address', $_POST ) )
		return;
	if ( !array_key_exists( 'password', $_POST ) )
		return;
	if ( !array_key_exists( 'last_name', $_POST ) )
		return;
	if ( !array_key_exists( 'first_name', $_POST ) )
		return;
	$email_address = filter_var( $_POST['email_address'], FILTER_VALIDATE_EMAIL );
	if ( $email_address === FALSE )
		return page_message_add( 'Πληκτρολόγησε μία έγκυρη διεύθυνση email.', 'error' );
	$user = user::select_by( 'email_address', $email_address );
	if ( !is_null( $user ) ) {
		if ( $user->role_id === user::ROLE_UNVER )
			$user->delete();
		else
			return page_message_add( 'Χρησιμοποίησε μία διαφορετική διεύθυνση email.', 'error' );
	}
	$user = new user();
	$user->email_address = $email_address;
	$user->password_hash = password_hash( $_POST['password'], PASSWORD_DEFAULT );
	$user->last_name = $_POST['last_name'] !== '' ? $_POST['last_name'] : NULL;
	$user->first_name = $_POST['first_name'] !== '' ? $_POST['first_name'] : NULL;
	$user->role_id = user::ROLE_UNVER;
	$user->reg_time = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
	$user->reg_ip = $_SERVER['REMOTE_ADDR'];
	$user->insert();
	$vlink = vlink::write( $user->user_id, 'register' );
	require_once HOME_DIR . 'php/mailer.php';
	$mail = new mailer();
	$mail->addAddress( $user->email_address );
	$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'εγγραφή' );
	$mail->msgHTML( implode( mailer::NEWLINE, [
		sprintf( '<p>Για να ολοκληρώσεις την εγγραφή σου στο <a href="%s">%s</a>, ακολούθησε τον παρακάτω σύνδεσμο:</p>', HOME_URL, SITE_NAME ),
		sprintf( '<p><a href="%s">%s</a></p>', $vlink->url(), 'εγγραφή' ),
		'<hr />',
		'<p><small>Αν η ενέργεια δεν προήλθε από εσένα, αγνόησε το παρόν μήνυμα.</small></p>',
	] ) );
	$mail->send();
	return page_message_add( 'Ακολούθησε το σύνδεσμο που εστάλη στα εισερχόμενά σου για να ολοκληρώσεις την εγγραφή σου.', 'success' );
} )();

page_title_set( 'Εγγραφή' );

page_body_add( function() {
?>
<section class="w3-panel w3-content">
	<form class="w3-card-4 w3-round w3-theme-l4" method="post">
		<div class="w3-row-padding">
			<div class="w3-margin-top w3-half">
				<label>επώνυμο *</label>
				<input class="w3-input" name="last_name" type="text" placeholder="επώνυμο" value="<?= $_POST['last_name'] ?? '' ?>" />
			</div>
			<div class="w3-margin-top w3-half">
				<label>όνομα *</label>
				<input class="w3-input" name="first_name" type="text" placeholder="όνομα" value="<?= $_POST['first_name'] ?? '' ?>" />
			</div>
			<div class="w3-margin-top w3-half">
				<label>διεύθυνση email *</label>
				<input class="w3-input" name="email_address" type="email" required="required" placeholder="διεύθυνση email" value="<?= $_POST['email_address'] ?? '' ?>" />
			</div>
			<div class="w3-margin-top w3-half">
				<label>κωδικός πρόσβασης *</label>
				<input class="w3-input" name="password" type="password" required="required" placeholder="κωδικός πρόσβασης" />
			</div>
		</div>
		<div class="w3-container">
			<div class="w3-section">
				<button class="w3-button w3-round w3-theme" type="submit">
					<span class="fa fa-power-off"></span>
					<span>εγγραφή</span>
				</button>
			</div>
		</div>
	</form>
</section>
<?php
} );

page_html();