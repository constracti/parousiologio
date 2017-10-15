<?php

# TODO move inline css and js in separate files

require_once 'panel.php';
require_once 'table.php';

function failure( string $error = '', ...$args ) {
	global $errors;
	page_title_set( 'Σφάλμα' );
	if ( array_key_exists( $error, $errors ) )
		$html = sprintf( $errors[ $error ], ...$args );
	else
		$html = $error;
	page_message_add( $html, 'error' );
	page_html();
}

function redirect( string $url = SITE_URL ) {
	header( 'location: ' . $url );
	exit;
}


/**************
 * page title *
 **************/

$page_title = NULL;

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

function page_nav_add( callable $function, ...$arguments ) {
	global $page_navs;
	$page_navs[] = [
		'function'  => $function,
		'arguments' => $arguments,
	];
}

function bar_link( array $arguments = [] ) {
	if ( !array_key_exists( 'href', $arguments ) )
		$arguments['href'] = NULL;
	if ( !array_key_exists( 'text', $arguments ) )
		$arguments['text'] = NULL;
	if ( !array_key_exists( 'icon', $arguments ) )
		$arguments['icon'] = NULL;
	if ( !array_key_exists( 'hide_small', $arguments ) )
		$arguments['hide_small'] = TRUE;
	if ( !array_key_exists( 'hide_medium', $arguments ) )
		$arguments['hide_medium'] = TRUE;
	$class = [];
	if ( $arguments['hide_small'] )
		$class[] = 'w3-hide-small';
	if ( $arguments['hide_medium'] )
		$class[] = 'w3-hide-medium';
	echo sprintf( '<a class="w3-bar-item w3-button" href="%s" title="%s">', $arguments['href'], $arguments['text'] ) . "\n";
	echo sprintf( '<span class="fa %s"></span>', $arguments['icon'] ) . "\n";
	echo sprintf( '<span class="%s">%s</span>', implode( ' ', $class ), $arguments['text'] ) . "\n";
	echo '</a>' . "\n";
}

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= season_href() ?>" title="αρχική">
	<img src="<?= site_href( 'favicon-256.png' ) ?>" style="height: 24px; width: auto; margin: -4px 0px;" />
	<span class="w3-hide-small"><?= SITE_NAME ?></span>
</a>
<?php
} );

page_nav_add( function() {
?>
<div class="w3-dropdown-hover w3-right">
	<button class="w3-button" title="σύνδεσμοι">
		<span class="fa fa-external-link"></span>
		<span class="w3-hide-small w3-hide-medium">σύνδεσμοι</span>
		<span class="fa fa-caret-down"></span>
	</button>
	<div class="w3-dropdown-content w3-bar-block w3-theme-l2">
		<a class="w3-bar-item w3-button" href="https://agonistes.gr/" target="_blank" title="Χαρούμενοι Αγωνιστές">
			<img src="<?= site_href( 'img/agonistes.png' ) ?>" style="margin: 0px;" />
			<span>Χαρούμενοι Αγωνιστές</span>
		</a>
		<a class="w3-bar-item w3-button" href="https://synathlountes.agonistes.gr/" target="_blank" title="Συναθλούντες">
			<img src="<?= site_href( 'img/synathlountes.png' ) ?>" style="margin: 0px;" />
			<span>Συναθλούντες</span>
		</a>
	</div>
</div>
<?php
} );

page_nav_add( function() {
	global $cuser;
	if ( is_null( $cuser ) || $cuser->role < user::ROLE_OBSER )
		return;
?>
<div class="w3-dropdown-hover w3-right">
	<button class="w3-button" title="διαχείριση">
		<span class="fa fa-star"></span>
		<span class="w3-hide-small w3-hide-medium">διαχείριση</span>
		<span class="fa fa-caret-down"></span>
	</button>
	<div class="w3-dropdown-content w3-bar-block w3-theme-l2">
<?php
	if ( $cuser->get_meta( 'index' ) !== 'list' ) {
		bar_link( [
			'href' => season_href( 'view.php' ),
			'text' => 'προβολή',
			'icon' => 'fa-list',
			'hide_small' => FALSE,
			'hide_medium' => FALSE,
		] );
	}
	bar_link( [
		'href' => season_href( 'summary.php' ),
		'text' => 'σύνοψη',
		'icon' => 'fa-table',
		'hide_small' => FALSE,
		'hide_medium' => FALSE,
	] );
	if ( $cuser->role >= user::ROLE_ADMIN ) {
		bar_link( [
			'href' => season_href( 'events.php' ),
			'text' => 'συμβάντα',
			'icon' => 'fa-calendar-check-o',
			'hide_small' => FALSE,
			'hide_medium' => FALSE,
		] );
		bar_link( [
			'href' => season_href( 'teams.php' ),
			'text' => 'ομάδες',
			'icon' => 'fa-users',
			'hide_small' => FALSE,
			'hide_medium' => FALSE,
		] );
		bar_link( [
			'href' => site_href( 'locations.php' ),
			'text' => 'περιοχές',
			'icon' => 'fa-globe',
			'hide_small' => FALSE,
			'hide_medium' => FALSE,
		] );
		bar_link( [
			'href' => site_href( 'children.php' ),
			'text' => 'παιδιά',
			'icon' => 'fa-child',
			'hide_small' => FALSE,
			'hide_medium' => FALSE,
		] );
		bar_link( [
			'href' => site_href( 'users.php' ),
			'text' => 'χρήστες',
			'icon' => 'fa-user',
			'hide_small' => FALSE,
			'hide_medium' => FALSE,
		] );
	}
?>
	</div>
</div>
<?php
} );

page_nav_add( function() {
	global $cuser;
	if ( is_null( $cuser ) )
		return;
	$hash = md5( $cuser->email_address );
	$gravatar = sprintf( 'https://www.gravatar.com/avatar/%s?size=24&default=mm', $hash );
?>
<div class="w3-dropdown-hover w3-right">
	<button class="w3-button" title="<?= $cuser->email_address ?>">
		<img class="w3-circle" src="<?= $gravatar ?>" style="height: 24px; width: auto; margin: -4px 0px;" />
		<span class="w3-hide-small w3-hide-medium"><?= $cuser->email_address ?></span>
		<span class="fa fa-caret-down"></span>
	</button>
	<div class="w3-dropdown-content w3-bar-block w3-theme-l2">
<?php
	bar_link( [
		'href' => site_href( 'profile.php' ),
		'text' => 'προφίλ',
		'icon' => 'fa-pencil',
		'hide_small' => FALSE,
		'hide_medium' => FALSE,
	] );
	bar_link( [
		'href' => site_href( 'settings.php' ),
		'text' => 'ρυθμίσεις',
		'icon' => 'fa-cog',
		'hide_small' => FALSE,
		'hide_medium' => FALSE,
	] );
	bar_link( [
		'href' => site_href( 'logout.php' ),
		'text' => 'έξοδος',
		'icon' => 'fa-sign-out',
		'hide_small' => FALSE,
		'hide_medium' => FALSE,
	] );
?>
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

function page_body_add( callable $function, ...$arguments ) {
	global $page_bodies;
	$page_bodies[] = [
		'function'  => $function,
		'arguments' => $arguments,
	];
}

function form_section( array $fields, array $arguments = [] ) {
	if ( !array_key_exists( 'full_screen', $arguments ) )
		$arguments['full_screen'] = FALSE;
	if ( !array_key_exists( 'responsive', $arguments ) )
		$arguments['responsive'] = 'w3-col s12';
	if ( !array_key_exists( 'submit_icon', $arguments ) )
		$arguments['submit_icon'] = 'fa-floppy-o';
	if ( !array_key_exists( 'submit_text', $arguments ) )
		$arguments['submit_text'] = 'αποθήκευση';
	if ( !array_key_exists( 'recaptcha', $arguments ) )
		$arguments['recaptcha'] = FALSE;
	if ( $arguments['full_screen'] )
		echo '<section class="w3-panel">' . "\n";
	else
		echo '<section class="w3-panel w3-content">' . "\n";
	echo '<form class="form-ajax w3-card-4 w3-round w3-theme-l4" autocomplete="off" method="post">' . "\n";
	if ( $arguments['recaptcha'] )
		echo sprintf( '<div class="g-recaptcha" data-sitekey="%s" data-callback="form_ajax_recaptcha_callback" data-size="invisible"></div>', RECAPTCHA_SITE_KEY ) . "\n";
	if ( array_key_exists( 'header', $arguments ) ) {
		echo '<div class="w3-container">' . "\n";
		echo $arguments['header'];
		echo '</div>';
	}
	echo '<div class="w3-row-padding">' . "\n";
	foreach ( $fields as $field ) {
		echo sprintf( '<div class="w3-margin-top %s">', $arguments['responsive'] ) . "\n";
		$field->html();
		echo '</div>' . "\n";
	}
	echo '</div>' . "\n";
	echo '<div class="w3-container">' . "\n";
	echo '<div class="w3-section">' . "\n";
	echo '<button class="w3-button w3-round w3-theme-action" type="submit">' . "\n";
	echo sprintf( '<span class="fa %s"></span>', $arguments['submit_icon'] ) . "\n";
	echo sprintf( '<span>%s</span>', $arguments['submit_text'] ) . "\n";
	echo '</button>' . "\n";
	if ( array_key_exists( 'delete', $arguments ) ) {
		echo sprintf( '<a class="w3-button w3-round w3-red w3-right link-ajax" href="%s" data-confirm="οριστική διαγραφή;">', $arguments['delete'] ) . "\n";
		echo '<span class="fa fa-trash"></span>' . "\n";
		echo '<span>διαγραφή</span>' . "\n";
		echo '</a>' . "\n";
	}
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	if ( array_key_exists( 'footer', $arguments ) ) {
		echo '<div class="w3-container">' . "\n";
		echo $arguments['footer'];
		echo '</div>';
	}
	echo '</form>' . "\n";
	echo '</section>' . "\n";
?>
<script>

var form;

function form_ajax_recaptcha_callback( token ) {
	form.find( '.g-recaptcha-response' ).val( token );
	form.submit();
}

$( function() {

$( '.form-ajax' ).submit( function() {
	form = $( this );
	var recaptcha = form.find( '.g-recaptcha' );
	if ( recaptcha.length > 0 && recaptcha.find( '.g-recaptcha-response' ).val() === '' ) {
		recaptcha.data( 'callback', 'alert' );
		grecaptcha.execute();
		return false;
	}
	var btn = form.find( 'button[type="submit"]' );
	if ( btn.prop( 'disabled' ) )
		return false;
	btn.prop( 'disabled', true );
	var fa = btn.children( '.fa' );
	var cl = fa.prop( 'class' );
	fa.prop( 'class', 'fa fa-spinner fa-pulse' );
	$.post( form.prop( 'action' ), form.serialize() ).done( function( data ) {
		if ( typeof( data ) === 'object' ) {
			if ( data.hasOwnProperty( 'alert' ) )
				alert( data.alert );
			if ( data.hasOwnProperty( 'location' ) )
				location.href = data.location;
		} else {
			alert( data );
		}
	} ).fail( function( jqXHR ) {
		alert( jqXHR.statusText + ' ' + jqXHR.status );
	} ).always( function() {
		fa.prop( 'class', cl );
		btn.prop( 'disabled', false );
	} );
	if ( recaptcha.length > 0 ) {
		grecaptcha.reset();
		recaptcha.find( '.g-recaptcha-response' ).val( '' );
	}
	return false;
} );

} );
</script>
<?php
	if ( $arguments['recaptcha'] )
		echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>' . "\n";
}

page_body_add( function() {
	# TODO link and link-delete style like w3css
?>
<style>
.link {
	display: inline-block;
	white-space: nowrap;
	text-decoration: none;
	color: #000000;
}
.link:hover {
	color: #3a3a3a;
}
.link-delete {
	color: #f44336;
}
.link-delete:hover {
	color: #ff5722;
}
</style>
<script>
$( function() {

$( '.link-ajax' ).click( function() {
	var link = $( this );
	var fa = link.find( '.fa' );
	if ( fa.hasClass( 'fa-spinner' ) )
		return false;
	if ( link.data( 'confirm' ) !== undefined && !confirm( link.data( 'confirm' ) ) )
		return false;
	var cl = fa.prop( 'class' );
	fa.prop( 'class', 'fa fa-spinner fa-pulse' );
	$.post( link.prop( 'href' ), function( data ) {
		if ( typeof( data ) === 'object' ) {
			if ( data.hasOwnProperty( 'alert' ) )
				alert( data.alert );
			if ( data.hasOwnProperty( 'location' ) )
				location.href = data.location;
			if ( link.data( 'remove' ) !== undefined )
				link.parents( link.data( 'remove' ) ).remove();
		} else {
			alert( data );
		}
	} ).fail( function( jqXHR ) {
		alert( jqXHR.statusText + ' ' + jqXHR.status );
	} ).always( function() {
		fa.prop( 'class', cl );
	} );
	return false;
} );

} );
</script>
<style>
ul.relation>li {
	padding: 4px 8px;
	display: flex;
	justify-content: space-between;
	align-items: center;
}
ul.relation>li>* {
	margin: 4px 8px;
	flex-shrink: 0;
}
ul.relation>li:first-child>*:not(:last-child) {
	flex-shrink: 1;
}
ul.relation>li:not(:first-child) {
	flex-wrap: wrap;
}
ul.relation>li:not(:first-child)>* {
	flex-grow: 1;
}
ul.relation>li:not(:first-child)>a:first-child {
	width: calc( 100% - 16px );
}
ul.relation[data-relation="grade"]>li:not(:first-child)>label {
	width: calc( 100% / 3 - 16px );
}
</style>
<script>
$( function() {

$( 'ul.relation>li:not(:first-child)>a:first-child' ).click( function() {
	$( this ).siblings().children( 'input[type="checkbox"]' ).prop( 'checked', true );
	$.post( $( this ).prop( 'href' ) );
	return false;
} );
$( 'ul.relation>li:first-child>a:last-child' ).click( function() {
	$( this ).parent( 'li' ).siblings().children().children( 'input[type="checkbox"]' ).prop( 'checked', false );
	$.post( $( this ).prop( 'href' ) );
	return false;
} );
$( 'ul.relation>li:not(:first-child)>label>input[type="checkbox"]' ).change( function() {
	$.post( $( this ).data( $( this ).prop( 'checked' ) ? 'href-on' : 'href-off' ) );
} );

} );
</script>
<?php
} );


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
		<title><?= $page_title ?? SITE_NAME ?></title>
		<link rel="icon" href="<?= site_href( 'favicon-256.png' ) ?>" />
<?php
	foreach ( $page_styles as $style ) {
?>
		<link rel="stylesheet" href="<?= $style ?>" />
<?php
	}
?>
		<style>
body>.w3-bar:first-child {
	overflow: initial;
}
body>.w3-bar:first-child>.w3-dropdown-hover {
	position: relative;
}
body>.w3-bar:first-child>.w3-dropdown-hover>.w3-dropdown-content {
	min-width: initial;
	position: absolute;
}
body>.w3-bar:first-child>.w3-dropdown-hover>.w3-dropdown-content>.w3-bar-item {
	white-space: nowrap;
}
body>.w3-bar:first-child>.w3-dropdown-hover.w3-right>.w3-dropdown-content {
	right: 0px;
}
.action {
	position: fixed;
	right: 50px;
	bottom: 50px;
}

.flex {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 4px 8px;
}
.flex.flex-equal {
	padding: 8px;
}
.flex.flex-equal>* {
	margin: 8px;
}
.flex>* {
	margin: 4px 8px;
}
.flex>.flex-m6 { width: calc(100%/1 - 16px); }
@media (min-width:601px) {
	.flex>.flex-m6 { width: calc(100%/2 - 16px); }
}
@media (min-width:993px) {
	.flex>.flex-l4 { width: calc(100%/3 - 16px); }
}
		</style>
<?php
	foreach ( $page_scripts as $script ) {
?>
		<script src="<?= $script ?>"></script>
<?php
	}
?>
	</head>
	<body class="w3-theme-l5">
		<div class="w3-bar w3-theme">
<?php
	foreach ( $page_navs as $nav )
		$nav['function']( ...$nav['arguments'] );
?>
		</div>
		<h1 class="w3-panel w3-content w3-text-theme w3-center"><?= $page_title ?? SITE_NAME ?></h1>
<?php
	if ( !is_null( $cuser ) && ( is_null( $cuser->last_name ) || is_null( $cuser->first_name ) ) )
		page_message_add( sprintf( 'Συμπλήρωσε τα στοιχεία σου στο <a href="%s">προφίλ</a>.', site_href( 'profile.php' ) ), 'warning' );
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
		$body['function']( ...$body['arguments'] );
?>
	</body>
</html>
<?php
	exit;
}