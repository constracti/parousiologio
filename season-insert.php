<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$fields = [
	'year' => new field_year( 'year', [
		'placeholder' => 'έτος',
		'required' => TRUE,
	] ),
	'slogan_old' => new field( 'slogan_old', [
		'placeholder' => 'σύνθημα Χαρούμενων Αγωνιστών',
	] ),
	'source' => new field( 'source', [
		'placeholder' => 'πηγή',
	] ),
	'slogan_new' => new field( 'slogan_new', [
		'placeholder' => 'σύνθημα Χελιδονιών',
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$season = new season();
	$season->year = $fields['year']->post();
	$season->slogan_old = $fields['slogan_old']->post();
	$season->source = $fields['source']->post();
	$season->slogan_new = $fields['slogan_new']->post();
	$seasons = season::select( [
		'year' => $season->year,
	] );
	if ( count( $seasons ) > 0 )
		failure( 'Το έτος υπάρχει ήδη.' );
	failure( 'TODO advance year' );
	$season->insert();
	success( [
		'alert' => 'Το έτος προστέθηκε.',
		'location' => site_href( 'season-update.php', [ 'season_id' => $season->season_id ] ),
	] );
}


/********
 * main *
 ********/

page_title_set( 'Προσθήκη έτους' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'seasons.php' ),
	'text' => 'έτη',
	'icon' => 'fa-calendar',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'season-insert.php' ),
	'text' => 'προσθήκη',
	'icon' => 'fa-plus',
] );

page_body_add( 'form_section', $fields );


/********
 * exit *
 ********/

page_html();