<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$season = season::request();

$fields = [
	'year' => new field_year( 'year', [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $season->year,
	] ),
	'slogan_old' => new field( 'slogan_old', [
		'placeholder' => 'σύνθημα Χαρούμενων Αγωνιστών',
		'value' => $season->slogan_old,
	] ),
	'source' => new field( 'source', [
		'placeholder' => 'πηγή',
		'value' => $season->source,
	] ),
	'slogan_new' => new field( 'slogan_new', [
		'placeholder' => 'σύνθημα Χελιδονιών',
		'value' => $season->slogan_new,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$season->year = $fields['year']->post();
	$season->slogan_old = $fields['slogan_old']->post();
	$season->source = $fields['source']->post();
	$season->slogan_new = $fields['slogan_new']->post();
	$seasons = season::select( [
		'year' => $season->year,
	] );
	if ( count( $seasons ) > 0 && !array_key_exists( $season->season_id, $seasons ) )
		failure( 'Το έτος υπάρχει ήδη.' );
	$season->update();
	success( [
		'alert' => 'Το έτος ενημερώθηκε.',
	] );
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία έτους' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'seasons.php' ),
	'text' => 'έτη',
	'icon' => 'fa-calendar',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'season-update.php', [ 'season_id' => $season->season_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( 'form_section', $fields, [
	'delete' => site_href( 'season-delete.php', [ 'season_id' => $season->season_id ] ),
] );


/********
 * exit *
 ********/

page_html();