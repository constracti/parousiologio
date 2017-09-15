<?php

require_once 'php/page.php';

function index_login_html() {
?>
		<section class="w3-panel w3-content">
			<div class="w3-container w3-card-4 w3-round w3-theme-l4">
				<h3>είσοδος με λογαριασμό κοινωνικής δικτύωσης</h3>
				<div class="w3-section">
					<a class="w3-button w3-round w3-red" href="<?= HOME_URL ?>oauth2.php?provider=google&login">
						<span class="fa fa-google-plus"></span>
						<span class="w3-hide-small">Google</span>
					</a>
					<a class="w3-button w3-round w3-green" href="<?= HOME_URL ?>oauth2.php?provider=microsoft&login">
						<span class="fa fa-windows"></span>
						<span class="w3-hide-small">Microsoft</span>
					</a>
					<a class="w3-button w3-round w3-purple" href="<?= HOME_URL ?>oauth2.php?provider=yahoo&login">
						<span class="fa fa-yahoo"></span>
						<span class="w3-hide-small">Yahoo</span>
					</a>
				</div>
			</div>
		</section>
		<section class="w3-panel w3-content">
			<form class="w3-container w3-card-4 w3-round w3-theme-l4" method="post">
				<h3>είσοδος με τοπικό λογαριασμό</h3>
				<div class="w3-section">
					<label>διεύθυνση email *</label>
					<input class="w3-input" name="email_address" type="email" required="required" placeholder="διεύθυνση email" value="<?= $_POST['email_address'] ?? '' ?>" />
				</div>
				<div class="w3-section">
					<label>κωδικός πρόσβασης *</label>
					<input class="w3-input" name="password" type="password" required="required" placeholder="κωδικός πρόσβασης" />
				</div>
				<div class="w3-section">
					<button class="w3-button w3-round w3-theme-action" type="submit">
						<span class="fa fa-sign-in"></span>
						<span>είσοδος</span>
					</button>
				</div>
				<hr />
				<div class="w3-section w3-clear">
					<a class="w3-small w3-left" href="<?= HOME_URL ?>register.php" title="εγγραφή">δεν έχω εγγραφεί</a>
					<a class="w3-small w3-right" href="<?= HOME_URL ?>repass.php" title="επαναφορά κωδικού πρόσβασης">ξέχασα τον κωδικό μου</a>
				</div>
			</form>
		</section>
<?php
}

function index_home_html() {
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
									<a class="w3-button w3-bar-item w3-theme-action" href="<?= HOME_URL ?>presences.php?mode=desktop&team_id=<?= $team->team_id ?>" title="υπολογιστής" style="min-width: 50px;">
										<span class="fa fa-desktop"></span>
									</a>
									<a class="w3-button w3-bar-item w3-theme" href="<?= HOME_URL ?>presences.php?mode=mobile&team_id=<?= $team->team_id ?>" title="κινητό" style="min-width: 50px;">
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
	( function() {
		if ( !array_key_exists( 'email_address', $_POST ) || !array_key_exists( 'password', $_POST ) )
			return;
		$email_address = filter_var( $_POST['email_address'], FILTER_VALIDATE_EMAIL );
		if ( $email_address === FALSE )
			return page_message_add( 'Πληκτρολόγησε μία έγκυρη διεύθυνση email.', 'error' );
		$user = user::select_by( 'email_address', $email_address );
		if ( is_null( $user ) )
			return page_message_add( 'Πραγματοποίησε εγγραφή ή συνδέσου με λογαριασμό κοινωνικής δικτύωσης.', 'error' );
		if ( is_null( $user->password_hash ) )
			return page_message_add( 'Δεν έχεις ορίσει κωδικό. Συνδέσου με λογαριασμό κοινωνικής δικτύωσης.', 'warning' );
		if ( !password_verify( $_POST['password'], $user->password_hash ) )
			return page_message_add( 'Πληκτρολόγησε το σωστό κωδικό πρόσβασης.', 'error' );
		if ( $user->role_id === user::ROLE_UNVER )
			return page_message_add( 'Ακολούθησε πρώτα το σύνδεσμο επαλήθευσης από τα εισερχόμενά σου.', 'error' );
		epoint::write( $user->user_id );
	} )();
	page_title_set( 'Είσοδος' );
	page_body_add( 'index_login_html' );
} else {
	page_nav_add( 'season_dropdown' );
	page_body_add( 'index_home_html' );
}

page_html();
