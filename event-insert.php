<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$fields = [
	'event_name' => new field( 'event_name', [
		'placeholder' => 'περιγραφή',
	] ),
	'event_date' => new field( 'event_date', [
		'type' => 'date',
		'placeholder' => 'ημερομηνία',
		'required' => TRUE,
		'value' => ( new dtime() )->format( 'Y-m-d' ),
	] ),
	'season_id' => new field_select( 'season_id', season::select_options(), [
		'placeholder' => 'έτος',
		'required' => TRUE,
		'value' => $cseason->season_id,
	] ),
];

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$event = new event();
	$event->event_name = $fields['event_name']->post();
	$event->event_date = $fields['event_date']->post();
	$event->season_id = $fields['season_id']->post();
	$event->insert();
	success( [
		'alert' => 'Το συμβάν προστέθηκε.',
		'location' => site_href( 'event-update.php', [ 'event_id' => $event->event_id ] ),
	] );
}

page_title_set( 'Προσθήκη συμβάντος' );

page_nav_add( 'season_dropdown', [
	'href' => 'events.php',
	'text' => 'συμβάντα',
	'icon' => 'fa-calendar-check-o',
] );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= season_href( 'event-insert.php' ) ?>" title="προσθήκη">
	<span class="fa fa-plus"></span>
	<span class="w3-hide-small w3-hide-medium">προσθήκη</span>
</a>
<?php
} );

page_body_add( 'form_section', $fields );

page_html();
