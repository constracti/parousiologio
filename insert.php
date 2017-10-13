<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$team = team::request();
if ( !$cuser->accesses( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

$grades = ( function( $grades ) {
	$options = [];
	foreach ( $grades as $grade )
		$options[ $grade->grade_id ] = $grade->grade_name;
	return $options;
} ) ( $team->select_grades() );

$fields = [
	'last_name' => new field( 'last_name', [
		'placeholder' => 'επώνυμο',
		'required' => TRUE,
	] ),
	'first_name' => new field( 'first_name', [
		'placeholder' => 'όνομα',
		'required' => TRUE,
	] ),
	'home_phone' => new field_phone( 'home_phone', [
		'placeholder' => 'σταθερό τηλέφωνο',
	] ),
	'mobile_phone' => new field_phone( 'mobile_phone', [
		'placeholder' => 'κινητό τηλέφωνο',
	] ),
	'email_address' => new field_email( 'email_address', [
		'placeholder' => 'διεύθυνση email',
	] ),
	'school' => new field( 'school', [
		'placeholder' => 'σχολείο',
	] ),
	'grade_id' => new field_select( 'grade_id', $grades, [
		'placeholder' => 'τάξη',
		'required' => TRUE,
		'data-year' => $cseason->year,
	] ),
	'birth_year' => new field_year( 'birth_year', [
		'placeholder' => 'έτος γέννησης',
	] ),
	'fath_name' => new field( 'fath_name', [
		'placeholder' => 'όνομα πατρός',
	] ),
	'fath_mobile' => new field_phone( 'fath_mobile', [
		'placeholder' => 'κινητό πατρός',
	] ),
	'fath_occup' => new field( 'fath_occup', [
		'placeholder' => 'επάγγελμα πατρός',
	] ),
	'fath_email' => new field_email( 'fath_email', [
		'placeholder' => 'email πατρός',
	] ),
	'moth_name' => new field( 'moth_name', [
		'placeholder' => 'όνομα μητρός',
	] ),
	'moth_mobile' => new field_phone( 'moth_mobile', [
		'placeholder' => 'κινητό μητρός',
	] ),
	'moth_occup' => new field( 'moth_occup', [
		'placeholder' => 'επάγγελμα μητρός',
	] ),
	'moth_email' => new field_email( 'moth_email', [
		'placeholder' => 'email μητρός',
	] ),
	'address' => new field( 'address', [
		'placeholder' => 'διεύθυνση',
	] ),
	'city' => new field( 'city', [
		'placeholder' => 'πόλη',
	] ),
	'postal_code' => new field_pc( 'postal_code', [
		'placeholder' => 'ταχυδρομικός κώδικας',
	] ),
	'meta_mobile' => new field_select( 'meta_mobile', [
		'self' => 'παιδιού',
		'fath' => 'πατρός',
		'moth' => 'μητρός',
	], [
		'placeholder' => 'κινητό ενημέρωσης',
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$child = new child();
	$follow = new follow();
	$child->last_name = $fields['last_name']->post();
	$child->first_name = $fields['first_name']->post();
	$child->home_phone = $fields['home_phone']->post();
	$child->mobile_phone = $fields['mobile_phone']->post();
	$child->email_address = $fields['email_address']->post();
	$child->school = $fields['school']->post();
	$child->birth_year = $fields['birth_year']->post();
	$child->fath_name = $fields['fath_name']->post();
	$child->fath_mobile = $fields['fath_mobile']->post();
	$child->fath_occup = $fields['fath_occup']->post();
	$child->fath_email = $fields['fath_email']->post();
	$child->moth_name = $fields['moth_name']->post();
	$child->moth_mobile = $fields['moth_mobile']->post();
	$child->moth_occup = $fields['moth_occup']->post();
	$child->moth_email = $fields['moth_email']->post();
	$child->address = $fields['address']->post();
	$child->city = $fields['city']->post();
	$child->postal_code = $fields['postal_code']->post();
	$child->set_meta( 'mobile', $fields['meta_mobile']->post() );
	$child->insert();
	$follow->child_id = $child->child_id;
	$follow->season_id = $team->season_id;
	$follow->grade_id = $fields['grade_id']->post();
	$follow->location_id = $team->location_id;
	$follow->insert();
	success( [
		'alert' => 'Η εγγραφή παιδιού προστέθηκε.',
		'location' => site_href( 'update.php', [ 'team_id' => $team->team_id, 'child_id' => $child->child_id ] ),
	] );
}


/********
 * main *
 ********/

page_title_set( 'Προσθήκη' );

page_script_add( site_href( 'js/birth_year.js' ) );

page_nav_add( 'season_dropdown' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'presences.php', [ 'team_id' => $team->team_id ] ),
	'text' => 'παρουσίες',
	'icon' => 'fa-check-square',
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'insert.php', [ 'team_id' => $team->team_id ] ),
	'text' => 'προσθήκη',
	'icon' => 'fa-plus',
] );

page_body_add( 'form_section', $fields, [
	'full_screen' => TRUE,
	'responsive' => 'w3-col l3 m6 s12',
] );


/********
 * exit *
 ********/

page_html();