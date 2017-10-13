<?php

require_once 'php/core.php';

privilege( user::ROLE_OBSER );

page_title_set( sprintf( 'Σύνοψη %d', $cseason->year ) );

page_style_add( site_href( 'css/presences-container.css', [ 'v' => '0.1' ] ) );
page_script_add( site_href( 'js/presences-container.js', [ 'v' => '0.1' ] ) );
page_script_add( site_href( 'js/presences-month.js', [ 'v' => '0.1' ] ) );
page_script_add( site_href( 'js/presences-modal.js', [ 'v' => '0.1' ] ) );

page_nav_add( 'season_dropdown', [
	'href' => 'summary.php',
	'text' => 'σύνοψη',
	'icon' => 'fa-table',
] );

$locations = location::select();
$teams = team::select( [
	'season_id' => $cseason->season_id,
] );
$events = event::select( [
	'season_id' => $cseason->season_id,
], [
	'event_date' => 'ASC',
	'event_id' => 'ASC',
] );
foreach ( $events as $event ) {
	$dt = new dtime( $event->event_date );
	$event->title = sprintf( '%s, %s', $dt->weekday_short_name(), $dt->format( 'j/n' ) );
	if ( !is_null( $event->event_name ) )
		$event->title .= sprintf( ': %s', $event->event_name );
}
$presences = ( function(): array {
	global $db;
	global $cseason;
	$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_team`.`team_id`, `xa_event`.`event_id`, COUNT(`xa_presence`.`child_id`) AS `presences`
FROM `xa_team`
JOIN `xa_location` ON `xa_location`.`location_id` = `xa_team`.`location_id`
LEFT JOIN `xa_target` ON `xa_target`.`team_id` = `xa_team`.`team_id`
LEFT JOIN `xa_follow` ON `xa_follow`.`season_id` = `xa_team`.`season_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id` AND `xa_follow`.`location_id` = `xa_location`.`location_id`
JOIN `xa_event` ON `xa_event`.`season_id` = `xa_team`.`season_id`
LEFT JOIN `xa_presence` ON `xa_presence`.`child_id` = `xa_follow`.`child_id` AND `xa_presence`.`event_id` = `xa_event`.`event_id`
WHERE `xa_team`.`season_id` = ?
GROUP BY `xa_location`.`location_id`, `xa_team`.`team_id`, `xa_event`.`event_id`
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC,
MIN(`xa_target`.`grade_id`) ASC, `xa_team`.`team_id` ASC,
`xa_event`.`event_date` ASC, `xa_event`.`event_id` ASC
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
	global $events;
	echo '<div id="presences-container" class="flex" style="justify-content: center; align-items: flex-start;">' . "\n";
	echo '<div id="presences-sidebar" class="w3-border-top">' . "\n";
	foreach ( array_reverse( $events, TRUE ) as $event ) {
		$dt = new dtime( $event->event_date );
		echo sprintf( '<a class="flex presences-event w3-button w3-border-bottom" data-event="%d">', $event->event_id ) . "\n";
		echo '<div>' . "\n";
		echo sprintf( '<time datetime="%s">%s, %s</time>', $dt->format( dtime::DATE ), $dt->weekday_short_name(), $dt->format( 'j/n' ) );
		if ( !is_null( $event->event_name ) )
			echo sprintf( ': <span>%s</span>', $event->event_name ) . "\n";
		echo '</div>' . "\n";
		echo sprintf( '<span class="presences-event-sum" data-event="%d"></span>', $event->event_id ) . "\n";
		echo '</a>' . "\n";
	}
	echo '</div>' . "\n";
	echo '<div id="presences-main">' . "\n";
	echo '<table id="presences-table" class="w3-border w3-striped w3-hoverable" style="border-collapse: collapse;">' . "\n";
	echo '<thead class="w3-theme">' . "\n";
	echo '<tr>' . "\n";
	echo '<th rowspan="2">ομάδα</th>' . "\n";
	$panel = new panel();
	$panel->add( function( event $event ): string {
		$dt = new dtime( $event->event_date );
		return $dt->format( 'Y-m' );
	}, function( event $event ) {
		global $month_events;
		$month_events = 0;
	}, function( event $event ) {
		global $month_events;
		$dt = new dtime( $event->event_date );
		echo sprintf( '<th colspan="%d" class="presences-month" data-month="%s">%s</th>',
			$month_events,
			$dt->format( 'Y-m' ),
			$dt->month_name()
		) . "\n";
	} );
	$panel->add( 'event_id', function( event $event ) {
		global $month_events;
		$month_events++;
	} );
	$panel->html( $events );
	echo '<th rowspan="2">παρουσίες</th>' . "\n";
	echo '</tr>' . "\n";
	echo '<tr>' . "\n";
	foreach ( $events as $event ) {
		$dt = new dtime( $event->event_date );
		echo sprintf( '<th class="presences-event presences-month w3-center" data-event="%d" data-month="%s" title="%s">%s</th>',
			$event->event_id,
			$dt->format( 'Y-m' ),
			$event->title,
			$dt->format( 'j' )
		) . "\n";
	}
	echo '</tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody class="w3-border-bottom">' . "\n";
}, function( $item ) {
	global $events;
	global $teams;
	echo '</tbody>' . "\n";
	echo '<tfoot class="w3-theme">' . "\n";
	echo '<tr>' . "\n";
	echo sprintf( '<th>%d</th>', count( $teams ) ) . "\n";
	foreach ( $events as $event ) {
		$dt = new dtime( $event->event_date );
		echo sprintf( '<th class="presences-event presences-month presences-event-sum w3-right-align" data-event="%d" data-month="%s" title="%s"></th>',
			$event->event_id,
			$dt->format( 'Y-m' ),
			$event->title
		) . "\n";
	}
	echo '<th class="presences-total-sum w3-right-align"></th>' . "\n";
	echo '</tr>' . "\n";
	echo '</tfoot>' . "\n";
	echo '</table>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
?>
<script>

$.fn.extend( {
	sum: function() {
		var sum = 0;
		this.each( function() {
			sum += parseInt( $( this ).html() );
		} );
		return sum;
	},
} );

$( function() {

$( '#presences-table .presences-team-sum' ).each( function() {
	var team = $( this ).data( 'team' );
	var sum = $( '.presences-team[data-team="' + team + '"]' ).sum();
	$( '.presences-team-sum[data-team="' + team + '"]' ).html( sum );
} );

$( '#presences-table .presences-location' ).each( function() {
	var location = $( this ).data( 'location' );
	var event = $( this ).data( 'event' );
	var sum = $( '.presences-team[data-location="' + location + '"][data-event="' + event + '"]' ).sum();
	$( '.presences-location[data-location="' + location + '"][data-event="' + event + '"]' ).html( sum );
} );

$( '#presences-table .presences-location-sum' ).each( function() {
	var location = $( this ).data( 'location' );
	var sum = $( '.presences-location[data-location="' + location + '"]' ).sum();
	$( '.presences-location-sum[data-location="' + location + '"]' ).html( sum );
} );

$( '#presences-table .presences-event-sum' ).each( function() {
	var event = $( this ).data( 'event' );
	var sum = $( '.presences-location[data-event="' + event + '"]' ).sum();
	$( '.presences-event-sum[data-event="' + event + '"]' ).html( sum );
} );

$( '#presences-table .presences-total-sum' ).each( function() {
	var sum = $( '.presences-location-sum' ).sum();
	$( '.presences-total-sum' ).html( sum );
} );

} );
</script>
<?php
} );
$panel->add( 'location_id', function( $item ) {
	global $events;
	global $locations;
	$location = $locations[ $item->location_id ];
	echo '<tr class="w3-border-top" style="font-weight: bold;">' . "\n";
	echo sprintf( '<td>%s</td>', $location->location_name ) . "\n";
	foreach ( $events as $event ) {
		$dt = new dtime( $event->event_date );
		echo sprintf( '<td class="presences-event presences-month presences-location w3-right-align" data-event="%d" data-month="%s" title="%s" data-location="%d"></td>',
			$event->event_id,
			$dt->format( 'Y-m' ),
			$event->title,
			$item->location_id
		) . "\n";
	}
	echo sprintf( '<td class="presences-location-sum w3-right-align" data-location="%d"></td>', $item->location_id ) . "\n";
	echo '</tr>' . "\n";
} );
$panel->add( 'team_id', function( $item ) {
	global $teams;
	$team = $teams[ $item->team_id ];
	echo '<tr>' . "\n";
	echo sprintf( '<td>%s</td>', $team->team_name ) . "\n";
}, function( $item ) {
	echo sprintf( '<td class="presences-team-sum w3-right-align" data-team="%d"></td>', $item->team_id ) . "\n";
	echo '</tr>' . "\n";
} );
$panel->add( 'event_id', function( $item ) {
	global $events;
	$event = $events[ $item->event_id ];
	$dt = new dtime( $event->event_date );
	echo sprintf( '<td class="presences-event presences-month presences-team w3-right-align" data-event="%d", data-month="%s" title="%s" data-location="%d" data-team="%d">%d</td>',
		$event->event_id,
		$dt->format( 'Y-m' ),
		$event->title,
		$item->location_id,
		$item->team_id,
		$item->presences
	) . "\n";
} );
page_body_add( [ $panel, 'html' ], $presences );

page_body_add( function() {
	global $events;
?>
<section class="action">
	<button class="w3-button w3-circle w3-theme modal-show" data-modal="#modal-view" title="προβολή">
		<span class="fa fa-eye"></span>
	</button>
	<a href="<?= season_href( 'view-download.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="μεταφόρτωση">
		<span class="fa fa-download"></span>
	</a>
</section>
<section class="w3-modal modal" id="modal-view">
	<div class="w3-modal-content w3-card-4">
		<div class="flex w3-theme">
			<div style="font-size: large;">στήλες</div>
			<span class="w3-button w3-theme w3-hover-red" title="κλείσιμο" style="flex-shrink: 0;">
				<span class="fa fa-times"></span>
			</span>
		</div>
		<div class="w3-padding">
<?php
	$panel = new panel();
	$panel->add( function( event $event ) {
		$dt = new dtime( $event->event_date );
		return $dt->format( 'Y-m' );
	}, function ( event $event ) {
		$dt = new dtime( $event->event_date );
		echo "\t\t\t" . sprintf( '<button class="w3-button month-toggle" data-month="%s">%s</button>', $dt->format( 'Y-m' ), $dt->month_name() ) . "\n";
	} );
	$panel->html( $events );
?>
		</div>
	</div>
</section>
<?php
} );

page_html();