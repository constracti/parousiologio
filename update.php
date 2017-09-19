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

$child = child::request( 'child_id' );
if ( !$team->has_child( $child->child_id ) )
	failure();

$follow = follow::select( [
	'child_id' => $child->child_id,
	'season_id' => $team->season_id,
] );
$follow = array_values( $follow )[ 0 ];

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
		'data-year' => $cyear,
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
];

$field_success && ( function( array $fields ) {
	global $child;
	global $follow;
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return;
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
	$child->update();
	$follow->grade_id = $fields['grade_id']->value();
	$follow->update();
	return page_message_add( 'Η εγγραφή παιδιού ενημερώθηκε.', 'success' );
} )( $fields );

page_title_set( 'Επεξεργασία' );

page_script_add( SITE_URL . 'js/birth_year.js' );

page_nav_add( 'season_dropdown' );
page_nav_add( function() {
	global $mode;
	global $team;
	global $child;
?>
			<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>presences.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" title="παρουσίες">
				<span class="fa fa-check-square"></span>
				<span class="w3-hide-small w3-hide-medium">παρουσίες</span>
			</a>
			<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>update.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>&child_id=<?= $child->child_id ?>" title="επεξεργασία">
				<span class="fa fa-pencil"></span>
				<span class="w3-hide-small w3-hide-medium">επεξεργασία</span>
			</a>
<?php
} );

page_body_add( 'form_html', $fields, [
	'full_screen' => TRUE,
	'responsive' => 'w3-col l3 m6 s12',
	'delete' => sprintf( '%sdelete.php?mode=%s&team_id=%d&child_id=%d', SITE_URL, $mode, $team->team_id, $child->child_id ),
] );

page_html();