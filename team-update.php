<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$team = team::request();

$fields = [
	'location_id' => new field_select( 'location_id', location::select_options(), [
		'placeholder' => 'περιοχή',
		'required' => TRUE,
		'value' => $team->location_id,
	] ),
	'team_name' => new field( 'team_name', [
		'placeholder' => 'όνομα',
		'required' => TRUE,
		'value' => $team->team_name,
	] ),
	'season_id' => new field_select( 'season_id', season::select_options(), [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $team->season_id,
	] ),
	'on_sunday' => new field_radio( 'on_sunday', [
		0 => 'Σάββατο',
		1 => 'Κυριακή',
	], [
		'value' => $team->on_sunday,
		'required' => TRUE,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	switch ( request_var( 'relation', TRUE ) ) {
		case 'insert_grade':
			$grade = grade::request();
			$team->insert_grade( $grade->grade_id );
			success();
		case 'delete_grade':
			$grade = grade::request();
			$team->delete_grade( $grade->grade_id );
			success();
		case 'insert_grades':
			$category = category::request();
			$team->insert_grades( $category->category_id );
			success();
		case 'delete_grades':
			$team->delete_grades();
			success();
		case 'insert_user':
			$user = user::request();
			$team->insert_user( $user->user_id );
			success();
		case 'delete_user':
			$user = user::request();
			$team->delete_user( $user->user_id );
			success();
		case 'delete_users':
			$team->delete_users();
			success();
		default:
			$team->location_id = $fields['location_id']->post();
			$team->team_name = $fields['team_name']->post();
			$team->season_id = $fields['season_id']->post();
			$team->on_sunday = $fields['on_sunday']->post();
			$team->update();
			success( [
				'alert' => 'Η ομάδα ενημερώθηκε.',
			] );
	}
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία ομάδας' );

page_nav_add( 'season_dropdown', [
	'href' => 'teams.php',
	'text' => 'ομάδες',
	'icon' => 'fa-users',
] );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . sprintf( 'team-update.php?team_id=%d', $team->team_id ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( 'form_section', $fields, [
	'delete' => SITE_URL . sprintf( 'team-delete.php?team_id=%d', $team->team_id ),
] );


/**********
 * grades *
 **********/

$panel = new panel();
$panel->add( function( grade $grade ) {
	return NULL;
}, function( grade $grade ) {
	global $team;
	$href = SITE_URL . sprintf( 'team-update.php?relation=delete_grades&team_id=%d', $team->team_id );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-card-4 w3-round w3-theme-l4 relation" data-relation="grade">' . "\n";
	echo '<li>' . "\n";
	echo '<h3>τάξεις</h3>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-orange" href="%s">καθαρισμός</a>', $href ) . "\n";
	echo '</li>' . "\n";
}, function( grade $grade ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'category_id', function( grade $grade ) {
	global $team;
	$href = SITE_URL . sprintf( 'team-update.php?relation=insert_grades&team_id=%d&category_id=%d', $team->team_id, $grade->category_id );
	echo '<li>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-theme-action" href="%s">%s</a>', $href, $grade->category_name ) . "\n";
}, function( grade $grade ) {
	echo '</li>' . "\n";
} );
$panel->add( 'grade_id', function( grade $grade ) {
	global $team;
	$href_on = SITE_URL . sprintf( 'team-update.php?relation=insert_grade&team_id=%d&grade_id=%d', $team->team_id, $grade->grade_id );
	$href_off = SITE_URL . sprintf( 'team-update.php?relation=delete_grade&team_id=%d&grade_id=%d', $team->team_id, $grade->grade_id );
	echo '<label class="w3-button w3-round w3-theme">' . "\n";
	echo sprintf( '<input type="checkbox" data-href-on="%s" data-href-off="%s"%s />', $href_on, $href_off, $grade->check ? ' checked="checked"' : '' ) . "\n";
	echo sprintf( '<span>%s</span>', $grade->grade_name ) . "\n";
	echo '</label>' . "\n";
} );
page_body_add( [ $panel, 'html' ], $team->check_grades() );


/*********
 * users *
 *********/

$panel = new panel();
$panel->add( NULL, function( user $user ) {
	global $team;
	$href = SITE_URL . sprintf( 'team-update.php?relation=delete_users&team_id=%d', $team->team_id );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-card-4 w3-round w3-theme-l4 relation">' . "\n";
	echo '<li>' . "\n";
	echo '<h3>χρήστες</h3>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-orange" href="%s">καθαρισμός</a>', $href ) . "\n";
	echo '</li>' . "\n";
}, function( user $user ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( function( user $user ): string {
	return mb_substr( $user->last_name, 0, 1 );
}, function( user $user ) {
	echo '<li>' . "\n";
}, function( user $user ) {
	echo '</li>' . "\n";
} );
$panel->add( 'user_id', function( user $user ) {
	global $team;
	$href_on = SITE_URL . sprintf( 'team-update.php?relation=insert_user&team_id=%d&user_id=%d', $team->team_id, $user->user_id );
	$href_off = SITE_URL . sprintf( 'team-update.php?relation=delete_user&team_id=%d&user_id=%d', $team->team_id, $user->user_id );
	echo '<label class="w3-button w3-round w3-theme">' . "\n";
	echo sprintf( '<input type="checkbox" data-href-on="%s" data-href-off="%s"%s />', $href_on, $href_off, $user->check ? ' checked="checked"' : '' ) . "\n";
	echo sprintf( '<span>%s %s</span>', $user->last_name, $user->first_name ) . "\n";
	echo '</label>' . "\n";
} );
page_body_add( [ $panel, 'html' ], $team->check_users() );


/********
 * exit *
 ********/

page_html();