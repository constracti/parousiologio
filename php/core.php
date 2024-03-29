<?php

require_once 'config.php';

require_once 'vendor/autoload.php';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' )
	require_once SITE_DIR . 'php/ajax.php';
else
	require_once SITE_DIR . 'php/page.php';

require_once SITE_DIR . 'php/entity.php';
require_once SITE_DIR . 'php/field.php';

require_once SITE_DIR . 'php/category.php';
require_once SITE_DIR . 'php/child.php';
require_once SITE_DIR . 'php/dtime.php';
require_once SITE_DIR . 'php/epoint.php';
require_once SITE_DIR . 'php/event.php';
require_once SITE_DIR . 'php/follow.php';
require_once SITE_DIR . 'php/grade.php';
require_once SITE_DIR . 'php/location.php';
require_once SITE_DIR . 'php/user.php';
require_once SITE_DIR . 'php/season.php';
require_once SITE_DIR . 'php/team.php';
require_once SITE_DIR . 'php/vlink.php';

function sorter( string ...$keys ): callable {
	return function( $x1, $x2 ) use ( $keys ): int {
		foreach ( $keys as $key ) {
			$negate = FALSE;
			if ( substr( $key, 0, 1 ) === '~' ) {
				$negate = TRUE;
				$key = substr( $key, 1 );
			}
			$props = explode( '/', $key );
			$v1 = $x1;
			$v2 = $x2;
			foreach ( $props as $prop ) {
				if ( is_array( $v1 ) ) {
					assert( array_key_exists( $prop, $v1 ) );
					$v1 = $v1[$prop];
				} elseif ( is_object( $v1 ) ) {
					assert( property_exists( $v1, $prop ) );
					$v1 = $v1->$prop;
				} else {
					assert( FALSE );
				}
				if ( is_array( $v2 ) ) {
					assert( array_key_exists( $prop, $v2 ) );
					$v2 = $v2[$prop];
				} elseif ( is_object( $v2 ) ) {
					assert( property_exists( $v2, $prop ) );
					$v2 = $v2->$prop;
				} else {
					assert( FALSE );
				}
			}
			$cmp = $v1 <=> $v2;
			if ( $negate )
				$cmp = -$cmp;
			if ( $cmp )
				return $cmp;
		}
		return 0;
	};
}


/**********
 * errors *
 **********/

$errors = [
	'internal_error' => 'Εσωτερικό σφάλμα:<br /><code>%s</code>',
	'database_not_accessible' => 'Η βάση δεδομένων δεν είναι προσβάσιμη:<br /><code>%s</code>',
	'privilege_required' => 'Πρέπει να είσαι τουλάχιστον <i>%s</i> για να έχεις πρόσβαση σε αυτή τη σελίδα.',
	'argument_not_defined' => 'Η παράμετρος <i>%s</i> δεν είναι ορισμένη.',
	'argument_not_valid' => 'Η παράμετρος <i>%s</i> δεν είναι έγκυρη.',
];


/***********
 * filters *
 ***********/

function filter_int( string $var ) {
	$var = filter_var( $var, FILTER_VALIDATE_INT );
	if ( $var === FALSE )
		return NULL;
	return $var;
}

function filter_text( string $var ) {
	$var = strip_tags( $var );
	$var = preg_replace( '/\s+/', ' ', $var );
	$var = trim( $var );
	if ( $var === '' )
		return NULL;
	return $var;
}

function filter_email( string $var ) {
	$var = filter_var( $var, FILTER_VALIDATE_EMAIL );
	if ( $var === FALSE )
		return NULL;
	return $var;
}

function filter_regexp( string $var, string $regexp ) {
	$var = filter_var( $var, FILTER_VALIDATE_REGEXP, [
		'options' => [
			'regexp' => '/^' . $regexp . '$/',
		],
	] );
	if ( $var === FALSE )
		return NULL;
	return $var;
}


/***********
 * request *
 ***********/

function request_bool( string $key ): bool {
	if ( !array_key_exists( $key, $_REQUEST ) )
		return FALSE;
	$var = $_REQUEST[ $key ];
	if ( is_null( $var ) || $var === '' )
		return FALSE;
	return TRUE;
}

function request_var( string $key, bool $nullable = FALSE ) {
	if ( request_bool( $key ) )
		return $_REQUEST[ $key ];
	if ( $nullable )
		return NULL;
	failure( 'argument_not_defined', $key );
}

function request_int( string $key, bool $nullable = FALSE ) {
	$var = request_var( $key, $nullable );
	if ( is_null( $var ) )
		return NULL;
	$var = filter_int( $var );
	if ( !is_null( $var ) )
		return $var;
	failure( 'argument_not_valid', $key );
}

function request_recaptcha() {
	$response = request_var( 'g-recaptcha-response' );
	$ch = curl_init( 'https://www.google.com/recaptcha/api/siteverify' );
	if ( $ch === FALSE )
		failure( 'argument_not_valid', 'g-recaptcha-response' ); # TODO normally it's an external error
	curl_setopt( $ch, CURLOPT_POST, TRUE );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, [
		'secret' => RECAPTCHA_SECRET_KEY,
		'response' => $response,
		'remoteip' => $_SERVER['REMOTE_ADDR'],
	] );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	$result = curl_exec( $ch );
	curl_close( $ch );
	$result = json_decode( $result );
	if ( $result->success === FALSE )
		failure( 'argument_not_valid', 'g-recaptcha-response' );
}


/************
 * database *
 ************/

$db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );

if ( !is_null( $db->connect_error ) )
	failure( 'database_not_accessible', $db->connect_error );

if ( !$db->set_charset( 'utf8' ) )
	failure( 'database_not_accessible', $db->error );


/***********
 * session *
 ***********/

function logout() {
	global $cuser;
	global $cepoint;
	if ( is_null( $cuser ) )
		return;
	$cuser = NULL;
	$cepoint->clear();
	$cepoint = NULL;
}

$cuser = NULL;
$cepoint = epoint::read();
if ( !is_null( $cepoint ) ) {
	$cuser = user::select_by( 'user_id', $cepoint->user_id );
	$cuser->act_tm = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
	$cuser->act_ip = $_SERVER['REMOTE_ADDR'];
	$cuser->update();
}

function privilege( int $role ) {
	global $cuser;
	if ( is_null( $cuser ) || $cuser->role < $role )
		failure( 'privilege_required', user::ROLES[ $role ] );
}


/**********
 * season *
 **********/

$cseason = NULL;
$lseason = season::select_last();
if ( !is_null( $lseason ) ) {
	$cseason = ( function() {
		$season = season::request( '', TRUE );
		if ( !is_null( $season ) )
			return $season;
		$team = team::request( '', TRUE );
		if ( !is_null( $team ) )
			return season::select_by( 'season_id', $team->season_id );
		$event = event::request( '', TRUE );
		if ( !is_null( $event ) )
			return season::select_by( 'season_id', $event->season_id );
		return NULL;
	} )() ?? $lseason;
}

function site_href( string $url = '', array $parameters = [] ): string {
	if ( !empty( $parameters ) )
		$url .= '?' . http_build_query( $parameters );
	return SITE_URL . $url;
}

function season_href( string $url = '', array $parameters = [] ): string {
	global $cseason;
	global $lseason;
	if ( $cseason->season_id !== $lseason->season_id )
		$parameters['season_id'] = $cseason->season_id;
	return site_href( $url, $parameters );
}

function season_dropdown( array $arguments = [] ) {
	global $cseason;
	global $lseason;
	if ( !array_key_exists( 'href', $arguments ) )
		$arguments['href'] = '';
	if ( !array_key_exists( 'pars', $arguments ) )
		$arguments['pars'] = [];
	if ( !array_key_exists( 'text', $arguments ) )
		$arguments['text'] = $cseason->year;
	if ( !array_key_exists( 'icon', $arguments ) )
		$arguments['icon'] = 'fa-calendar';
	$class = 'w3-button';
	if ( $lseason->season_id !== $cseason->season_id )
		$class .= ' w3-theme-d2';
?>
<div class="w3-dropdown-hover">
	<button class="<?= $class ?>" title="<?= $arguments['text'] ?>">
		<span class="fa <?= $arguments['icon'] ?>"></span>
		<span class="w3-hide-small"><?= $arguments['text'] ?></span>
		<span class="fa fa-caret-down"></span>
	</button>
	<div class="w3-dropdown-content w3-bar-block w3-theme-l2">
<?php
	foreach ( season::select( [], [ 'year' => 'DESC' ] ) as $season ) {
		$class = 'w3-bar-item w3-button';
		$pars = $arguments['pars'];
		if ( $season->season_id !== $lseason->season_id )
			$pars['season_id'] = $season->season_id;
		$href = site_href( $arguments['href'], $pars );
		if ( $season->season_id === $cseason->season_id )
			$class .= ' w3-theme-l1';
		echo sprintf( '<a class="%s" href="%s" title="%d">', $class, $href, $season->year ) . "\n";
		echo sprintf( '<span>%d</span>', $season->year ) . "\n";
		if ( !is_null( $season->slogan_old ) ) {
			echo '<span class="w3-hide-small">-</span>' . "\n";
			echo sprintf( '<span class="w3-hide-small">%s</span>', $season->slogan_old ) . "\n";
		}
		echo '</a>' . "\n";
	}
?>
	</div>
</div>
<?php
}
