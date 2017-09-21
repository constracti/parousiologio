<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$mode = request_var( 'mode' );
if ( !in_array( $mode, [ 'desktop', 'mobile' ] ) )
	failure( 'argument_not_valid', 'mode' );

$team = team::request( 'team_id' );
if ( !$cuser->has_team( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$child = child::request( 'child_id' );
	if ( !$team->has_child( $child->child_id ) )
		failure( 'argument_not_valid', 'child_id' );
	$event = event::request( 'event_id' );
	if ( !$team->has_event( $event->event_id ) )
		failure( 'argument_not_valid', 'event_id' );
	$check = request_var( 'check' ) === 'on';
	if ( $check )
		$child->insert_event( $event->event_id );
	else
		$child->delete_event( $event->event_id );
	success();
}

$location = location::select_by( 'location_id', $team->location_id );

page_title_set( sprintf( '%s (%s %d)', $team->team_name, $location->location_name, $cyear ) );

page_script_add( SITE_URL . 'js/properties.js' );
page_script_add( SITE_URL . 'js/months.js' );
page_script_add( SITE_URL . 'js/presences.js' );

page_nav_add( 'season_dropdown' );
page_nav_add( function() {
	global $mode;
	global $team;
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>presences.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" title="παρουσίες">
	<span class="fa fa-check-square"></span>
	<span class="w3-hide-small w3-hide-medium">παρουσίες</span>
</a>
<?php
} );

$children = $team->get_children();
$events = $team->get_events();
$presences = $team->get_presences( $mode );
$properties = [
	'home_phone'    => 'σταθερό τηλέφωνο',
	'mobile_phone'  => 'κινητό τηλέφωνο',
	'email_address' => 'διεύθυνση email',
	'school'        => 'σχολείο',
	'grade_name'    => 'τάξη',
	'birth_year'    => 'έτος γέννησης',
	'fath_name'     => 'όνομα πατρός',
	'fath_mobile'   => 'κινητό πατρός',
	'fath_occup'    => 'επάγγελμα πατρός',
	'fath_email'    => 'email πατρός',
	'moth_name'     => 'όνομα μητρός',
	'moth_mobile'   => 'κινητό μητρός',
	'moth_occup'    => 'επάγγελμα μητρός',
	'moth_email'    => 'email μητρός',
	'address'      => 'διεύθυνση',
	'city'          => 'πόλη',
	'postal_code'   => 'τ.κ.',
];

function presences_desktop_body() {
	global $mode;
	global $team;
	global $children;
	global $events;
	global $presences;
	global $properties;
?>
<section class="w3-panel">
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
	</style>
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
					<a href="<?= SITE_URL ?>update.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>&child_id=<?= $child->child_id ?>" title="επεξεργασία">
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
				<td class="xa-month" data-month="<?= $dt->format( 'Y-m' ) ?>">
					<input class="xa-presence-item" data-child="<?= $item->child_id ?>" data-event="<?= $item->event_id ?>" type="checkbox"<?= $item->check ? ' checked="checked"' : '' ?> />
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
</section>
<?php
}

function presences_mobile_body() {
	global $presences;
	$panel = new panel();
	$panel->add( function( $item ) {
		global $events;
		$event = $events[ $item->event_id ];
		$dt = dtime::from_sql( $event->date, dtime::DATE );
		return $dt->format( 'Y-m' );
	}, function( $item ) {
		global $events;
		$event = $events[ $item->event_id ];
		$dt = dtime::from_sql( $event->date, dtime::DATE );
		echo sprintf ( '<section class="w3-panel w3-content xa-month" data-month="%s">', $dt->format( 'Y-m' ) ) . "\n";
		echo '<ul class="w3-ul w3-card-4 w3-theme-l4">' . "\n";
		echo '<li class="w3-container w3-theme">' . "\n";
		echo sprintf( '<h3 style="margin: 0px;">%s %s</h3>', $dt->month_name(), $dt->format( 'Y' ) ) . "\n";
		echo '</li>' . "\n";
	}, function( $item ) {
		echo '</ul>' . "\n";
		echo '</section>' . "\n";
	} );
	$panel->add( 'event_id', function( $item ) {
		global $events;
		$event = $events[ $item->event_id ];
		$dt = dtime::from_sql( $event->date, dtime::DATE );
		echo sprintf( '<li class="xa-event">', $dt->format( 'Y-m' ) ) . "\n";
		echo '<div style="display: flex; justify-content: space-between; align-items: center;">' . "\n";
		echo sprintf( '<h5 style="margin: 0px;">%s. %s</h5>', $dt->format( 'j' ), $event->name ) . "\n";
		echo '<div style="flex-shrink: 0;">' . "\n";
		echo sprintf( '<span class="w3-badge w3-theme xa-presence-event" data-event="%d"></span>', $event->event_id ) . "\n";
		echo '<a class="xa-event-toggle" style="cursor: pointer;"><span class="fa"></span></a>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
		echo '<div class="xa-event-content" style="display: none;">' . "\n";
	}, function( $item ) {
		echo '</div>' . "\n";
		echo '</li>' . "\n";
	} );
	$panel->add( 'child_id', function( $item ) {
		global $mode;
		global $team;
		global $children;
		$child = $children[ $item->child_id ];
		echo '<div style="margin: 8px 0px;">' . "\n";
		echo '<div style="display: flex; justify-content: space-between; align-items: center;">' . "\n";
		echo '<div style="display: flex; justify-content: flex-start; align-items: center;">' . "\n";
		echo sprintf( '<input class="xa-presence-item" data-child="%d" data-event="%d" style="flex-shrink: 0;" type="checkbox"%s />', $item->child_id, $item->event_id, $item->check ? ' checked="checked"' : '' ) . "\n";
		echo sprintf( '<a style="padding-left: 4px;" href="%supdate.php?mode=%s&team_id=%d&child_id=%d" title="επεξεργασία">', SITE_URL, $mode, $team->team_id, $child->child_id ) . "\n";
		echo sprintf( '<span>%s</span>', $child->last_name ) . "\n";
		echo sprintf( '<span>%s</span>', $child->first_name ) . "\n";
		echo '</a>' . "\n";
		echo '</div>' . "\n";
		global $properties;
		echo '<div style="flex-shrink: 0;">' . "\n";
		foreach ( $properties as $property => $property_label ) {
			if ( !in_array( $property, [ 'grade_name', 'home_phone', 'mobile_phone' ] ) )
				continue;
			echo sprintf( '<span class="xa-property w3-tag w3-theme w3-round w3-small" data-property="%s" title="%s" style="white-space: nowrap;">%s</span>', $property, $property_label, $child->$property ) . "\n";
		}
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	} );
	$panel->html( $presences );
?>
<script>
$( function() {

$( '.xa-event-toggle' ).find( '.fa' ).addClass( 'fa-plus-square-o' ).end().click( function() {
	$( this ).
	find( '.fa' ).toggleClass( 'fa-plus-square-o' ).toggleClass( 'fa-minus-square-o' ).end().
	parents( '.xa-event' ).find( '.xa-event-content' ).toggle();
} );

} );
</script>
<?php
}

page_script_add( SITE_URL . 'js/modal.js' );

page_body_add( sprintf( 'presences_%s_body', $mode ) );

page_body_add( function() {
	global $mode;
	global $team;
	global $events;
	global $properties;
?>
<section class="action">
	<button class="w3-button w3-circle w3-theme xa-modal-show" data-modal="#xa-modal-view" title="προβολή">
		<span class="fa fa-eye"></span>
	</button>
	<a href="<?= SITE_URL ?>download.php?team_id=<?= $team->team_id ?>" class="w3-button w3-circle w3-theme" title="μεταφόρτωση">
		<span class="fa fa-download"></span>
	</a>
	<a href="<?= SITE_URL ?>insert.php?mode=<?= $mode ?>&team_id=<?= $team->team_id ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<section class="w3-modal modal-columns xa-modal" id="xa-modal-view">
	<div class="w3-modal-content w3-card-4">
		<div class="w3-container w3-theme">
			<div style="display: flex; justify-content: space-between; align-items: center;">
				<h3>στήλες</h3>
				<span class="w3-button w3-theme w3-hover-red" title="κλείσιμο" style="flex-shrink: 0;">
					<span class="fa fa-times"></span>
				</span>
			</div>
		</div>
		<div class="w3-padding">
<?php
	foreach ( $properties as $property => $property_label )
		echo "\t\t\t" . sprintf( '<button class="w3-button xa-property-toggle" data-property="%s">%s</button>', $property, $property_label ) . "\n";
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
		echo "\t\t\t" . sprintf( '<button class="w3-button xa-month-toggle" data-month="%s">%s</button>', $dt->format( 'Y-m' ), $dt->month_name() ) . "\n";
	} );
	$panel->html( $events );
?>
		</div>
	</div>
</section>
<script>
$( function() {

$( '.xa-modal-show' ).click( function() {
	$( $( this ).data( 'modal' ) ).show();
} );

$( '.xa-modal' ).click( function() {
	$( this ).hide();
} ).find( '.w3-modal-content' ).click( function( event ) {
	event.stopPropagation();
} ).end().
find( '.w3-hover-red' ).click( function() {
	$( this ).parents( '.xa-modal' ).hide();
} );

} );
</script>
<?php
} );

page_html();