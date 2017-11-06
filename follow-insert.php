<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$child = child::request();

$fields = [
	'season_id' => new field_select( 'season_id', season::select_options(), [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $cseason->season_id,
	] ),
	'grade_id' => new field_select( 'grade_id', grade::select_options(), [
		'placeholder' => 'τάξη',
		'required' => TRUE,
	] ),
	'location_id' => new field_select( 'location_id', location::select_options(), [
		'placeholder' => 'περιοχή',
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$follow = new follow();
	$follow->child_id = $child->child_id;
	$follow->season_id = $fields['season_id']->post();
	$follow->grade_id = $fields['grade_id']->post();
	$follow->location_id = $fields['location_id']->post();
	$follows = follow::select( [
		'child_id' => $follow->child_id,
		'season_id' => $follow->season_id,
	] );
	if ( count( $follows ) > 0 )
		failure( 'Το έτος παιδιού υπάρχει ήδη.' );
	$follow->insert();
	success( [
		'alert' => 'Το έτος παιδιού προστέθηκε.',
		'location' => site_href( 'follow-update.php', [ 'follow_id' => $follow->follow_id ] ),
	] );
}


/********
 * main *
 ********/

page_title_set( 'Προσθήκη έτους παιδιού' );

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

page_nav_add( 'bar_link', [
	'href' => site_href( 'follow-insert.php', [ 'child_id' => $child->child_id ] ),
	'text' => 'προσθήκη',
	'icon' => 'fa-plus',
] );

page_body_add( function() {
	global $child;
	echo sprintf( '<h3 class="w3-panel w3-content w3-text-theme w3-center">%s %s</h3>', $child->last_name, $child->first_name ) . "\n";
} );

page_body_add( 'form_section', $fields );


/********
 * exit *
 ********/

page_html();