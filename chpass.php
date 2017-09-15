<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . HOME_URL );
	exit;
}

( function() {
	global $cuser;
	if ( !array_key_exists( 'password', $_POST ) )
		return;
	$cuser->password_hash = password_hash( $_POST['password'], PASSWORD_DEFAULT );
	$cuser->update();
	return page_message_add( 'Ο κωδικός πρόσβασης άλλαξε.', 'success' );
} )();

page_title_set( 'Κωδικός πρόσβασης' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>settings.php" title="ρυθμίσεις">
	<span class="fa fa-cog"></span>
	<span class="w3-hide-small">ρυθμίσεις</span>
</a>
<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>chpass.php" title="κωδικός πρόσβασης">
	<span class="fa fa-lock"></span>
	<span class="w3-hide-small w3-hide-medium">κωδικός πρόσβασης</span>
</a>
<?php
} );

page_body_add( function() {
	global $cuser;
?>
<section class="w3-panel w3-content">
	<form class="w3-container w3-card-4 w3-round w3-theme-l4" method="post">
		<div class="w3-section">
			<label>καινούριος κωδικός πρόσβασης *</label>
			<input class="w3-input" name="password" type="password" required="required" placeholder="καινούριος κωδικός πρόσβασης" />
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