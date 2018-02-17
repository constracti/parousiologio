<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$team = team::request();
if ( !$cuser->accesses( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

$child = child::request();
if ( !$team->has_child( $child->child_id ) )
	failure( 'argument_not_valid', 'child_id' );

$follows = follow::select( [
	'child_id' => $child->child_id,
	'season_id' => $team->season_id,
] );
$follow = array_shift( $follows );

$grades = ( function( $grades ) {
	$options = [];
	foreach ( $grades as $grade )
		$options[ $grade->grade_id ] = $grade->grade_name;
	return $options;
} ) ( $team->select_grades() );

$fields = [
	'last_name' => new field( 'last_name', [
		'placeholder' => 'επώνυμο',
		'value' => $child->last_name,
		'required' => TRUE,
	] ),
	'first_name' => new field( 'first_name', [
		'placeholder' => 'όνομα',
		'value' => $child->first_name,
		'required' => TRUE,
	] ),
	'home_phone' => new field_phone( 'home_phone', [
		'placeholder' => 'σταθερό τηλέφωνο',
		'value' => $child->home_phone,
	] ),
	'mobile_phone' => new field_phone( 'mobile_phone', [
		'placeholder' => 'κινητό τηλέφωνο',
		'value' => $child->mobile_phone,
	] ),
	'email_address' => new field_email( 'email_address', [
		'placeholder' => 'διεύθυνση email',
		'value' => $child->email_address,
	] ),
	'school' => new field( 'school', [
		'placeholder' => 'σχολείο',
		'value' => $child->school,
	] ),
	'grade_id' => new field_select( 'grade_id', $grades, [
		'placeholder' => 'τάξη',
		'required' => TRUE,
		'value' => $follow->grade_id,
		'data-year' => $cseason->year,
	] ),
	'birth_year' => new field_year( 'birth_year', [
		'placeholder' => 'έτος γέννησης',
		'value' => $child->birth_year,
	] ),
	'fath_name' => new field( 'fath_name', [
		'placeholder' => 'όνομα πατρός',
		'value' => $child->fath_name,
	] ),
	'fath_mobile' => new field_phone( 'fath_mobile', [
		'placeholder' => 'κινητό πατρός',
		'value' => $child->fath_mobile,
	] ),
	'fath_occup' => new field( 'fath_occup', [
		'placeholder' => 'επάγγελμα πατρός',
		'value' => $child->fath_occup,
	] ),
	'fath_email' => new field_email( 'fath_email', [
		'placeholder' => 'email πατρός',
		'value' => $child->fath_email,
	] ),
	'moth_name' => new field( 'moth_name', [
		'placeholder' => 'όνομα μητρός',
		'value' => $child->moth_name,
	] ),
	'moth_mobile' => new field_phone( 'moth_mobile', [
		'placeholder' => 'κινητό μητρός',
		'value' => $child->moth_mobile,
	] ),
	'moth_occup' => new field( 'moth_occup', [
		'placeholder' => 'επάγγελμα μητρός',
		'value' => $child->moth_occup,
	] ),
	'moth_email' => new field_email( 'moth_email', [
		'placeholder' => 'email μητρός',
		'value' => $child->moth_email,
	] ),
	'address' => new field( 'address', [
		'placeholder' => 'διεύθυνση',
		'value' => $child->address,
	] ),
	'city' => new field( 'city', [
		'placeholder' => 'πόλη',
		'value' => $child->city,
	] ),
	'postal_code' => new field_pc( 'postal_code', [
		'placeholder' => 'ταχυδρομικός κώδικας',
		'value' => $child->postal_code,
	] ),
	'meta_mobile' => new field_select( 'meta_mobile', [
		'self' => 'παιδιού',
		'fath' => 'πατρός',
		'moth' => 'μητρός',
	], [
		'placeholder' => 'κινητό ενημέρωσης',
		'value' => $child->get_meta( 'mobile' ),
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
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
	$child->update();
	$follow->grade_id = $fields['grade_id']->post();
	$follow->update();
	success( [
		'alert' => 'Η εγγραφή παιδιού ενημερώθηκε.',
	] );
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία' );

page_script_add( site_href( 'js/birth_year.js' ) );

page_nav_add( 'season_dropdown' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'presences.php', [ 'team_id' => $team->team_id ] ),
	'text' => 'παρουσίες',
	'icon' => 'fa-check-square',
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'update.php', [ 'team_id' => $team->team_id, 'child_id' => $child->child_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( 'form_section', $fields, [
	'full_screen' => TRUE,
	'responsive' => 'w3-col l3 m6 s12',
	'delete' => site_href( 'delete.php', [ 'team_id' => $team->team_id, 'child_id' => $child->child_id ] ),
] );


/********
 * exit *
 ********/

page_html();
