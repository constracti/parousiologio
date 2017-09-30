<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Περιοχές' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'locations.php' ),
	'text' => 'περιοχές',
	'icon' => 'fa-globe',
	'hide_medium' => FALSE,
] );

$seasons = season::select();
$locations = location::select();
$items = ( function(): array {
	global $db;
	$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_season`.`season_id`, COUNT( `xa_team`.`team_id` ) AS `teams`
FROM `xa_location`
JOIN `xa_season`
LEFT JOIN `xa_team` ON `xa_team`.`location_id` = `xa_location`.`location_id` AND `xa_team`.`season_id` = `xa_season`.`season_id`
GROUP BY `xa_location`.`location_id`, `xa_season`.`season_id`
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_location`.`location_id` ASC,
`xa_season`.`year` DESC, `xa_season`.`season_id` DESC
	' );
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
$panel->add( function( $item ): int {
	global $locations;
	$location = $locations[ $item->location_id ];
	return $location->is_swarm;
}, function( $item ) {
	global $locations;
	$location = $locations[ $item->location_id ];
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	echo '<li class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $location->is_swarm ? 'ομάδες' : 'κατηχητικά' ) . "\n";
	echo '</li>' . "\n";
}, function( $item ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'location_id', function( $item ) {
	global $locations;
	$location = $locations[ $item->location_id ];
	echo '<li class="flex">' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $location->location_name ) . "\n";
	echo '<div>' . "\n";
}, function( $item ) {
	global $locations;
	$location = $locations[ $item->location_id ];
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '<div style="flex-shrink: 0;">' . "\n";
	$href = site_href( 'location-update.php', [ 'location_id' => $location->location_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-green" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
	$href = site_href( 'location-delete.php', [ 'location_id' => $location->location_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
$panel->add( 'season_id', function( $item ) {
	global $seasons;
	$season = $seasons[ $item->season_id ];
	if ( $item->teams !== 0 )
		echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%d: %d</span>', $season->year, $item->teams ) . "\n";
} );
page_body_add( [ $panel, 'html' ], $items );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= site_href( 'location-insert.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

page_html();