<?php

require_once 'php/page.php';

if ( is_null( $cuser ) )
	failure();

$mode = request_var( 'mode' );
if ( !in_array( $mode, [ 'desktop', 'mobile' ] ) )
	failure();

$team = team::request( 'team_id' );
if ( !$cuser->has_team( $team->team_id ) )
	failure();

$child = child::request( 'child_id' );
if ( !$team->has_child( $child->child_id ) )
	failure();

$follow = follow::select( [
	'child_id' => $child->child_id,
	'season_id' => $team->season_id,
] );
$follow = array_values( $follow )[ 0 ];

$follow->delete();

$follows = $child->select_follows();
if ( count( $follows ) === 0 )
	$child->delete();

header( 'location: ' . SITE_URL . 'presences.php?mode=' . $mode . '&team_id=' . $team->team_id );
exit;