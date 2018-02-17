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
	request_recaptcha();
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
		'location' => site_href(),
	] );
}

page_body_add( function() {
?>
<section class="w3-panel w3-content">
	<div class="w3-card-4 w3-round w3-theme-l4">
		<div class="w3-container">
			<h3>είσοδος με λογαριασμό κοινωνικής δικτύωσης</h3>
		</div>
		<div class="w3-container">
			<div class="w3-section">
				<a class="w3-button w3-round w3-red" href="<?= site_href( 'oauth2.php', [ 'provider' => 'google' ] ) ?>">
					<span class="fa fa-google"></span>
					<span class="w3-hide-small">Google</span>
				</a>
				<a class="w3-button w3-round w3-blue" href="<?= site_href( 'oauth2.php', [ 'provider' => 'microsoft' ] ) ?>">
					<span class="fa fa-windows"></span>
					<span class="w3-hide-small">Microsoft</span>
				</a>
				<a class="w3-button w3-round w3-purple" href="<?= site_href( 'oauth2.php', [ 'provider' => 'yahoo' ] ) ?>">
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
		sprintf( '<a class="w3-small w3-left" href="%s" title="εγγραφή">δεν έχω εγγραφεί</a>', site_href( 'register.php' ) ) . "\n" .
		sprintf( '<a class="w3-small w3-right" href="%s" title="επαναφορά κωδικού πρόσβασης">ξέχασα τον κωδικό μου</a>', site_href( 'repass.php' ) ) . "\n" .
		'</div>' . "\n",
	'submit_icon' => 'fa-sign-in',
	'submit_text' => 'είσοδος',
	'recaptcha' => TRUE,
] );

} elseif ( $cuser->role === user::ROLE_GUEST ) {

page_message_add( 'Περίμενε να εγκριθεί ο λογαριασμός σου από τη διαχείριση.', 'warning' );

} else {

page_nav_add( 'season_dropdown' );

define( 'INDEX', TRUE );
if ( $cuser->role >= user::ROLE_OBSER && $cuser->get_meta( 'index' ) === 'list' )
	require SITE_DIR . 'view.php';

$locations = location::select();
$teams = team::select();
$grades = grade::select();
$items = ( function(): array {
	global $db;
	global $cseason;
	global $cuser;
	$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_team`.`team_id`, `xa_grade`.`category_id`, `xa_grade`.`grade_id`
FROM `xa_team`
JOIN `xa_access` ON `xa_access`.`team_id` = `xa_team`.`team_id` AND `xa_access`.`user_id` = ?
JOIN `xa_location` ON `xa_location`.`location_id` = `xa_team`.`location_id`
LEFT JOIN `xa_target` ON `xa_target`.`team_id` = `xa_team`.`team_id`
JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_target`.`grade_id`
WHERE `xa_team`.`season_id` = ?
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_location`.`location_id` ASC,
`xa_target`.`grade_id` ASC, `xa_team`.`team_id` ASC
	' );
	$stmt->bind_param( 'ii', $cuser->user_id, $cseason->season_id );
	$stmt->execute();
	$rslt = $stmt->get_result();
	$stmt->close();
	$items = [];
	while ( !is_null( $item = $rslt->fetch_object() ) )
		$items[] = $item;
	$rslt->free();
	return $items;
} )();

$panel = new panel();
$panel->add( NULL, function( $item ) {
	echo '<section class="flex flex-equal" style="flex-wrap: wrap; justify-content: center; align-items: flex-start;">' . "\n";
}, function( $item ) {
	echo '</section>' . "\n";
} );
$panel->add( 'location_id', function ( $item ) {
	global $locations;
	$location = $locations[ $item->location_id ];
	global $teams;
	$team = $teams[ $item->team_id ];
	echo '<div class="flex-l4 flex-m6 flex-s12 w3-border w3-theme-l4">' . "\n";
	echo '<div class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $location->location_name ) . "\n";
	echo sprintf( '<div style="flex-shrink: 0;">%s</div>', $location->is_swarm ? 'ομάδα' : 'κατηχητικό' ) . "\n";
	echo '</div>' . "\n";
}, function( $item ) {
	echo '</div>' . "\n";
} );
$panel->add( 'team_id', function( $item ) {
	global $teams;
	$team = $teams[ $item->team_id ];
	$href = site_href( 'presences.php', [ 'team_id' => $team->team_id ] );
	echo sprintf( '<a href="%s" class="flex w3-button w3-block w3-border-top w3-left-align" style="white-space: normal;">', $href ) . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
}, function( $item ) {
	echo '</div>' . "\n";
	echo '</a>' . "\n";
} );
$panel->add( 'category_id', function( $item ) {
	if ( is_null( $item->category_id ) )
		return;
	echo '<div>' . "\n";
}, function ( $item ) {
	if ( is_null( $item->category_id ) )
		return;
	echo '</div>' . "\n";
} );
$panel->add( 'grade_id', function( $item ) {
	if ( is_null( $item->grade_id ) )
		return;
	global $grades;
	$grade = $grades[ $item->grade_id ];
	echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s</span>', $grade->grade_name ) . "\n";
} );
page_body_add( [ $panel, 'html' ], $items );

}

page_body_add( function() {
	echo '<footer class="w3-panel w3-center w3-small">' . "\n";
	echo '<span class="fa fa-envelope"></span>' . "\n";
	echo '<span>επικοινωνία:</span>' . "\n";
	echo sprintf( '<a href="mailto:%s">%s</a>', MAIL_USER, MAIL_USER ) . "\n";
	echo '</footer>' . "\n";
	echo '<footer class="w3-panel w3-center w3-small">' . "\n";
	echo sprintf( '<img src="%s" />', site_href( 'img/agonistes.png' ) ) . "\n";
	echo '<a href="https://agonistes.gr/" target="_blank" title="Χαρούμενοι Αγωνιστές">Χαρούμενοι Αγωνιστές</a>' . "\n";
	echo '<span>|</span>' . "\n";
	echo sprintf( '<img src="%s" />', site_href( 'img/synathlountes.png' ) ) . "\n";
	echo '<a href="https://synathlountes.agonistes.gr/" target="_blank" title="Συναθλούντες">Συναθλούντες</a>' . "\n";
	echo '</footer>' . "\n";
} );

page_body_add( 'google_analytics' );

page_html();