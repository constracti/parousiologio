<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$event = event::request();

$event->delete();

$href = season_href( 'events.php' );
if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Το συμβάν διαγράφηκε.',
	'location' => $href,
] );
