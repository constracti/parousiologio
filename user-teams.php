<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$user = user::request();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	switch ( request_var( 'relation', TRUE ) ) {
		case 'insert_team':
			$team = team::request();
			if ( $team->season_id !== $cseason->season_id )
				failure( 'argument_not_valid', 'team_id' );
			$user->insert_team( $team->team_id );
			success();
		case 'delete_team':
			$team = team::request();
			if ( $team->season_id !== $cseason->season_id )
				failure( 'argument_not_valid', 'team_id' );
			$user->delete_team( $team->team_id );
			success();
		case 'insert_teams':
			$location = location::request();
			$user->insert_teams( $location->location_id );
			success();
		case 'delete_teams':
			$user->delete_teams();
			success();
		default:
			failure( 'argument_not_valid', 'relation' );
	}
}


/********
 * main *
 ********/

page_title_set( 'Ομάδες χρήστη' );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . 'users.php',
	'text' => 'χρήστες',
	'icon' => 'fa-user',
	'hide_medium' => FALSE,
] );

page_nav_add( 'season_dropdown', [
	'href' => 'user-teams.php',
	'pars' => [ 'user_id' => $user->user_id ],
	'text' => 'ομάδες',
	'icon' => 'fa-users',
] );


/*********
 * teams *
 *********/

$panel = new panel();
$panel->add( NULL, function( team $team ) {
	global $user;
	global $cseason;
	echo '<section class="w3-panel w3-content">' . "\n";
	echo sprintf( '<h3>%s %s</h3>', $user->last_name, $user->first_name ) . "\n";
	echo '<ul class="w3-ul w3-card-4 w3-round w3-theme-l4 relation">' . "\n";
	echo '<li>' . "\n";
	echo sprintf( '<h3>ομάδες %d</h3>', $cseason->year ) . "\n";
	$href = season_href( 'user-teams.php', [ 'relation' => 'delete_teams', 'user_id' => $user->user_id ] );
	echo sprintf( '<a class="w3-button w3-round w3-orange" href="%s">καθαρισμός</a>', $href ) . "\n";
	echo '</li>' . "\n";
}, function( team $team ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'location_id', function( team $team ) {
	global $user;
	echo '<li>' . "\n";
	$href = season_href( 'user-teams.php', [ 'relation' => 'insert_teams', 'user_id' => $user->user_id, 'location_id' => $team->location_id ] );
	echo sprintf( '<a class="w3-button w3-round w3-theme-action" href="%s">%s</a>', $href, $team->location_name ) . "\n";
}, function( team $team ) {
	echo '</li>' . "\n";
} );
$panel->add( 'team_id', function( team $team ) {
	global $user;
	if ( is_null( $team->team_id ) )
		return;
	$href_on = season_href( 'user-teams.php', [ 'relation' => 'insert_team', 'user_id' => $user->user_id, 'team_id' => $team->team_id ] );
	$href_off = season_href( 'user-teams.php', [ 'relation' => 'delete_team', 'user_id' => $user->user_id, 'team_id' => $team->team_id ] );
	echo '<label class="w3-button w3-round w3-theme">' . "\n";
	echo sprintf( '<input type="checkbox" data-href-on="%s" data-href-off="%s"%s />', $href_on, $href_off, $team->check ? ' checked="checked"' : '' ) . "\n";
	echo sprintf( '<span>%s</span>', $team->team_name ) . "\n";
	echo '</label>' . "\n";
} );
page_body_add( [ $panel, 'html' ], $user->check_teams() );


/********
 * exit *
 ********/

page_html();