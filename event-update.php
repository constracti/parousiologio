<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$event = event::request( 'event_id' );

$fields = [
	'name' => new field( 'name', [
		'placeholder' => 'περιγραφή',
		'value' => $event->name,
	] ),
	'date' => new field( 'date', [
		'type' => 'date',
		'placeholder' => 'ημερομηνία',
		'required' => TRUE,
		'value' => $event->date,
	] ),
	'season_id' => new field_select( 'season_id', season::select_options(), [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $event->season_id,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$event->name = $fields['name']->post();
	$event->date = $fields['date']->post();
	$event->season_id = $fields['season_id']->post();
	$event->update();
	success( [
		'alert' => 'Το συμβάν ενημερώθηκε.',
	] );
}

page_title_set( 'Επεξεργασία συμβάντος' );

page_nav_add( 'season_dropdown', [
	'href' => 'events.php',
	'text' => 'συμβάντα',
	'icon' => 'fa-calendar-check-o',
] );

# TODO season_href with parameters
page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= season_href( 'event-update.php' ) ?>" title="επεξεργασία">
	<span class="fa fa-pencil"></span>
	<span class="w3-hide-small w3-hide-medium">επεξεργασία</span>
</a>
<?php
} );

page_body_add( 'form_section', $fields, [
	'delete' => sprintf( '%sevent-delete.php?event_id=%d', SITE_URL, $event->event_id ),
] );

# TODO event grades and locations

page_html();