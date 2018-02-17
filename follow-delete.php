<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$follow = follow::request();

$follow->delete();

$href = site_href( 'follows.php', [ 'child_id' => $follow->child_id ] );

if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Το έτος παιδιού διαγράφηκε.',
	'location' => $href,
] );
