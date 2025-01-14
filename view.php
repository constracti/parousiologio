<?php

require_once 'php/core.php';

privilege( user::ROLE_OBSER );

if ( !defined( 'INDEX' ) ) {

page_title_set( sprintf( 'Προβολή %d', $cseason->year ) );

page_nav_add( 'season_dropdown', [
	'href' => 'view.php',
	'text' => 'προβολή',
	'icon' => 'fa-list',
] );

}

$locations = location::select();
$teams = team::select( [
	'season_id' => $cseason->season_id,
] );
$grades = grade::select();
$items = ( function(): array {
	global $db;
	global $cseason;
	$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_team`.`team_id`, `xa_grade`.`category_id`, `xa_grade`.`grade_id`
FROM `xa_location`
LEFT JOIN `xa_team` ON `xa_team`.`location_id` = `xa_location`.`location_id` AND `xa_team`.`season_id` = ?
LEFT JOIN `xa_target` ON `xa_target`.`team_id` = `xa_team`.`team_id`
JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_target`.`grade_id`
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_location`.`location_id` ASC,
`xa_target`.`grade_id` ASC, `xa_team`.`team_id` ASC
	' );
	$stmt->bind_param( 'i', $cseason->season_id );
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
	if ( is_null( $item->team_id ) )
		return;
	global $teams;
	$team = $teams[ $item->team_id ];
	$href = site_href( 'presences.php', [ 'team_id' => $team->team_id ] );
	echo sprintf( '<a href="%s" class="flex w3-button w3-block w3-border-top w3-left-align" style="white-space: normal;">', $href ) . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
}, function( $item ) {
	if ( is_null( $item->team_id ) )
		return;
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

page_body_add( function() {
?>
<hr>
<section class="w3-panel w3-content w3-center">
	<p><a href="<?= season_href( 'location-null.php' ) ?>">παιδιά χωρίς περιοχή</a></p>
</section>
<?php
} );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= season_href( 'season-download.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="μεταφόρτωση">
		<span class="fa fa-download"></span>
	</a>
</section>
<?php
} );

page_html();
