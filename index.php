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
	$panel = new panel();
	$panel->add( 'location_id', function( team $team ) {
?>
<section class="w3-container w3-col l4 m6 s12 w3-margin-bottom" style="float: none;">
	<ul class="w3-ul w3-card-4 w3-theme-l4 list">
		<li class="w3-theme">
			<div style="font-size: large;"><?= $team->location_name ?></div>
			<div style="text-align: right;"><?= $team->is_swarm ? 'ομάδα' : 'κατηχητικό' ?><br /><?= $team->on_sunday ? 'Κυριακή' : 'Σάββατο' ?></div>
		</li>
<?php
	}, function( team $team ) {
		echo '</ul>' . "\n";
		echo '</section>' . "\n";
	} );
	$panel->add( 'team_id', function( team $team ) {
		echo '<li>' . "\n";
		echo '<div>' . "\n";
		echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
		echo '<div>' . "\n";
		foreach ( $team->get_grades() as $grade )
			echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s</span>', $grade->grade_name ) . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
		echo '<div>' . "\n";
		$href = SITE_URL . sprintf( 'presences.php?mode=desktop&team_id=%d', $team->team_id );
		echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme-action" title="υπολογιστής"><span class="fa fa-desktop"></span></a>', $href ) . "\n";
		$href = SITE_URL . sprintf( 'presences.php?mode=mobile&team_id=%d', $team->team_id );
		echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme" title="κινητό"><span class="fa fa-mobile"></span></a>', $href ) . "\n";
		echo '</div>' . "\n";
		echo '</li>' . "\n";
	} );
	echo '<div style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center;">' . "\n";
	$panel->html( $cuser->get_index() );
	echo '</div>' . "\n";
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