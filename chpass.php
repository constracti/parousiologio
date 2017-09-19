<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . SITE_URL );
	exit;
}

$field_success = TRUE;
$fields = [
	'password' => new field( 'password', [
		'type' => 'password',
		'placeholder' => 'καινούριος κωδικός πρόσβασης',
		'required' => TRUE,
	] ),
];

$field_success && ( function( array $fields ) {
	global $cuser;
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return;
	$password = $fields['password']->value();
	$cuser->password_hash = password_hash( $password, PASSWORD_DEFAULT );
	$cuser->update();
	return page_message_add( 'Ο κωδικός πρόσβασης άλλαξε.', 'success' );
} )( $fields );

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

page_body_add( 'form_html', $fields );

page_html();