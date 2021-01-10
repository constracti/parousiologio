<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$child = child::request();

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
	'meta_comments' => new field( 'meta_comments', [
		'placeholder' => 'σχόλια',
		'value' => $child->get_meta('comments' ),
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
	$child->set_meta( 'comments', $fields['meta_comments']->post() );
	$child->update();
	success( [
		'alert' => 'Το παιδί ενημερώθηκε.',
	] );
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία παιδιού' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'children.php' ),
	'text' => 'παιδιά',
	'icon' => 'fa-child',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'child-update.php', [ 'child_id' => $child->child_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'follows.php', [ 'child_id' => $child->child_id ] ),
	'text' => 'έτη',
	'icon' => 'fa-calendar',
] );

page_body_add( 'form_section', $fields, [
	'full_screen' => TRUE,
	'responsive' => 'w3-col l3 m6 s12',
	'delete' => site_href( 'child-delete.php', [ 'child_id' => $child->child_id ] ),
] );


/********
 * exit *
 ********/

page_html();
