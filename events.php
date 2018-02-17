<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Συμβάντα' );

page_nav_add( 'season_dropdown', [
	'href' => 'events.php',
	'text' => 'συμβάντα',
	'icon' => 'fa-calendar-check-o',
] );

$events = event::select( [
	'season_id' => $cseason->season_id,
] );
$grades = grade::select();
$items = ( function(): array {
	global $db;
	global $cseason;
	$stmt = $db->prepare( '
SELECT `xa_event`.`event_id`, `xa_grade`.`category_id`, `xa_regard`.`grade_id`
FROM `xa_event`
LEFT JOIN `xa_regard` ON `xa_regard`.`event_id` = `xa_event`.`event_id`
LEFT JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_regard`.`grade_id`
WHERE `xa_event`.`season_id` = ?
ORDER BY `xa_event`.`event_date` DESC, `xa_event`.`event_id` DESC, `xa_regard`.`grade_id` ASC
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
$panel->add( function( $item ) {
	global $events;
	$event = $events[ $item->event_id ];
	$dt = new dtime( $event->event_date );
	return $dt->format( 'Y-m' );
}, function( $item ) {
	global $events;
	$event = $events[ $item->event_id ];
	$dt = new dtime( $event->event_date );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	echo '<li class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s %s</div>', $dt->month_name(), $dt->format( 'Y' ) ) . "\n";
	echo '</li>' . "\n";
}, function( $item ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'event_id', function( $item ) {
	global $events;
	$event = $events[ $item->event_id ];
	$dt = new dtime( $event->event_date );
	echo '<li class="flex">' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<span class="w3-tag w3-round w3-theme-action" style="font-size: small;">%s, %s</span>', $dt->weekday_short_name(), $dt->format( 'j' ) ) . "\n";
	if ( !is_null( $event->event_name ) )
		echo sprintf( '<span>%s</span>', $event->event_name ) . "\n";
}, function( $item ) {
	global $events;
	$event = $events[ $item->event_id ];
	echo '</div>' . "\n";
	echo '<div style="flex-shrink: 0;">' . "\n";
	$href = site_href( 'event-update.php', [ 'event_id' => $event->event_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-green" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
	$href = site_href( 'event-delete.php', [ 'event_id' => $event->event_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
$panel->add( 'category_id', function( $item ) {
	if ( is_null( $item->category_id ) )
		return;
	echo '<div>' . "\n";
}, function($item ) {
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
	<a href="<?= season_href( 'event-insert.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

page_html();
