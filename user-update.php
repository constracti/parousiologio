<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$user = user::request();

$roles = user::ROLES;
if ( $user->role === user::ROLE_UNVER )
	$roles = array_intersect_key( $roles, array_fill_keys( [ user::ROLE_UNVER ], NULL ) );
else
	unset( $roles[ user::ROLE_UNVER ] );
foreach ( $roles as $role => $text )
	if ( $role > $cuser->role )
		unset( $roles[ $role ] );

$fields = [
	'last_name' => new field( 'last_name', [
		'placeholder' => 'επώνυμο',
		'value' => $user->last_name,
	] ),
	'first_name' => new field( 'first_name', [
		'placeholder' => 'όνομα',
		'value' => $user->first_name,
	] ),
	'home_phone' => new field_phone( 'home_phone', [
		'placeholder' => 'σταθερό τηλέφωνο',
		'value' => $user->home_phone,
	] ),
	'mobile_phone' => new field_phone( 'mobile_phone', [
		'placeholder' => 'κινητό τηλέφωνο',
		'value' => $user->mobile_phone,
	] ),
	'occupation' => new field( 'occupation', [
		'placeholder' => 'απασχόληση',
		'value' => $user->occupation,
	] ),
	'first_year' => new field_year( 'first_year', [
		'placeholder' => 'πρώτο έτος διακονίας',
		'value' => $user->first_year,
	] ),
	'address' => new field( 'address', [
		'placeholder' => 'διεύθυνση',
		'value' => $user->address,
	] ),
	'city' => new field( 'city', [
		'placeholder' => 'πόλη',
		'value' => $user->city,
	] ),
	'postal_code' => new field_pc( 'postal_code', [
		'placeholder' => 'ταχυδρομικός κώδικας',
		'value' => $user->postal_code,
	] ),
	'role' => new field_select( 'role', $roles, [
		'placeholder' => 'δικαιώματα',
		'required' => TRUE,
		'value' => $user->role,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$user->last_name = $fields['last_name']->post();
	$user->first_name = $fields['first_name']->post();
	$user->home_phone = $fields['home_phone']->post();
	$user->mobile_phone = $fields['mobile_phone']->post();
	$user->occupation = $fields['occupation']->post();
	$user->first_year = $fields['first_year']->post();
	$user->address = $fields['address']->post();
	$user->city = $fields['city']->post();
	$user->postal_code = $fields['postal_code']->post();
	$user->role = $fields['role']->post();
	$user->update();
	success( [
		'alert' => 'Ο χρήστης ενημερώθηκε.',
	] );
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία χρήστη' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'users.php' ),
	'text' => 'χρήστες',
	'icon' => 'fa-user',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'user-update.php', [ 'user_id' => $user->user_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( 'form_section', $fields, [
	'responsive' => 'w3-col m6 s12',
	'delete' => site_href( 'user-delete.php', [ 'user_id' => $user->user_id ] ),
] );


/********
 * exit *
 ********/

page_html();