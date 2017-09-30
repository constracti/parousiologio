<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$location = location::request();

$fields = [
	'location_name' => new field( 'location_name', [
		'placeholder' => 'όνομα',
		'required' => TRUE,
		'value' => $location->location_name,
	] ),
	'is_swarm' => new field_radio( 'is_swarm', [
		0 => 'κατηχητικό',
		1 => 'ομάδα',
	], [
		'value' => $location->is_swarm,
		'required' => TRUE,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$location->location_name = $fields['location_name']->post();
	$location->is_swarm = $fields['is_swarm']->post();
	$location->update();
	success( [
		'alert' => 'Η περιοχή ενημερώθηκε.',
	] );
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία περιοχής' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'locations.php' ),
	'text' => 'περιοχές',
	'icon' => 'fa-globe',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'location-update.php', [ 'location_id' => $location->location_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( 'form_section', $fields, [
	'delete' => site_href( 'location-delete.php', [ 'location_id' => $location->location_id ] ),
] );


/********
 * exit *
 ********/

page_html();