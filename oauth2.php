<?php

require_once 'php/core.php';

logout();

$provider = request_var( 'provider' );

switch ( $provider ) {
	case 'google':
		require_once COMPOSER_DIR . 'oauth2-google/vendor/autoload.php';
		$provider = new League\OAuth2\Client\Provider\Google( [
			'clientId'     => GOOGLE_CLIENT_ID,
			'clientSecret' => GOOGLE_CLIENT_SECRET,
			'redirectUri'  => site_href( 'oauth2.php', [ 'provider' => 'google' ] ),
		] );
		$scope = [ 'email' ];
		break;
	case 'microsoft':
		require_once COMPOSER_DIR . 'oauth2-microsoft/vendor/autoload.php';
		$provider = new Stevenmaguire\OAuth2\Client\Provider\Microsoft( [
			'clientId'     => MICROSOFT_CLIENT_ID,
			'clientSecret' => MICROSOFT_CLIENT_SECRET,
			'redirectUri'  => site_href( 'oauth2.php', [ 'provider' => 'microsoft' ] ),
		] );
		$scope = [ 'wl.emails' ];
		break;
	case 'yahoo':
		require_once COMPOSER_DIR . 'oauth2-yahoo/vendor/autoload.php';
		$provider = new Hayageek\OAuth2\Client\Provider\Yahoo( [
			'clientId'     => YAHOO_CLIENT_ID,
			'clientSecret' => YAHOO_CLIENT_SECRET,
			'redirectUri'  => site_href( 'oauth2.php', [ 'provider' => 'yahoo' ] ),
		] );
		$scope = [ 'openid' ];
		break;
	default:
		failure( 'argument_not_valid', 'provider' );
}

if ( array_key_exists( 'code', $_GET ) ) {
	$token = $provider->getAccessToken( 'authorization_code', [ 'code' => $_GET['code'] ] );
	$owner = $provider->getResourceOwner( $token );
	$email_address = filter_var( $owner->getEmail(), FILTER_VALIDATE_EMAIL );
	if ( $email_address === FALSE )
		failure( 'Ο πάροχος απάντησε με μη έγκυρη διεύθυνση email.' );
	$user = user::select_by_email_address( $email_address );
	if ( is_null( $user ) ) {
		$user = new user();
		$user->email_address = $email_address;
		$user->role_id = user::ROLE_GUEST;
		$user->reg_time = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
		$user->reg_ip = $_SERVER['REMOTE_ADDR'];
		$user->insert();
		$user->inform();
		user::clear_by_email_address();
	}
	epoint::write( $user->user_id );
	redirect();
} elseif ( array_key_exists( 'error', $_GET ) ) {
	failure( sprintf( 'Η αυθεντικοποίηση δεν ήταν επιτυχής.<br /><code>%s</code>', $_GET['error'] ) );
} else {
	$options = [
		'scope' => $scope,
	];
	redirect( $provider->getAuthorizationUrl( $options ) );
}