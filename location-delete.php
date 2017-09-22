<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$location = location::request();

if ( count( team::select( [ 'location_id' => $location->location_id ] ) ) )
	failure( 'Διάγραψε πρώτα τις ομάδες και μετά την περιοχή.' );

$location->delete();

$href = SITE_URL . 'locations.php';
if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Η περιοχή διαγράφηκε.',
	'location' => $href,
] );