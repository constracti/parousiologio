<?php

require_once 'php/page.php';

function index_login_html() {
?>
<section class="w3-panel w3-content">
	<div class="w3-container w3-card-4 w3-round w3-theme-l4">
		<h3>είσοδος με λογαριασμό κοινωνικής δικτύωσης</h3>
		<div class="w3-section">
			<a class="w3-button w3-round w3-red" href="<?= HOME_URL ?>oauth2.php?provider=google&login">
				<span class="fa fa-google-plus"></span>
				<span class="w3-hide-small">Google</span>
			</a>
			<a class="w3-button w3-round w3-green" href="<?= HOME_URL ?>oauth2.php?provider=microsoft&login">
				<span class="fa fa-windows"></span>
				<span class="w3-hide-small">Microsoft</span>
			</a>
			<a class="w3-button w3-round w3-purple" href="<?= HOME_URL ?>oauth2.php?provider=yahoo&login">
				<span class="fa fa-yahoo"></span>
				<span class="w3-hide-small">Yahoo</span>
			</a>
		</div>
	</div>
</section>
<section class="w3-panel w3-content">
	<form class="w3-container w3-card-4 w3-round w3-theme-l4" method="post">
		<h3>είσοδος με τοπικό λογαριασμό</h3>
		<div class="w3-section">
			<label>διεύθυνση email *</label>
			<input class="w3-input" name="email_address" type="email" required="required" placeholder="διεύθυνση email" value="<?= $_POST['email_address'] ?? '' ?>" />
		</div>
		<div class="w3-section">
			<label>κωδικός πρόσβασης *</label>
			<input class="w3-input" name="password" type="password" required="required" placeholder="κωδικός πρόσβασης" />
		</div>
		<div class="w3-section">
			<button class="w3-button w3-round w3-theme" type="submit">
				<span class="fa fa-sign-in"></span>
				<span>είσοδος</span>
			</button>
		</div>
		<hr />
		<div class="w3-section w3-clear">
			<a class="w3-small w3-left" href="<?= HOME_URL ?>register.php" title="εγγραφή">δεν έχω εγγραφεί</a>
			<a class="w3-small w3-right" href="<?= HOME_URL ?>repass.php" title="επαναφορά κωδικού πρόσβασης">ξέχασα τον κωδικό μου</a>
		</div>
	</form>
</section>
<?php
}

if ( is_null( $cuser ) ) {
	( function() {
		if ( !array_key_exists( 'email_address', $_POST ) || !array_key_exists( 'password', $_POST ) )
			return;
		$email_address = filter_var( $_POST['email_address'], FILTER_VALIDATE_EMAIL );
		if ( $email_address === FALSE )
			return page_message_add( 'Πληκτρολόγησε μία έγκυρη διεύθυνση email.', 'error' );
		$user = user::select_by( 'email_address', $email_address );
		if ( is_null( $user ) )
			return page_message_add( 'Πραγματοποίησε εγγραφή ή συνδέσου με λογαριασμό κοινωνικής δικτύωσης.', 'error' );
		if ( is_null( $user->password_hash ) )
			return page_message_add( 'Δεν έχεις ορίσει κωδικό. Συνδέσου με λογαριασμό κοινωνικής δικτύωσης.', 'warning' );
		if ( !password_verify( $_POST['password'], $user->password_hash ) )
			return page_message_add( 'Πληκτρολόγησε το σωστό κωδικό πρόσβασης.', 'error' );
		if ( $user->role_id === user::ROLE_UNVER )
			return page_message_add( 'Ακολούθησε πρώτα το σύνδεσμο επαλήθευσης από τα εισερχόμενά σου.', 'error' );
		epoint::write( $user->user_id );
	} )();
	page_title_set( 'Είσοδος' );
	page_body_add( 'index_login_html' );
} else {
	page_nav_add( 'season_dropdown' );
}

page_html();
