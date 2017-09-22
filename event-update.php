<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$event = event::request();

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
	switch ( request_var( 'relation', TRUE ) ) {
		case 'insert_grade':
			$grade = grade::request();
			$event->insert_grade( $grade->grade_id );
			success();
		case 'delete_grade':
			$grade = grade::request();
			$event->delete_grade( $grade->grade_id );
			success();
		case 'insert_grades':
			$category = category::request();
			$event->insert_grades( $category->category_id );
			success();
		case 'delete_grades':
			$event->delete_grades();
			success();
		case 'insert_location':
			$location = location::request();
			$event->insert_location( $location->location_id );
			success();
		case 'delete_location':
			$location = location::request();
			$event->delete_location( $location->location_id );
			success();
		case 'insert_locations':
			$is_swarm = request_int( 'is_swarm' );
			$event->insert_locations( $is_swarm );
			success();
		case 'delete_locations':
			$event->delete_locations();
			success();
		default:
			$event->name = $fields['name']->post();
			$event->date = $fields['date']->post();
			$event->season_id = $fields['season_id']->post();
			$event->update();
			success( [
				'alert' => 'Το συμβάν ενημερώθηκε.',
			] );
	}
}


/********
 * main *
 ********/

page_title_set( 'Επεξεργασία συμβάντος' );

page_nav_add( 'season_dropdown', [
	'href' => 'events.php',
	'text' => 'συμβάντα',
	'icon' => 'fa-calendar-check-o',
] );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . sprintf( 'event-update.php?event_id=%d', $event->event_id ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_body_add( 'form_section', $fields, [
	'delete' => SITE_URL . sprintf( 'event-delete.php?event_id=%d', $event->event_id ),
] );


/**********
 * grades *
 **********/

$panel = new panel();
$panel->add( function( grade $grade ) {
	return NULL;
}, function( grade $grade ) {
	global $event;
	$href = SITE_URL . sprintf( 'event-update.php?relation=delete_grades&event_id=%d', $event->event_id );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-card-4 w3-round w3-theme-l4 relation" data-relation="grade">' . "\n";
	echo '<li>' . "\n";
	echo '<h3>τάξεις</h3>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-orange" href="%s">καθαρισμός</a>', $href ) . "\n";
	echo '</li>' . "\n";
}, function( grade $grade ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'category_id', function( grade $grade ) {
	global $event;
	$href = SITE_URL . sprintf( 'event-update.php?relation=insert_grades&event_id=%d&category_id=%d', $event->event_id, $grade->category_id );
	echo '<li>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-theme-action" href="%s">%s</a>', $href, $grade->category_name ) . "\n";
}, function( grade $grade ) {
	echo '</li>' . "\n";
} );
$panel->add( 'grade_id', function( grade $grade ) {
	global $event;
	$href_on = SITE_URL . sprintf( 'event-update.php?relation=insert_grade&event_id=%d&grade_id=%d', $event->event_id, $grade->grade_id );
	$href_off = SITE_URL . sprintf( 'event-update.php?relation=delete_grade&event_id=%d&grade_id=%d', $event->event_id, $grade->grade_id );
	echo '<label class="w3-button w3-round w3-theme">' . "\n";
	echo sprintf( '<input type="checkbox" data-href-on="%s" data-href-off="%s"%s />', $href_on, $href_off, $grade->check ? ' checked="checked"' : '' ) . "\n";
	echo sprintf( '<span>%s</span>', $grade->grade_name ) . "\n";
	echo '</label>' . "\n";
} );
page_body_add( [ $panel, 'html' ], $event->select_grades() );


/*************
 * locations *
 *************/

$panel = new panel();
$panel->add( NULL, function( location $location ) {
	global $event;
	$href = SITE_URL . sprintf( 'event-update.php?relation=delete_locations&event_id=%d', $event->event_id );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-card-4 w3-round w3-theme-l4 relation">' . "\n";
	echo '<li>' . "\n";
	echo '<h3>περιοχές</h3>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-orange" href="%s">καθαρισμός</a>', $href ) . "\n";
	echo '</li>' . "\n";
}, function( location $location ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'is_swarm', function( location $location ) {
	global $event;
	$href = SITE_URL . sprintf( 'event-update.php?relation=insert_locations&event_id=%d&is_swarm=%d', $event->event_id, $location->is_swarm );
	echo '<li>' . "\n";
	echo sprintf( '<a class="w3-button w3-round w3-theme-action" href="%s">%s</a>', $href, $location->is_swarm ? 'ομάδες' : 'κατηχητικά' ) . "\n";
}, function( location $location ) {
	echo '</li>' . "\n";
} );
$panel->add( 'location_id', function( location $location ) {
	global $event;
	$href_on = SITE_URL . sprintf( 'event-update.php?relation=insert_location&event_id=%d&location_id=%d', $event->event_id, $location->location_id );
	$href_off = SITE_URL . sprintf( 'event-update.php?relation=delete_location&event_id=%d&location_id=%d', $event->event_id, $location->location_id );
	echo '<label class="w3-button w3-round w3-theme">' . "\n";
	echo sprintf( '<input type="checkbox" data-href-on="%s" data-href-off="%s"%s />', $href_on, $href_off, $location->check ? ' checked="checked"' : '' ) . "\n";
	echo sprintf( '<span>%s</span>', $location->location_name ) . "\n";
	echo '</label>' . "\n";
} );
page_body_add( [ $panel, 'html' ], $event->select_locations() );


/********
 * exit *
 ********/

page_html();