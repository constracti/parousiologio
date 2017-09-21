<?php

require_once 'php/core.php';

function social_login_html() {
?>
<section class="w3-panel w3-content">
	<div class="w3-card-4 w3-round w3-theme-l4">
		<div class="w3-container">
			<h3>είσοδος με λογαριασμό κοινωνικής δικτύωσης</h3>
		</div>
		<div class="w3-container">
			<div class="w3-section">
				<a class="w3-button w3-round w3-red" href="<?= SITE_URL ?>oauth2.php?provider=google">
					<span class="fa fa-google-plus"></span>
					<span class="w3-hide-small">Google</span>
				</a>
				<a class="w3-button w3-round w3-green" href="<?= SITE_URL ?>oauth2.php?provider=microsoft">
					<span class="fa fa-windows"></span>
					<span class="w3-hide-small">Microsoft</span>
				</a>
				<a class="w3-button w3-round w3-purple" href="<?= SITE_URL ?>oauth2.php?provider=yahoo">
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
<section class="w3-container w3-col l4 m6 s12 w3-margin-bottom" style="float: none;">
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
</section>
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
	$fields = [
		'email_address' => new field_email( 'email_address', [
			'placeholder' => 'διεύθυνση email',
			'required' => TRUE,
		] ),
		'password' => new field_password( 'password', [
			'placeholder' => 'κωδικός πρόσβασης',
			'required' => TRUE,
		] ),
	];
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		$email_address = $fields['email_address']->post();
		$password = $fields['password']->post();
		$user = user::select_by_email_address( $email_address );
		if ( is_null( $user ) )
			failure( sprintf( 'Η διεύθυνση email %s δεν αντιστοιχεί σε κάποιον εγγεγραμμένο χρήστη.', $email_address ) );
		if ( is_null( $user->password_hash ) )
			failure( 'Δεν έχεις ορίσει κωδικό. Συνδέσου με λογαριασμό κοινωνικής δικτύωσης.' );
		if ( !password_verify( $password, $user->password_hash ) )
			failure( 'Πληκτρολόγησε το σωστό κωδικό πρόσβασης.' );
		epoint::write( $user->user_id );
		success( [
			'location' => SITE_URL,
		] );
	}
	page_title_set( 'Είσοδος' );
	page_body_add( 'social_login_html' );
	page_body_add( 'form_section', $fields, [
		'header' => '<h3 class="w3-section">είσοδος με τοπικό λογαριασμό</h3>' . "\n",
		'footer' => '<div class="w3-section w3-clear">' . "\n" .
			sprintf( '<a class="w3-small w3-left" href="%sregister.php" title="εγγραφή">δεν έχω εγγραφεί</a>', SITE_URL ) . "\n" .
			sprintf( '<a class="w3-small w3-right" href="%srepass.php" title="επαναφορά κωδικού πρόσβασης">ξέχασα τον κωδικό μου</a>', SITE_URL ) . "\n" .
			'</div>' . "\n",
		'submit_icon' => 'fa-sign-in',
		'submit_text' => 'είσοδος',
	] );
} elseif ( $cuser->role_id === user::ROLE_GUEST ) {
	page_message_add( 'Περίμενε να εγκριθεί ο λογαριασμός σου από τη διαχείριση.', 'warning' );
} else {
	page_nav_add( 'season_dropdown' );
	page_body_add( 'home_html' );
}

page_html();