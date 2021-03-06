<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$user = user::request();

if ( $user->role >= $cuser->role )
	failure( 'Δεν έχεις δικαίωμα να διαγράψεις αυτόν τον χρήστη.' );

$user->delete();

$href = site_href( 'users.php' );
if ( strpos( $_SERVER['HTTP_REFERER'], $href ) === 0 )
	success();

success( [
	'alert' => 'Ο χρήστης διαγράφηκε.',
	'location' => $href,
] );
