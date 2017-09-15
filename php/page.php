<?php

require_once 'core.php';
require_once 'panel.php';


function failure() {
	header( 'location: ' . HOME_URL );
	exit;
}


function checked( bool $checked ): string {
	if ( $checked )
		return ' checked="checked"';
	return '';
}


/**************
 * page title *
 **************/

$page_title = SITE_NAME;

function page_title_set( string $title ) {
	global $page_title;
	$page_title = $title;
}


/***************
 * page styles *
 ***************/

$page_styles = [];

function page_style_add( string $style ) {
	global $page_styles;
	$page_styles[] = $style;
}

page_style_add( 'https://www.w3schools.com/w3css/4/w3.css' );
page_style_add( 'https://www.w3schools.com/lib/w3-theme-blue.css' );
page_style_add( 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );


/****************
 * page scripts *
 ****************/

$page_scripts = [];

function page_script_add( string $script ) {
	global $page_scripts;
	$page_scripts[] = $script;
}

page_script_add( 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js' );


/*************
 * page navs *
 *************/

$page_navs = [];

function page_nav_add( callable $function ) {
	global $page_navs;
	$page_navs[] = $function;
}

page_nav_add( function() {
?>
			<a class="w3-bar-item w3-button w3-right" href="https://agonistes.gr/" target="_blank" title="Χαρούμενοι Αγωνιστές - Χαρούμενες Αγωνίστριες">
				<span class="fa fa-globe"></span>
			</a>
<?php
} );

# TODO maybe logout or chmail actions will be invoked afterwards
if ( !is_null( $cuser ) )
	page_nav_add( function() {
		global $cuser;
		$hash = md5( $cuser->email_address );
		$gravatar = sprintf( 'https://www.gravatar.com/avatar/%s?size=24&default=mm', $hash );
?>
			<div class="w3-dropdown-hover w3-right">
				<button class="w3-button" title="<?= $cuser->email_address ?>">
					<img class="w3-circle" src="<?= $gravatar ?>" style="height: 24px; width: auto; margin: -4px 0px;" />
					<span class="w3-hide-small"><?= $cuser->email_address ?></span>
					<span class="fa fa-caret-down"></span>
				</button>
				<div class="w3-dropdown-content w3-bar-block w3-theme-l2" style="min-width: initial;">
					<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>profile.php" title="προφίλ">
						<span class="fa fa-pencil"></span>
						<span class="w3-hide-small">προφίλ</span>
					</a>
					<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>settings.php" title="ρυθμίσεις">
						<span class="fa fa-cog"></span>
						<span class="w3-hide-small">ρυθμίσεις</span>
					</a>
					<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>logout.php" title="έξοδος">
						<span class="fa fa-sign-out"></span>
						<span class="w3-hide-small">έξοδος</span>
					</a>
				</div>
			</div>
<?php
	} );


/*****************
 * page messages *
 *****************/

$page_messages = [];

function page_message_color( string $type ): string {
	switch( $type ) {
		case 'success':
			return 'w3-green';
		case 'info':
			return 'w3-blue';
		case 'warning':
			return 'w3-orange';
		case 'error':
			return 'w3-red';
		default:
			return 'w3-theme';
	}
}

function page_message_add( string $html, string $type = '' ) {
	global $page_messages;
	$page_messages[] = [
		'html' => $html,
		'type' => $type,
	];
}


/***************
 * page bodies *
 ***************/

$page_bodies = [];

function page_body_add( callable $body ) {
	global $page_bodies;
	$page_bodies[] = $body;
}


/**********
 * season *
 **********/

function season_dropdown() {
	global $cyear;
	global $lyear;
	$class = 'w3-button';
	if ( $lyear !== $cyear )
		$class .= ' w3-theme-d2';
?>
			<div class="w3-dropdown-hover">
				<button class="<?= $class ?>" title="<?= $cyear ?>">
					<span class="fa fa-calendar"></span>
					<span><?= $cyear ?></span>
					<span class="fa fa-caret-down"></span>
				</button>
				<div class="w3-dropdown-content w3-bar-block w3-theme-l2" style="min-width: initial;">
<?php
	foreach ( season::select( [], [ 'year' => 'DESC' ] ) as $season ) {
		$class = 'w3-bar-item w3-button';
		$href = HOME_URL;
		if ( $season->year !== $lyear )
			$href .= '?year=' . $season->year;
		if ( $season->year === $cyear )
			$class .= ' w3-theme-l1';
?>
					<a class="<?= $class ?>" href="<?= $href ?>" title="<?= $season->year ?>">
						<span><?= $season->year ?></span>
						<span class="w3-hide-small">-</span>
						<span class="w3-hide-small"><?= $season->slogan_old ?></span>
					</a>
<?php
	}
?>
				</div>
			</div>
<?php
}

function season_home(): string {
	global $cyear;
	global $lyear;
	$href = HOME_URL;
	if ( $cyear !== $lyear )
		$href .= '?year=' . $cyear;
	return $href;
}

$cyear = NULL;
$lyear = season::select_last();
if ( !is_null( $lyear ) ) {
	$lyear = $lyear->year;
	$cyear = ( function() {
		if ( array_key_exists( 'year', $_GET ) ) {
			$year = filter_var( $_GET['year'], FILTER_VALIDATE_INT );
			if ( $year === FALSE )
				return NULL;
			$season = season::select_by( 'year', $year );
			if ( is_null( $season ) )
				return NULL;
			return $season->year;
		}
		if ( array_key_exists( 'team_id', $_GET ) ) {
			$team_id = filter_var( $_GET['team_id'], FILTER_VALIDATE_INT );
			if ( $team_id === FALSE )
				return NULL;
			$team = team::select_by( 'team_id', $team_id );
			if ( is_null( $team ) )
				return NULL;
			$season = season::select_by( 'season_id', $team->season_id );
			return $season->year;
		}
		return NULL;
	} )() ?? $lyear;
}


/*************
 * page html *
 *************/

function page_html() {
	global $cuser;
	global $page_title;
	global $page_styles;
	global $page_scripts;
	global $page_navs;
	global $page_messages;
	global $page_bodies;
?>
<!DOCTYPE html>
<html lang="el">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="author" content="constracti" />
		<meta name="description" content="Παρουσιολόγιο Χαρούμενων Αγωνιστών Αθήνας" />
		<meta name="keywords" content="παρουσίες, παρουσίες χα, παρουσιολόγιο, χαρούμενοι αγωνιστές, χαρούμενοι, αγωνιστές, παρουσιολόγιο χαρούμενων αγωνιστών, παρουσιολόγιο χα, χα, parousies, parousies xa, parousiologio, xaroumenoi agonistes, xaroumenoi, agonistes, parousiologio xaroumenon agoniston, parousiologio xa, xa" />
		<title><?= $page_title ?></title>
		<link rel="icon" href="<?= HOME_URL ?>favicon-256.png" />
<?php
	foreach ( $page_styles as $style ) {
?>
		<link rel="stylesheet" href="<?= $style ?>" />
<?php
	}
	foreach ( $page_scripts as $script ) {
?>
		<script src="<?= $script ?>"></script>
<?php
	}
?>
	</head>
	<body class="w3-theme-l5">
		<div class="w3-bar w3-theme">
			<a class="w3-bar-item w3-button" href="<?= season_home() ?>" title="αρχική σελίδα">
				<img src="<?= HOME_URL ?>favicon-256.png" style="height: 24px; width: auto; margin: -4px 0px;" />
				<span class="w3-hide-small"><?= SITE_NAME ?></span>
			</a>
<?php
	foreach ( $page_navs as $nav )
		$nav();
?>
		</div>
		<h1 class="w3-panel w3-content w3-text-theme w3-center"><?= $page_title ?></h1>
<?php
	if ( !is_null( $cuser ) && ( is_null( $cuser->last_name ) || is_null( $cuser->first_name ) ) )
		page_message_add( sprintf( 'Συμπλήρωσε τα στοιχεία σου στο <a href="%sprofile.php">προφίλ</a>.', HOME_URL ), 'warning' );
	foreach ( $page_messages as $message ) {
?>
		<div class="w3-panel w3-content">
			<div class="w3-container w3-round w3-leftbar <?= page_message_color( $message['type'] ) ?>">
				<p><?= $message['html'] ?></p>
			</div>
		</div>
<?php
	}
	foreach ( $page_bodies as $body )
		$body();
?>
	</body>
</html>
<?php
}