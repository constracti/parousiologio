<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$team = team::request();
if ( !$cuser->accesses( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$child = child::request();
	if ( !$team->has_child( $child->child_id ) )
		failure( 'argument_not_valid', 'child_id' );
	$event = event::request();
	if ( !$team->has_event( $event->event_id ) )
		failure( 'argument_not_valid', 'event_id' );
	$check = request_var( 'check' ) === 'on';
	if ( $check )
		$child->insert_event( $event->event_id );
	else
		$child->delete_event( $event->event_id );
	success();
}


/********
 * main *
 ********/

$location = location::select_by( 'location_id', $team->location_id );

page_title_set( sprintf( '%s (%s %d)', $team->team_name, $location->location_name, $cseason->year ) );

page_nav_add( 'season_dropdown' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'presences.php', [ 'team_id', $team->team_id ] ),
	'text' => 'παρουσίες',
	'icon' => 'fa-check-square',
] );

$children = $team->select_children();
$events = $team->select_events();
$presences = $team->check_presences();

page_body_add( function() {
	global $team;
	global $events;
	global $children;
	global $presences;
?>
<style>
#presences-sidebar {
	display: none;
	flex-shrink: 10;
	min-width: 120px;
}
#presences-sidebar>.presences-event.flex {
	padding: 2px;
}
#presences-sidebar>.presences-event.flex>* {
	margin: 2px;
}
#presences-sidebar>.presences-event>:not(:last-child) {
	overflow: hidden;
	text-overflow: ellipsis;
}
#presences-sidebar>.presences-event>:last-child {
	flex-shrink: 0;
}
#presences-container.presences-container-expanded>#presences-sidebar>.presences-event>:last-child {
	display: none;
}
#presences-table .presences-property {
	display: none;
}
#presences-table>*>tr>* {
	padding: 4px;
	text-align: left;
}

@media (min-width:993px) {
	#presences-table .presences-month.presences-month-hide {
		display: none;
	}
	#presences-table .presences-property.presences-property-show {
		display: table-cell;
	}
}

@media (max-width:992px) {
	#presences-container {
		padding: 0px;
	}
	#presences-container>* {
		display: block;
		height: 100%;
		margin: 0px;
		flex-grow: 1;
		overflow-y: auto;
	}
	#presences-container:not(.presences-container-expanded)>#presences-main {
		display: none;
	}
	#presences-table {
		width: 100%;
		border-left: none !important;
		border-right: none !important;
	}
	#presences-table>thead, #presences-table>tfoot,
	#presences-table .presences-property,
	#presences-table .presences-event:not(.presences-event-visible),
	.action>.modal-show {
		display: none;
	}
}
</style>
<?php
	echo '<div id="presences-container" class="flex" style="justify-content: center; align-items: flex-start;">' . "\n";
	echo '<div id="presences-sidebar" class="w3-border-top">' . "\n";
	foreach ( array_reverse( $events, TRUE ) as $event ) {
		$dt = new dtime( $event->event_date_fixed );
		echo sprintf( '<a class="flex presences-event w3-button w3-border-bottom" data-event="%d">', $event->event_id ) . "\n";
		echo '<div>' . "\n";
		echo sprintf( '<time datetime="%s">%s, %s</time>', $dt->format( dtime::DATE ), $dt->weekday_short_name(), $dt->format( 'j/n' ) );
		$event->title = sprintf( '%s, %s', $dt->weekday_short_name(), $dt->format( 'j/n' ) );
		if ( !is_null( $event->event_name ) ) {
			echo sprintf( ': <span>%s</span>', $event->event_name ) . "\n";
			$event->title .= sprintf( ': %s', $event->event_name );
		}
		echo '</div>' . "\n";
		echo sprintf( '<span class="presences-event-sum" data-event="%d"></span>', $event->event_id ) . "\n";
		echo '</a>' . "\n";
	}
	echo '</div>' . "\n";
	echo '<div id="presences-main">' . "\n";
	echo '<table id="presences-table" class="w3-border w3-striped w3-hoverable" style="border-collapse: collapse;">' . "\n";
	echo '<thead class="w3-theme">' . "\n";
	echo '<tr>' . "\n";
	echo '<th rowspan="2">ονοματεπώνυμο</th>' . "\n";
	foreach ( child::COLS as $col => $colname )
		echo sprintf( '<th rowspan="2" class="presences-property" data-property="%s">%s</th>', $col, $colname ) . "\n";
	$panel = new panel();
	$panel->add( function( event $event ) {
		$dt = new dtime( $event->event_date_fixed );
		return $dt->format( 'Y-m' );
	}, function( event $event ) {
		global $month_counter;
		$month_counter = 0;
	}, function( event $event ) {
		global $month_counter;
		$dt = new dtime( $event->event_date_fixed );
		echo sprintf( '<th colspan="%d" class="presences-month" data-month="%s">%s</th>', $month_counter, $dt->format( 'Y-m' ), $dt->month_name() ) . "\n";
	} );
	$panel->add( 'event_id', function( event $event ) {
		global $month_counter;
		$month_counter++;
	} );
	$panel->html( $events );
	echo '<th rowspan="2" class="w3-right-align">παρουσίες</th>' . "\n";
	echo '</tr>' . "\n";
	echo '<tr>' . "\n";
	foreach ( $events as $event ) {
		$dt = new dtime( $event->event_date_fixed );
		echo sprintf( '<th class="presences-event presences-month w3-center" data-event="%d" data-month="%s" title="%s">%s</th>', $event->event_id, $dt->format( 'Y-m' ), $event->title, $dt->format( 'j' ) ) . "\n";
	}
	echo '</tr>' . "\n";
	echo '</thead>' . "\n";
	echo '<tbody>' . "\n";
	$panel = new panel();
	$panel->add( 'child_id', function( $item ) {
		global $team;
		global $children;
		$child = $children[ $item->child_id ];
		echo '<tr class="w3-border-top w3-border-bottom">' . "\n";
		$href = site_href( 'update.php', [ 'team_id' => $team->team_id, 'child_id' => $child->child_id ] );
		echo sprintf( '<td><a href="%s">%s %s</a></td>', $href, $child->last_name, $child->first_name ) . "\n";
		foreach ( child::COLS as $col => $colname )
			echo sprintf( '<td class="presences-property" data-property="%s">%s</td>', $col, $child->$col ) . "\n";
	}, function( $item ) {
		echo sprintf( '<td class="presences-child-sum w3-right-align" data-child="%d"></td>', $item->child_id );
		echo '</tr>' . "\n";
	} );
	$panel->add( 'event_id', function( $item ) {
		global $events;
		$event = $events[ $item->event_id ];
		$dt = new dtime( $event->event_date_fixed );
		echo sprintf( '<td class="presences-event presences-month w3-center" data-event="%d" data-month="%s" title="%s">', $item->event_id, $dt->format( 'Y-m' ), $event->title ) . "\n";
		echo sprintf( '<input class="presences-check w3-check" style="margin-top: -8px;" data-child="%d" data-event="%d" type="checkbox"%s />', $item->child_id, $item->event_id, $item->check ? ' checked="checked"' : '' ) . "\n";
		echo '</td>' . "\n";
	} );
	$panel->html( $presences );
	echo '</tbody>' . "\n";
	echo '<tfoot class="w3-theme">' . "\n";
	echo '<tr>' . "\n";
	echo '<td></td>' . "\n";
	foreach ( child::COLS as $col => $colname )
		echo sprintf( '<td class="presences-property" data-property="%s"></td>', $col ) . "\n";
	foreach ( $events as $event ) {
		$dt = new dtime( $event->event_date_fixed );
		echo sprintf( '<td class="presences-event presences-month presences-event-sum w3-center" data-event="%d" data-month="%s" title="%s"></td>', $event->event_id, $dt->format( 'Y-m' ), $event->title ) . "\n";
	}
	echo '<td class="presences-total-sum w3-right-align"></td>' . "\n";
	echo '</tr>' . "\n";
	echo '</tfoot>' . "\n";
	echo '</table>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
?>
<script>
$( function() {

function presences_child_sum( child ) {
	sum = $( '.presences-check[data-child="' + child + '"]:checked' ).length;
	$( '.presences-child-sum[data-child="' + child + '"]' ).html( sum );
}
function presences_event_sum( event ) {
	sum = $( '.presences-check[data-event="' + event + '"]:checked' ).length;
	$( '.presences-event-sum[data-event="' + event + '"]' ).html( sum );
}
function presences_total_sum() {
	sum = $( '.presences-check:checked' ).length;
	$( '.presences-total-sum' ).html( sum );
}

$( '#presences-table .presences-child-sum' ).each( function() {
	var child = $( this ).data( 'child' );
	presences_child_sum( child );
} );
$( '#presences-table .presences-event-sum' ).each( function() {
	var event = $( this ).data( 'event' );
	presences_event_sum( event );
} );
presences_total_sum();

$( '.presences-check' ).change( function() {
	var child = $( this ).data( 'child' );
	var event = $( this ).data( 'event' );
	presences_child_sum( child );
	presences_event_sum( event );
	presences_total_sum();
	$.post( '', {
		child_id: child,
		event_id: event,
		check : $( this ).prop( 'checked' ) ? 'on' : 'off',
	} );
} );

$( '#presences-sidebar>.presences-event' ).click( function() {
	var event = $( this ).data( 'event' );
	var selected = !$( this ).hasClass( 'w3-theme' );
	$( '#presences-sidebar>.presences-event' ).removeClass( 'w3-theme' );
	$( '#presences-table .presences-event' ).removeClass( 'presences-event-visible' );
	if ( selected ) {
		$( '#presences-container' ).addClass( 'presences-container-expanded' );
		$( this ).addClass( 'w3-theme' );
		$( '#presences-table .presences-event[data-event="' + event + '"]' ).addClass( 'presences-event-visible' );
	} else {
		$( '#presences-container' ).removeClass( 'presences-container-expanded' );
	}
	if ( Storage !== undefined ) {
		if ( selected )
			localStorage.setItem( 'event', event );
		else
			localStorage.removeItem( 'event' );
	}
} );
if ( Storage !== undefined && localStorage.getItem( 'event' ) !== null )
	$( '#presences-sidebar>.presences-event[data-event="' + localStorage.getItem( 'event' ) + '"]' ).click();

$( window ).resize( function() {
	$( '#presences-container' ).height( $( window ).height() - $( '#presences-container' ).position().top );
} ).resize();

var properties = [
	'grade_name',
];
if ( Storage !== undefined && localStorage.getItem( 'properties' ) !== null ) {
	if ( localStorage.getItem( 'properties' ) !== '' )
		properties = localStorage.getItem( 'properties' ).split( ';' );
	else
		properties = [];
}
$( '.property-toggle' ).each( function() {
	var property = $( this ).data( 'property' );
	var index = properties.indexOf( property );
	if ( index !== -1 ) {
		$( this ).addClass( 'w3-theme' );
		$( '.presences-property[data-property="' + property + '"]' ).addClass( 'presences-property-show' );
	}
} ).click( function() {
	var property = $( this ).data( 'property' );
	var index = properties.indexOf( property );
	if ( index !== -1 ) {
		$( this ).removeClass( 'w3-theme' );
		$( '.presences-property[data-property="' + property + '"]' ).removeClass( 'presences-property-show' );
		properties.splice( index, 1 );
	} else {
		$( this ).addClass( 'w3-theme' );
		$( '.presences-property[data-property="' + property + '"]' ).addClass( 'presences-property-show' );
		properties.push( property );
	}
	if ( Storage !== undefined )
		localStorage.setItem( 'properties', properties.join( ';' ) );
} );

var months = [];
if ( Storage !== undefined && localStorage.getItem( 'months' ) !== null && localStorage.getItem( 'months' ) !== '' )
	months = localStorage.getItem( 'months' ).split( ';' );
$( '.month-toggle' ).each( function() {
	var month = $( this ).data( 'month' );
	var index = months.indexOf( month );
	if ( index !== -1 )
		$( '.presences-month[data-month="' + month + '"]' ).addClass( 'presences-month-hide' );
	else
		$( this ).addClass( 'w3-theme' );
} ).click( function() {
	var month = $( this ).data( 'month' );
	var index = months.indexOf( month );
	if ( index !== -1 ) {
		$( this ).addClass( 'w3-theme' );
		$( '.presences-month[data-month="' + month + '"]' ).removeClass( 'presences-month-hide' );
		months.splice( index, 1 );
	} else {
		$( this ).removeClass( 'w3-theme' );
		$( '.presences-month[data-month="' + month + '"]' ).addClass( 'presences-month-hide' );
		months.push( month );
	}
	if ( Storage !== undefined )
		localStorage.setItem( 'months', months.join( ';' ) );
} );

} );
</script>
<?php
} );

page_body_add( function() {
	global $team;
	global $events;
?>
<section class="action">
	<button class="w3-button w3-circle w3-theme modal-show" data-modal="#modal-view" title="προβολή">
		<span class="fa fa-eye"></span>
	</button>
	<a href="<?= site_href( 'download.php', [ 'team_id' => $team->team_id ] ) ?>" class="w3-button w3-circle w3-theme" title="μεταφόρτωση">
		<span class="fa fa-download"></span>
	</a>
	<a href="<?= site_href( 'insert.php', [ 'team_id' => $team->team_id ] ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
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
	foreach ( child::COLS as $col => $colname )
		echo "\t\t\t" . sprintf( '<button class="w3-button property-toggle" data-property="%s">%s</button>', $col, $colname ) . "\n";
?>
		</div>
		<hr style="margin: 0px;" />
		<div class="w3-padding">
<?php
	$panel = new panel();
	$panel->add( function( event $event ) {
		$dt = new dtime( $event->event_date_fixed );
		return $dt->format( 'Y-m' );
	}, function ( event $event ) {
		$dt = new dtime( $event->event_date_fixed );
		echo "\t\t\t" . sprintf( '<button class="w3-button month-toggle" data-month="%s">%s</button>', $dt->format( 'Y-m' ), $dt->month_name() ) . "\n";
	} );
	$panel->html( $events );
?>
		</div>
	</div>
</section>
<script>
$( function() {

$( '.modal-show' ).click( function() {
	$( $( this ).data( 'modal' ) ).show();
} );

$( '.modal' ).click( function() {
	$( this ).hide();
} ).find( '.w3-modal-content' ).click( function( event ) {
	event.stopPropagation();
} ).end().
find( '.w3-hover-red' ).click( function() {
	$( this ).parents( '.modal' ).hide();
} );

} );
</script>
<?php
} );


/********
 * exit *
 ********/

page_html();