<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$team = team::request();

$team->delete();

$href = season_href( 'teams.php' );
if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Η ομάδα διαγράφηκε.',
	'location' => $href,
] );