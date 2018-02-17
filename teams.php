<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Ομάδες' );

page_nav_add( 'season_dropdown', [
	'href' => 'teams.php',
	'text' => 'ομάδες',
	'icon' => 'fa-users',
] );

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
LEFT JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_target`.`grade_id`
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_location`.`location_id` ASC,
`xa_grade`.`grade_id` ASC, `xa_team`.`team_id` ASC
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
$team_users = ( function(): array {
	global $db;
	global $cseason;
	$stmt = $db->prepare( '
SELECT `xa_team`.`team_id`, `xa_user`.`user_id`, `xa_user`.`last_name`, `xa_user`.`first_name`
FROM `xa_team`
LEFT JOIN `xa_access` ON `xa_access`.`team_id` = `xa_team`.`team_id`
LEFT JOIN `xa_user` ON `xa_user`.`user_id` = `xa_access`.`user_id`
WHERE `xa_team`.`season_id` = ?
ORDER BY `xa_team`.`team_id` ASC, `xa_user`.`last_name` ASC, `xa_user`.`first_name` ASC, `xa_user`.`user_id` ASC
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
$panel->add( 'team_id', function( $item ) {
	global $teams;
	$team = $teams[ $item->team_id ];
	$team->users = [];
} );
$panel->add( 'user_id', function( $item ) {
	if ( is_null( $item->user_id ) )
		return;
	global $teams;
	$team = $teams[ $item->team_id ];
	$team->users[] = $item;
} );
page_body_add( [ $panel, 'html' ], $team_users );

$panel = new panel();
$panel->add( 'location_id', function( $item ) {
	global $locations;
	$location = $locations[ $item->location_id ];
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	echo '<li class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $location->location_name ) . "\n";
	echo sprintf( '<div style="flex-shrink: 0;">%s</div>', $location->is_swarm ? 'ομάδα' : 'κατηχητικό' ) . "\n";
	echo '</li>' . "\n";
}, function( $item ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'team_id', function( $item ) {
	if ( is_null( $item->team_id ) )
		return;
	global $teams;
	$team = $teams[ $item->team_id ];
	echo '<li class="flex">' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
}, function( $item ) {
	if ( is_null( $item->team_id ) )
		return;
	global $teams;
	$team = $teams[ $item->team_id ];
	if ( !empty( $team->users ) ) {
		echo '<div>' . "\n";
		foreach ( $team->users as $user )
			echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s %s</span>', $user->last_name, $user->first_name ) . "\n";
		echo '</div>' . "\n";
	}
	echo '</div>' . "\n";
	echo '<div style="flex-shrink: 0;">' . "\n";
	$href = site_href( 'team-update.php', [ 'team_id' => $team->team_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-green" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
	$href = site_href( 'team-delete.php', [ 'team_id' => $team->team_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
$panel->add( 'category_id', function( $item ) {
	if ( is_null( $item->category_id ) )
		return;
	echo '<div>' . "\n";
}, function( $item ) {
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
<section class="action">
	<a href="<?= season_href( 'team-insert.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

page_html();
