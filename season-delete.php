<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$season = season::request();

$season->delete();

$href = site_href( 'seasons.php' );

if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Το έτος διαγράφηκε.',
	'location' => $href,
] );
