<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$child = child::request();

$child->delete();

$href = site_href( 'children.php' );

if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Το παιδί διαγράφηκε.',
	'location' => $href,
] );
