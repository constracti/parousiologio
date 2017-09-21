<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Συμβάντα' );

page_nav_add( 'season_dropdown', [
	'href' => 'events.php',
	'text' => 'συμβάντα',
	'icon' => 'fa-calendar-check-o',
] );

$events = event::select_admin();
$panel = new panel();
$panel->add( function( event $event ) {
	$dt = dtime::from_sql( $event->date, dtime::DATE );
	return $dt->format( 'Y-m' );
}, function( event $event ) {
	$dt = dtime::from_sql( $event->date, dtime::DATE );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	echo '<li class="w3-container w3-theme">' . "\n";
	echo sprintf( '<h3 style="margin: 0px;">%s %s</h3>', $dt->month_name(), $dt->format( 'Y' ) ) . "\n";
	echo '</li>' . "\n";
}, function( event $event ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'event_id', function( event $event ) {
	$dt = dtime::from_sql( $event->date, dtime::DATE );
	echo '<li>' . "\n";
	echo '<div style="display: flex; justify-content: space-between; align-items: center;">' . "\n";
	echo '<div>' . "\n";
	echo '<p style="margin: 0px;">' . "\n";
	echo sprintf( '<span class="w3-tag w3-round w3-theme-action" style="font-size: small;">%s, %s</span>', $dt->weekday_short_name(), $dt->format( 'j' ) ) . "\n";
	if ( !is_null( $event->name ) )
		echo sprintf( '<span>%s</span>', $event->name ) . "\n";
	echo '</p>' . "\n";
}, function( event $event ) {
	echo '</div>' . "\n";
	echo '<div style="flex-shrink: 0;">' . "\n";
	echo '<div class="w3-bar w3-round">' . "\n";
	echo sprintf( '<a href="%sevent-update.php?event_id=%d" class="w3-bar-item w3-button w3-theme" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', SITE_URL, $event->event_id ) . "\n";
	echo sprintf( '<a href="%sevent-delete.php?event_id=%d" class="w3-bar-item w3-button w3-red link-ajax" data-confirm="οριστική διαγραφή;" title="διαγραφή" data-remove="li"><span class="fa fa-trash"></span></a>', SITE_URL, $event->event_id ) . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
$panel->add( 'category_id', function( event $event ) {
	echo '<div>' . "\n";
}, function( event $event ) {
	echo '</div>' . "\n";
} );
$panel->add( 'grade_id', function( event $event ) {
	if ( is_null( $event->grade_id ) )
		return;
	echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s</span>', $event->grade_name ) . "\n";
} );
page_body_add( [ $panel, 'html' ], $events );

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