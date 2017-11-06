<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$follow = follow::request();
$child = child::select_by( 'child_id', $follow->child_id );

$fields = [
	'season_id' => new field_select( 'season_id', season::select_options(), [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $follow->season_id,
	] ),
	'grade_id' => new field_select( 'grade_id', grade::select_options(), [
		'placeholder' => 'τάξη',
		'required' => TRUE,
		'value' => $follow->grade_id,
	] ),
	'location_id' => new field_select( 'location_id', location::select_options(), [
		'placeholder' => 'περιοχή',
		'value' => $follow->location_id,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$follow->season_id = $fields['season_id']->post();
	$follow->grade_id = $fields['grade_id']->post();
	$follow->location_id = $fields['location_id']->post();
	$follows = follow::select( [
		'child_id' => $follow->child_id,
		'season_id' => $follow->season_id,
	] );
	if ( count( $follows ) > 0 && !array_key_exists( $follow->follow_id, $follows ) )
		failure( 'Το έτος παιδιού υπάρχει ήδη.' );
	$follow->update();
	success( [
		'alert' => 'Το έτος παιδιού ενημερώθηκε.',
	] );
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία έτους παιδιού' );

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
	'href' => site_href( 'follow-update.php', [ 'follow_id' => $follow->follow_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( function() {
	global $child;
	echo sprintf( '<h3 class="w3-panel w3-content w3-text-theme w3-center">%s %s</h3>', $child->last_name, $child->first_name ) . "\n";
} );

page_body_add( 'form_section', $fields, [
	'delete' => site_href( 'follow-delete.php', [ 'follow_id' => $follow->follow_id ] ),
] );


/********
 * exit *
 ********/

page_html();