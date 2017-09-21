<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$mode = request_var( 'mode' );
if ( !in_array( $mode, [ 'desktop', 'mobile' ] ) )
	failure( 'argument_not_valid', 'mode' );

$team = team::request( 'team_id' );
if ( !$cuser->has_team( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

$grades = ( function( $grades ) {
	$options = [];
	foreach ( $grades as $grade )
		$options[ $grade->grade_id ] = $grade->grade_name;
	return $options;
} ) ( $team->get_grades() );

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
		'data-year' => $cyear,
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
	$child->insert();
	$follow->child_id = $child->child_id;
	$follow->season_id = $team->season_id;
	$follow->grade_id = $fields['grade_id']->post();
	$follow->location_id = $team->location_id;
	$follow->insert();
	success( [
		'alert' => 'Η εγγραφή παιδιού προστέθηκε.',
		'location' => SITE_URL . sprintf( 'update.php?mode=%s&team_id=%d&child_id=%d', $mode, $team->team_id, $child->child_id ),
	] );
}

page_title_set( 'Προσθήκη' );

page_script_add( SITE_URL . 'js/birth_year.js' );

page_nav_add( 'season_dropdown' );
page_nav_add( function() {
	global $mode;
	global $team;
?>
			<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>presences.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" title="παρουσίες">
				<span class="fa fa-check-square"></span>
				<span class="w3-hide-small w3-hide-medium">παρουσίες</span>
			</a>
			<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>insert.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" title="προσθήκη">
				<span class="fa fa-plus"></span>
				<span class="w3-hide-small w3-hide-medium">προσθήκη</span>
			</a>
<?php
} );

page_body_add( 'form_section', $fields, [
	'full_screen' => TRUE,
	'responsive' => 'w3-col l3 m6 s12',
] );

page_html();