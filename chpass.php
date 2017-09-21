<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

$fields = [
	'password' => new field_password( 'password', [
		'placeholder' => 'καινούριος κωδικός πρόσβασης',
		'required' => TRUE,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$password = $fields['password']->post();
	$cuser->password_hash = password_hash( $password, PASSWORD_DEFAULT );
	$cuser->update();
	success( [
		'alert' => 'Ο κωδικός πρόσβασης άλλαξε.',
		'location' => SITE_URL . 'settings.php',
	] );
}

page_title_set( 'Κωδικός πρόσβασης' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>settings.php" title="ρυθμίσεις">
	<span class="fa fa-cog"></span>
	<span class="w3-hide-small">ρυθμίσεις</span>
</a>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>chpass.php" title="κωδικός πρόσβασης">
	<span class="fa fa-lock"></span>
	<span class="w3-hide-small w3-hide-medium">κωδικός πρόσβασης</span>
</a>
<?php
} );

page_body_add( 'form_section', $fields );

page_html();