<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

$fields = [
	'last_name' => new field( 'last_name', [
		'placeholder' => 'επώνυμο',
		'value' => $cuser->last_name,
	] ),
	'first_name' => new field( 'first_name', [
		'placeholder' => 'όνομα',
		'value' => $cuser->first_name,
	] ),
	'mobile_phone' => new field_phone( 'mobile_phone', [
		'placeholder' => 'τηλέφωνο',
		'value' => $cuser->mobile_phone,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$cuser->last_name = $fields['last_name']->post();
	$cuser->first_name = $fields['first_name']->post();
	$cuser->mobile_phone = $fields['mobile_phone']->post();
	$cuser->update();
	success( [
		'alert' => 'Το προφίλ ενημερώθηκε.',
	] );
}

page_title_set( 'Προφίλ' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'profile.php' ),
	'text' => 'προφίλ',
	'icon' => 'fa-pencil',
	'hide_medium' => FALSE,
] );

if ( !$cuser->has_gravatar() )
	page_body_add( function() {
		echo '<section class="w3-panel w3-content">' . "\n";
		echo '<p>' . "\n";
		echo '<span class="fa fa-info-circle"></span>' . "\n";
		echo '<span>Μπες στο <a href="https://el.gravatar.com/" target="_blank" title="Γενικά αναγνωρισμένο Άβαταρ">gravatar.com</a> και όρισε την εικόνα του προφίλ σου!</span>' . "\n";
		echo '</p>' . "\n";
		echo '</section>' . "\n";
	} );

page_body_add( 'form_section', $fields, [
	'responsive' => 'w3-col m6 s12',
] );

page_html();
