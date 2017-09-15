<?php

if ( !empty( $_POST ) )
	require_once 'php/ajax.php';
else
	require_once 'php/page.php';

if ( is_null( $cuser ) )
	failure();

$mode = request_string( 'mode' );
if ( !in_array( $mode, [ 'desktop', 'laptop' ] ) )
	failure();

$team = team::request();

if ( !empty( $_POST ) ) {
	$child = child::request();
	if ( !$team->has_child( $child->child_id ) )
		failure();
	$event = event::request();
	if ( !$team->has_event( $event->event_id ) )
		failure();
	$check = request_string( 'check' ) === 'on';
	if ( $check )
		$child->insert_event( $event->event_id );
	else
		$child->delete_event( $event->event_id );
	success();
}

if ( !$cuser->has_team( $team->team_id ) )
	failure();

$location = location::select_by( 'location_id', $team->location_id );

page_title_set( sprintf( '%s (%s %d)', $team->team_name, $location->location_name, $cyear ) );

page_script_add( HOME_URL . 'js/properties.js' );
page_script_add( HOME_URL . 'js/months.js' );

page_nav_add( 'season_dropdown' );
page_nav_add( function() {
	global $mode;
	global $team;
?>
			<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>presences.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" title="παρουσίες">
				<span class="fa fa-check-square"></span>
				<span class="w3-hide-small w3-hide-medium">παρουσίες</span>
			</a>
<?php
} );

$children = $team->select_children();
$events = $team->select_events();
$presences = $team->select_presences();
$properties = [
	'grade_name' => 'τάξη',
	'school'     => 'σχολείο',
	'city'       => 'πόλη',
];

page_body_add( function() {
	global $mode;
	global $team;
	global $children;
	global $events;
	global $presences;
	global $properties;
?>
<div class="w3-panel">
	<style>
table.xa-presences {
	width: initial;
	margin: auto;
}
table.xa-presences th, table.xa-presences td {
	padding: 4px !important;
	border: thin solid #ddd;
}
.xa-presence-day, .xa-presence-item, .xa-presence-event {
	text-align: center !important;
}
.xa-presence-child, .xa-presence-total {
	text-align: right !important;
}
	</style><!-- TODO move in CSS -->
	<table class="xa-presences w3-table w3-striped w3-card-4">
		<thead class="w3-theme">
			<tr>
				<th rowspan="2">ονοματεπώνυμο</th>
<?php
	foreach ( $properties as $property => $property_label ) {
?>
				<th class="xa-property" data-property="<?= $property ?>" rowspan="2"><?= $property_label ?></th>
<?php
	}
	$panel = new panel();
	$panel->add( function( event $event ) {
		$dt = dtime::from_sql( $event->date, dtime::DATE );
		return $dt->format( 'Y-m' );
	}, function ( event $event ) {
		global $month_counter;
		$month_counter = 0;
	}, function ( event $event ) {
		global $month_counter;
		$dt = dtime::from_sql( $event->date, dtime::DATE );
?>
				<th class="xa-month" data-month="<?= $dt->format( 'Y-m' ) ?>" colspan="<?= $month_counter ?>"><?= $dt->month_name() ?></th>
<?php
	} );
	$panel->add( 'event_id', function( event $event ) {
		global $month_counter;
		$month_counter++;
	} );
	$panel->html( $events );
?>
				<th rowspan="2">παρουσίες</th>
			</tr>
			<tr>
<?php
	$panel = new panel();
	$panel->add( 'event_id', function( event $event ) {
		$dt = dtime::from_sql( $event->date, dtime::DATE );
?>
				<th class="xa-month xa-presence-day" data-month="<?= $dt->format( 'Y-m' ) ?>"><?= $dt->format( 'j' ) ?></th>
<?php
	} );
	$panel->html( $events );
?>
			</tr>
		</thead>
		<tbody>
<?php
	$panel = new panel();
	$panel->add( 'child_id', function( $item ) {
		global $mode;
		global $team;
		global $children;
		global $properties;
		$child = $children[ $item->child_id ];
?>
			<tr>
				<td>
					<a href="<?= HOME_URL ?>update.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>&child_id=<?= $child->child_id ?>" title="επεξεργασία">
						<span><?= $child->last_name ?></span>
						<span><?= $child->first_name ?></span>
					</a>
				</td>
<?php
		foreach ( $properties as $property => $property_label ) {
?>
				<td class="xa-property" data-property="<?= $property ?>"><?= $child->$property ?? '' ?></td>
<?php
		}
	}, function( $item ) {
?>
				<td class="xa-presence-child" data-child="<?= $item->child_id ?>"></td>
			</tr>
<?php
	} );
	$panel->add( 'event_id', function( $item ) {
		global $events;
		$event = $events[ $item->event_id ];
		$dt = dtime::from_sql( $event->date, dtime::DATE );
?>
				<td class="xa-month xa-presence-item" data-month="<?= $dt->format( 'Y-m' ) ?>" data-child="<?= $item->child_id ?>" data-event="<?= $item->event_id ?>">
					<input type="checkbox"<?= checked( $item->check ) ?> />
				</td>
<?php
	} );
	$panel->html( $presences );
?>
		</tbody>
		<tfoot class="w3-theme-l2">
			<tr>
				<td></td>
<?php
	foreach ( $properties as $property => $property_label ) {
?>
				<td class="xa-property" data-property="<?= $property ?>"></td>
<?php
	}
	$panel = new panel();
	$panel->add( 'event_id', function( event $event ) {
		$dt = dtime::from_sql( $event->date, dtime::DATE );
?>
				<td class="xa-month xa-presence-event" data-month="<?= $dt->format( 'Y-m' ) ?>" data-event="<?= $event->event_id ?>"></td>
<?php
	} );
	$panel->html( $events );
?>
				<td class="xa-presence-total"></td>
			</tr>
		</tfoot>
	</table>
</div>
<script>
function calculate_child( id ) {
	var sum = $( '.xa-presence-item[data-child="' + id + '"]>input[type="checkbox"]:checked' ).length;
	$( '.xa-presence-child[data-child="' + id + '"]' ).html( sum );
}
function calculate_event( id ) {
	var sum = $( '.xa-presence-item[data-event="' + id + '"]>input[type="checkbox"]:checked' ).length;
	$( '.xa-presence-event[data-event="' + id + '"]' ).html( sum );
}
function calculate_total() {
	var sum = $( '.xa-presence-item>input[type="checkbox"]:checked' ).length;
	$( '.xa-presence-total' ).html( sum );
}
$( '.xa-presence-child' ).each( function() {
	calculate_child( $( this ).data( 'child' ) );
} );
$( '.xa-presence-event' ).each( function() {
	calculate_event( $( this ).data( 'event' ) );
} );
calculate_total();
$( '.xa-presence-item>input[type="checkbox"]' ).change( function() {
	var td = $( this ).parent();
	calculate_child( td.data( 'child' ) );
	calculate_event( td.data( 'event' ) );
	calculate_total();
	$.post( '', {
		child_id: td.data( 'child' ),
		event_id: td.data( 'event' ),
		check : $( this ).prop( 'checked' ) ? 'on' : 'off',
	} );
} );
</script>
<div style="position: fixed; right: 50px; bottom: 50px;">
	<button class="w3-button w3-circle w3-theme modal-columns-show" title="προβολή">
		<span class="fa fa-eye"></span>
	</button>
	<a href="<?= HOME_URL ?>download.php?team_id=<?= $team->team_id ?>" class="w3-button w3-circle w3-theme" title="μεταφόρτωση">
		<span class="fa fa-download"></span>
	</a>
	<a href="<?= HOME_URL ?>insert.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</div>
<div class="w3-modal modal-columns modal-columns-hide">
	<div class="w3-modal-content w3-card-4">
		<div class="w3-container w3-theme">
			<div style="display: flex; justify-content: space-between; align-items: center;">
				<h3>στήλες</h3>
				<span class="w3-button modal-columns-hide w3-theme w3-hover-red" title="κλείσιμο" style="flex-shrink: 0;">
					<span class="fa fa-times"></span>
				</span>
			</div>
		</div>
		<div class="w3-padding">
<?php
	foreach ( $properties as $property => $property_label ) {
?>
			<button class="w3-button xa-property-toggle" data-property="<?= $property ?>"><?= $property_label ?></button>
<?php
	}
?>
		</div>
		<hr style="margin: 0px;" />
		<div class="w3-padding">
<?php
	$panel = new panel();
	$panel->add( function( event $event ) {
		$dt = dtime::from_sql( $event->date, dtime::DATE );
		return $dt->format( 'Y-m' );
	}, function ( event $event ) {
		$dt = dtime::from_sql( $event->date, dtime::DATE );
?>
			<button class="w3-button xa-month-toggle" data-month="<?= $dt->format( 'Y-m' ) ?>"><?= $dt->month_name() ?></button>
<?php
	} );
	$panel->html( $events );
?>
		</div>
	</div>
</div>
<script>
$( '.modal-columns-show' ).click( function() {
	$( '.modal-columns' ).show();
} );
$( '.modal-columns-hide' ).click( function() {
	$( '.modal-columns' ).hide();
} );
$( '.modal-columns>.w3-modal-content' ).click( function( event ) {
	event.stopPropagation();
} );
</script>
<?php
} );

page_html();