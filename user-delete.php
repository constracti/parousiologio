<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$user = user::request();

if ( $user->role_id >= $cuser->role_id )
	failure( 'Δεν έχεις δικαίωμα να διαγράψεις αυτόν τον χρήστη.' );

$user->delete();

$href = season_href( 'users.php' );
if ( $_SERVER['HTTP_REFERER'] === $href )
	success();

success( [
	'alert' => 'Ο χρήστης διαγράφηκε.',
	'location' => $href,
] );