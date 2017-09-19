<?php

require_once 'php/page.php';

if ( is_null( $cuser ) )
	failure();

$mode = request_var( 'mode' );
if ( !in_array( $mode, [ 'desktop', 'mobile' ] ) )
	failure();

$team = team::request( 'team_id' );
if ( !$cuser->has_team( $team->team_id ) )
	failure();

$grades = ( function( $grades ) {
	$options = [];
	foreach ( $grades as $grade )
		$options[ $grade->grade_id ] = $grade->grade_name;
	return $options;
} ) ( $team->get_grades() );

$field_success = TRUE;
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

$field_success && ( function( array $fields ) {
	global $mode;
	global $team;
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return;
	$child = new child();
	$follow = new follow();
	$child->last_name = $fields['last_name']->value();
	$child->first_name = $fields['first_name']->value();
	$child->home_phone = $fields['home_phone']->value();
	$child->mobile_phone = $fields['mobile_phone']->value();
	$child->email_address = $fields['email_address']->value();
	$child->school = $fields['school']->value();
	$child->birth_year = $fields['birth_year']->value();
	$child->fath_name = $fields['fath_name']->value();
	$child->fath_mobile = $fields['fath_mobile']->value();
	$child->fath_occup = $fields['fath_occup']->value();
	$child->fath_email = $fields['fath_email']->value();
	$child->moth_name = $fields['moth_name']->value();
	$child->moth_mobile = $fields['moth_mobile']->value();
	$child->moth_occup = $fields['moth_occup']->value();
	$child->moth_email = $fields['moth_email']->value();
	$child->address = $fields['address']->value();
	$child->city = $fields['city']->value();
	$child->postal_code = $fields['postal_code']->value();
	$child->insert();
	$follow->child_id = $child->child_id;
	$follow->season_id = $team->season_id;
	$follow->grade_id = $fields['grade_id']->value();
	$follow->location_id = $team->location_id;
	$follow->insert();
	header( 'location: ' . sprintf( '%supdate.php?mode=%s&team_id=%d&child_id=%d', SITE_URL, $mode, $team->team_id, $child->child_id ) );
	exit;
} )( $fields );

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

page_body_add( 'form_html', $fields, [
	'full_screen' => TRUE,
	'responsive' => 'w3-col l3 m6 s12',
] );

page_html();