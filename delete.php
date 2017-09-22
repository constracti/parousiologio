<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$mode = request_var( 'mode' );
if ( !in_array( $mode, [ 'desktop', 'mobile' ] ) )
	failure( 'argument_not_valid', 'mode' );

$team = team::request();
if ( !$cuser->has_team( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

$child = child::request();
if ( !$team->has_child( $child->child_id ) )
	failure( 'argument_not_valid', 'child_id' );

$follows = follow::select( [
	'child_id' => $child->child_id,
	'season_id' => $team->season_id,
] );
$follow = array_shift( $follows );

$follow->delete();

$follows = $child->select_follows();
if ( count( $follows ) === 0 )
	$child->delete();

success( [
	'alert' => 'Η εγγραφή παιδιού διαγράφηκε.',
	'location' => SITE_URL . sprintf( 'presences.php?mode=%s&team_id=%d', $mode, $team->team_id ),
] );