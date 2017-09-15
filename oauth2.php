<?php

require_once 'php/core.php';

logout();

if ( !array_key_exists( 'provider', $_GET ) )
	exit( 'not set provider' );

switch ( $_GET['provider'] ) {
	case 'google':
		require_once COMPOSER_DIR . 'oauth2-google/vendor/autoload.php';
		$provider = new League\OAuth2\Client\Provider\Google( [
			'clientId'     => GOOGLE_CLIENT_ID,
			'clientSecret' => GOOGLE_CLIENT_SECRET,
			'redirectUri'  => HOME_URL . 'oauth2.php?provider=google',
		] );
		$scope = [ 'email' ];
		break;
	case 'microsoft':
		require_once COMPOSER_DIR . 'oauth2-microsoft/vendor/autoload.php';
		$provider = new Stevenmaguire\OAuth2\Client\Provider\Microsoft( [
			'clientId'     => MICROSOFT_CLIENT_ID,
			'clientSecret' => MICROSOFT_CLIENT_SECRET,
			'redirectUri'  => HOME_URL . 'oauth2.php?provider=microsoft',
		] );
		$scope = [ 'wl.emails' ];
		break;
	case 'yahoo':
		require_once COMPOSER_DIR . 'oauth2-yahoo/vendor/autoload.php';
		$provider = new Hayageek\OAuth2\Client\Provider\Yahoo( [
			'clientId'     => YAHOO_CLIENT_ID,
			'clientSecret' => YAHOO_CLIENT_SECRET,
			'redirectUri'  => HOME_URL . 'oauth2.php?provider=yahoo',
		] );
		$scope = [ 'openid' ];
		break;
	default:
		exit( 'invalid provider' );
}

if ( array_key_exists( 'login', $_GET ) ) {
	$options = [
		'scope' => $scope,
	];
	header( 'location: ' . $provider->getAuthorizationUrl( $options ) );
	exit;
} elseif ( array_key_exists( 'code', $_GET ) ) {
	$token = $provider->getAccessToken( 'authorization_code', [ 'code' => $_GET['code'] ] );
	$owner = $provider->getResourceOwner( $token );
	$email_address = filter_var( $owner->getEmail(), FILTER_VALIDATE_EMAIL );
	if ( $email_address === FALSE )
		exit( 'invalid email address' );
	$user = user::select_by( 'email_address', $email_address );
	if ( !is_null( $user ) && $user->role_id === user::ROLE_UNVER ) {
		$user->delete();
		$user = NULL;
	}
	if ( is_null( $user ) ) {
		$user = new user();
		$user->email_address = $email_address;
		$user->role_id = user::ROLE_GUEST;
		$user->reg_time = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
		$user->reg_ip = $_SERVER['REMOTE_ADDR'];
		$user->insert();
		# TODO inform admin
	}
	epoint::write( $user->user_id );
} elseif ( array_key_exists( 'error', $_GET ) ) {
	exit( 'authentication: ' . $_GET['error'] );
} else {
	exit( 'invalid page call' );
}