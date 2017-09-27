<?php

require_once 'php/core.php';

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

page_body_add( function() {
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
} );

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

$panel = new panel();
$panel->add( NULL, function( team $team ) {
	echo '<section class="flex flex-equal" style="flex-wrap: wrap; justify-content: center; align-items: flex-start;">' . "\n";
}, function( team $team ) {
	echo '</section>' . "\n";
} );
$panel->add( 'location_id', function( team $team ) {
	echo '<div class="flex-l4 flex-m6 flex-s12 w3-border w3-theme-l4">' . "\n";
	echo '<div class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $team->location_name ) . "\n";
	echo sprintf( '<div style="flex-shrink: 0; text-align: right;">%s<br />%s</div>', $team->is_swarm ? 'ομάδα' : 'κατηχητικό', $team->on_sunday ? 'Κυριακή' : 'Σάββατο' ) . "\n";
	echo '</div>' . "\n";
}, function( team $team ) {
	echo '</div>' . "\n";
} );
$panel->add( 'team_id', function( team $team ) {
	if ( is_null( $team->team_id ) )
		return;
	$href = SITE_URL . sprintf( 'presences.php?team_id=%d', $team->team_id );
	echo sprintf( '<a href="%s" class="flex w3-button w3-block w3-border-top w3-left-align" style="white-space: normal;">', $href ) . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
	echo '<div>' . "\n";
	$panel = new panel();
	$panel->add( 'category_id', function( grade $grade ) {
		echo '<div>' . "\n";
	}, function( grade $grade ) {
		echo '</div>' . "\n";		
	} );
	$panel->add( 'grade_id', function( grade $grade ) {
		echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s</span>', $grade->grade_name ) . "\n";
	} );
	$panel->html( $team->select_grades() );
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '</a>' . "\n";
} );
if ( $cuser->role_id >= user::ROLE_OBSER && $cuser->get_meta( 'index' ) === 'list' )
	$teams = team::select_admin();
else
	$teams = $cuser->select_teams();
page_body_add( [ $panel, 'html' ], $teams );

}

page_html();