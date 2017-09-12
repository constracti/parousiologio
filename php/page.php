<?php

require_once 'core.php';


/**************
 * page title *
 **************/

$page_title = SITE_NAME;

function page_title_set( string $title ) {
	global $page_title;
	$page_title = $title;
}


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
?>
<div class="w3-dropdown-hover w3-right">
	<button class="w3-button" title="<?= $cuser->email_address ?>">
		<span class="fa fa-user"></span>
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
	global $cseason;
	global $lseason;
	$class = 'w3-button';
	if ( $lseason !== $cseason )
		$class .= ' w3-theme-d2';
?>
<div class="w3-dropdown-hover">
	<button class="<?= $class ?>">
		<span class="fa fa-calendar"></span>
		<span class="w3-hide-small"><?= $cseason ?></span>
		<span class="fa fa-caret-down"></span>
	</button>
	<div class="w3-dropdown-content w3-bar-block w3-theme-l2" style="min-width: initial;">
<?php
	foreach ( season::select( [], [ 'year' => 'DESC' ] ) as $season ) {
		$class = 'w3-bar-item w3-button';
		$href = HOME_URL;
		if ( $season->year !== $lseason )
			$href .= '?year=' . $season->year;
		if ( $season->year === $cseason )
			$class .= ' w3-theme-l1';
?>
		<a class="<?= $class ?>" href="<?= $href ?>">
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
	global $cseason;
	global $lseason;
	$href = HOME_URL;
	if ( $cseason !== $lseason )
		$href .= '?year=' . $cseason;
	return $href;
}

$cseason = NULL;
$lseason = season::select_last();
if ( !is_null( $lseason ) ) {
	$lseason = $lseason->year;
	if ( array_key_exists( 'year', $_GET ) ) {
		$cseason = filter_var( $_GET['year'], FILTER_VALIDATE_INT );
		if ( $cseason === FALSE ) {
			$cseason = $lseason;
		} else {
			$cseason = season::select_by( 'year', $cseason );
			if ( is_null( $cseason ) )
				$cseason = $lseason;
			else
				$cseason = $cseason->year;
		}
	} else {
		$cseason = $lseason;
	}
}


/*************
 * page html *
 *************/

function page_html() {
	global $cuser;
	global $page_title;
	global $page_navs;
	global $page_messages;
	global $page_bodies;
?>
<!DOCTYPE html>
<html lang="el">
	<head>
		<meta charset="utf-8" />
		<title><?= $page_title ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
		<link rel="stylesheet" href="https://www.w3schools.com/lib/w3-theme-blue.css" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
	</head>
	<body class="w3-theme-l5">
		<div class="w3-bar w3-blue">
			<a class="w3-bar-item w3-button" href="<?= season_home() ?>" title="αρχική σελίδα">
				<img src="<?= HOME_URL ?>favicon-256.png" style="width: 24px; height: auto; margin: 0px; position: absolute;" />
				<span class="fa fa-home" style="margin-left: 40px;"></span>
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