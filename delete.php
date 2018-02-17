<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$team = team::request();
if ( !$cuser->accesses( $team->team_id ) )
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
	'location' => site_href( 'presences.php', [ 'team_id' => $team->team_id ] ),
] );
