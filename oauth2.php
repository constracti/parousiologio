<?php

require_once 'php/core.php';

logout();

$client = new Google\Client( [
	'client_id' => GOOGLE_CLIENT_ID,
	'client_secret' => GOOGLE_CLIENT_SECRET,
	'scopes' => [
		Google\Service\Oauth2::USERINFO_EMAIL,
	],
	'redirect_uri' => site_href( 'oauth2.php' ),
	'state' => OAUTH2_SECRET,
] );

if ( array_key_exists( 'code', $_GET ) ) {
	if ( !is_string( $_GET['code'] ) )
		failure( 'Ο πάροχος απάντησε με μη έγκυρο κωδικό.' );
	if ( !array_key_exists( 'state', $_GET ) || !is_string( $_GET['state'] ) || $_GET['state'] !== OAUTH2_SECRET )
		failure( 'Ο πάροχος κλήθηκε από μη έγκυρη πηγή.' );
	$client->authenticate( $_GET['code'] );
	$access_token = $client->getAccessToken();
	$client->setAccessToken( $access_token );
	$oauth = new Google\Service\Oauth2( $client );
	$userinfo = $oauth->userinfo->get();
	$email_address = filter_var( $userinfo->email, FILTER_VALIDATE_EMAIL );
	if ( $email_address === FALSE )
		failure( 'Ο πάροχος απάντησε με μη έγκυρη διεύθυνση email.' );
	$user = user::select_by_email_address( $email_address );
	if ( is_null( $user ) ) {
		$user = new user();
		$user->email_address = $email_address;
		$user->role = user::ROLE_GUEST;
		$user->reg_tm = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
		$user->reg_ip = $_SERVER['REMOTE_ADDR'];
		$user->insert();
		$user->inform();
		user::clear_by_email_address( $user->email_address );
	}
	epoint::write( $user->user_id );
	redirect();
} elseif ( array_key_exists( 'error', $_GET ) ) {
	failure( sprintf( 'Η αυθεντικοποίηση δεν ήταν επιτυχής.<br /><code>%s</code>', $_GET['error'] ) );
} else {
	$auth_url = $client->createAuthUrl();
	redirect( filter_var( $auth_url, FILTER_SANITIZE_URL ) );
}
