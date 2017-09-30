<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

$fields = [
	'password' => new field_password( 'password', [
		'placeholder' => 'καινούριος κωδικός πρόσβασης',
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$password = $fields['password']->post();
	$cuser->password_hash = !is_null( $password ) ? password_hash( $password, PASSWORD_DEFAULT ) : NULL;
	$cuser->update();
	success( [
		'alert' => 'Ο κωδικός πρόσβασης άλλαξε.',
		'location' => site_href( 'settings.php' ),
	] );
}

page_title_set( 'Κωδικός πρόσβασης' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'settings.php' ),
	'text' => 'ρυθμίσεις',
	'icon' => 'fa-cog',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'chpass.php' ),
	'text' => 'κωδικός πρόσβασης',
	'icon' => 'fa-lock',
] );

page_body_add( 'form_section', $fields );

page_html();