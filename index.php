<?php

require_once 'php/page.php';

$field_success = TRUE;
$fields = [
	'email_address' => new field( 'email_address', [
		'type' => 'email',
		'placeholder' => 'διεύθυνση email',
		'required' => TRUE,
	] ),
	'password' => new field( 'password', [
		'type' => 'password',
		'placeholder' => 'κωδικός πρόσβασης',
		'required' => TRUE,
	] ),
];

function social_login_html() {
?>
<section class="w3-panel w3-content">
	<div class="w3-card-4 w3-round w3-theme-l4">
		<div class="w3-container">
			<h3>είσοδος με λογαριασμό κοινωνικής δικτύωσης</h3>
		</div>
		<div class="w3-container">
			<div class="w3-section">
				<a class="w3-button w3-round w3-red" href="<?= SITE_URL ?>oauth2.php?provider=google&login">
					<span class="fa fa-google-plus"></span>
					<span class="w3-hide-small">Google</span>
				</a>
				<a class="w3-button w3-round w3-green" href="<?= SITE_URL ?>oauth2.php?provider=microsoft&login">
					<span class="fa fa-windows"></span>
					<span class="w3-hide-small">Microsoft</span>
				</a>
				<a class="w3-button w3-round w3-purple" href="<?= SITE_URL ?>oauth2.php?provider=yahoo&login">
					<span class="fa fa-yahoo"></span>
					<span class="w3-hide-small">Yahoo</span>
				</a>
			</div>
		</div>
	</div>
</section>
<?php
}

function home_html() {
	global $cuser;
	global $cyear;
	$cseason = season::select_by( 'year', $cyear );
	$teams = $cuser->select_index_teams( $cseason->season_id );
	$panel = new panel();
	$panel->add( 'location_id', function( team $team ) {
?>
			<div class="w3-container w3-col l4 m6 s12 w3-margin-bottom" style="float: none;">
				<ul class="w3-ul w3-card-4 w3-theme-l4">
					<li class="w3-container w3-theme">
						<div style="display: flex; justify-content: space-between; align-items:center;">
							<h3 style="margin: 0px;"><?= $team->location_name ?></h3>
							<div class="w3-right-align"><?= $team->is_swarm ? 'ομάδα' : 'κατηχητικό' ?><br /><?= $team->on_sunday ? 'Κυριακή' : 'Σάββατο' ?></div>
						</div>
					</li>
<?php
	}, function( team $team ) {
?>
				</ul>
			</div>
<?php
	} );
	$panel->add( 'team_id', function( team $team ) {
?>
					<li>
						<div style="display: flex; justify-content: space-between; align-items: center;">
							<div>
								<p style="margin: 0px;"><?= $team->team_name ?></p>
								<div>
<?php
	}, function ( team $team ) {
?>
								</div>
							</div>
							<div style="flex-shrink: 0;">
								<div class="w3-bar w3-round">
									<a class="w3-button w3-bar-item w3-theme-action" href="<?= SITE_URL ?>presences.php?mode=desktop&team_id=<?= $team->team_id ?>" title="υπολογιστής" style="min-width: 50px;">
										<span class="fa fa-desktop"></span>
									</a>
									<a class="w3-button w3-bar-item w3-theme" href="<?= SITE_URL ?>presences.php?mode=mobile&team_id=<?= $team->team_id ?>" title="κινητό" style="min-width: 50px;">
										<span class="fa fa-mobile"></span>
									</a>
								</div>
							</div>
						</div>
					</li>
<?php
	} );
	$panel->add( 'grade_id', function( team $team ) {
?>
									<span class="w3-tag w3-small w3-round w3-theme"><?= $team->grade_name ?></span>
<?php
	} );
?>
		<div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">
<?php
	$panel->html( $teams );
?>
		</div>
<?php
}

if ( is_null( $cuser ) ) {
	$field_success && ( function( array $fields ) {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
			return;
		$email_address = $fields['email_address']->value();
		$password = $fields['password']->value();
		$user = user::select_by( 'email_address', $email_address );
		if ( is_null( $user ) )
			return page_message_add( 'Πραγματοποίησε εγγραφή ή συνδέσου με λογαριασμό κοινωνικής δικτύωσης.', 'error' );
		if ( is_null( $user->password_hash ) )
			return page_message_add( 'Δεν έχεις ορίσει κωδικό. Συνδέσου με λογαριασμό κοινωνικής δικτύωσης.', 'warning' );
		if ( !password_verify( $password, $user->password_hash ) )
			return page_message_add( 'Πληκτρολόγησε το σωστό κωδικό πρόσβασης.', 'error' );
		if ( $user->role_id === user::ROLE_UNVER )
			return page_message_add( 'Ακολούθησε πρώτα το σύνδεσμο επαλήθευσης από τα εισερχόμενά σου.', 'error' );
		epoint::write( $user->user_id );
	} )( $fields );
	page_title_set( 'Είσοδος' );
	page_body_add( 'social_login_html' );
	page_body_add( 'form_html', $fields, [
		'header' => '<h3 class="w3-section">είσοδος με τοπικό λογαριασμό</h3>' . "\n",
		'footer' => '<div class="w3-section w3-clear">' . "\n" .
			sprintf( '<a class="w3-small w3-left" href="%sregister.php" title="εγγραφή">δεν έχω εγγραφεί</a>', SITE_URL ) . "\n" .
			sprintf( '<a class="w3-small w3-right" href="%srepass.php" title="επαναφορά κωδικού πρόσβασης">ξέχασα τον κωδικό μου</a>', SITE_URL ) . "\n" .
			'</div>' . "\n",
		'submit_icon' => 'fa-sign-in',
		'submit_text' => 'είσοδος',
	] );
} else {
	page_nav_add( 'season_dropdown' );
	page_body_add( 'home_html' );
}

page_html();
