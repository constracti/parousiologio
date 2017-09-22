<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$fields = [
	'location_id' => new field_select( 'location_id', location::select_options(), [
		'placeholder' => 'περιοχή',
		'required' => TRUE,
	] ),
	'team_name' => new field( 'team_name', [
		'placeholder' => 'όνομα',
		'required' => TRUE,
	] ),
	'season_id' => new field_select( 'season_id', season::select_options(), [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $cseason->season_id,
	] ),
	'on_sunday' => new field_radio( 'on_sunday', [
		0 => 'Σάββατο',
		1 => 'Κυριακή',
	], [
		'value' => 1,
		'required' => TRUE,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$team = new team();
	$team->location_id = $fields['location_id']->post();
	$team->team_name = $fields['team_name']->post();
	$team->season_id = $fields['season_id']->post();
	$team->on_sunday = $fields['on_sunday']->post();
	$team->insert();
	success( [
		'alert' => 'Η ομάδα προστέθηκε.',
		'location' => SITE_URL . sprintf( 'team-update.php?team_id=%d', $team->team_id ),
	] );
}


/********
 * main *
 ********/

page_title_set( 'Προσθήκη ομάδας' );

page_nav_add( 'season_dropdown', [
	'href' => 'teams.php',
	'text' => 'ομάδες',
	'icon' => 'fa-users',
] );

page_nav_add( 'bar_link', [
	'href' => season_href( 'team-insert.php' ),
	'text' => 'προσθήκη',
	'icon' => 'fa-plus',
] );

page_body_add( 'form_section', $fields );


/********
 * exit *
 ********/

page_html();