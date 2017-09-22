<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$fields = [
	'location_name' => new field( 'location_name', [
		'placeholder' => 'όνομα',
		'required' => TRUE,
	] ),
	'is_swarm' => new field_radio( 'is_swarm', [
		0 => 'κατηχητικό',
		1 => 'ομάδα',
	], [
		'value' => 1,
		'required' => TRUE,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$location = new location();
	$location->location_name = $fields['location_name']->post();
	$location->is_swarm = $fields['is_swarm']->post();
	$location->insert();
	success( [
		'alert' => 'Η περιοχή προστέθηκε.',
		'location' => SITE_URL . 'location-update.php?location_id=' . $location->location_id,
	] );
}


/********
 * main *
 ********/

page_title_set( 'Προσθήκη περιοχής' );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . 'locations.php',
	'text' => 'περιοχές',
	'icon' => 'fa-globe',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . 'location-insert.php',
	'text' => 'προσθήκη',
	'icon' => 'fa-plus',
] );

page_body_add( 'form_section', $fields );


/********
 * exit *
 ********/

page_html();