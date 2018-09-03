<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$lseason = season::select_last();

$fields = [
	'year' => new field_year( 'year', [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => !is_null( $lseason ) ? $lseason->year + 1 : NULL,
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
	'move' => new field_checkbox( 'move', [
		'placeholder' => 'μεταφορά δεδομένων από το προηγούμενο έτος',
		'value' => TRUE,
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
	$season->insert();
	$move = $fields['move']->post();
	if ( $move && !is_null( $pseason = $season->select_prev() ) ) {
		$diff = $season->year - $pseason->year;
		$stmt = $db->prepare( '
INSERT INTO `xa_follow` ( `child_id`, `season_id`, `grade_id`, `location_id` )
SELECT `child_id`, ?, `grade_id` + ?, `location_id`
FROM `xa_follow`
WHERE `season_id` = ? AND `grade_id` + ? <= 12
		' );
		$stmt->bind_param( 'iiii', $season->season_id, $diff, $pseason->season_id, $diff );
		$stmt->execute();
		$stmt->close();
		$pteams = team::select( [ 'season_id' => $pseason->season_id ] );
		foreach ( $pteams as $team ) {
			$pteam_id = $team->team_id;
			$team->season_id = $season->season_id;
			$team->insert();
			$stmt = $db->prepare( '
INSERT INTO `xa_target` ( `team_id`, `grade_id` )
SELECT ?, `grade_id`
FROM `xa_target`
WHERE `team_id` = ?
			' );
			$stmt->bind_param( 'ii', $team->team_id, $pteam_id );
			$stmt->execute();
			$stmt->close();
			$stmt = $db->prepare( '
INSERT INTO `xa_access` ( `user_id`, `team_id` )
SELECT `user_id`, ?
FROM `xa_access`
WHERE `team_id` = ?
			' );
			$stmt->bind_param( 'ii', $team->team_id, $pteam_id );
			$stmt->execute();
			$stmt->close();
		}
	}
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
