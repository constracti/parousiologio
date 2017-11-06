<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$child = child::request();


/********
 * main *
 ********/

page_title_set( 'Έτη παιδιού' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'children.php' ),
	'text' => 'παιδιά',
	'icon' => 'fa-child',
	'hide_medium' => FALSE,
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'child-update.php', [ 'child_id' => $child->child_id ] ),
	'text' => 'επεξεργασία',
	'icon' => 'fa-pencil',
] );

page_nav_add( 'bar_link', [
	'href' => site_href( 'follows.php', [ 'child_id' => $child->child_id ] ),
	'text' => 'έτη',
	'icon' => 'fa-calendar',
] );

page_body_add( function() {
	global $child;
	echo sprintf( '<h3 class="w3-panel w3-content w3-text-theme w3-center">%s %s</h3>', $child->last_name, $child->first_name ) . "\n";
} );

page_body_add( function() {
	global $child;
?>
<section class="action">
	<a href="<?= site_href( 'follow-insert.php', [ 'child_id' => $child->child_id ] ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

$seasons = season::select();
$grades = grade::select();
$locations = location::select();

$panel = new panel();
$panel->add( NULL, function( follow $follow ) {
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
}, function( follow $follow ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'follow_id', function( follow $follow ) {
	global $seasons;
	$season = $seasons[ $follow->season_id ];
	global $grades;
	$grade = $grades[ $follow->grade_id ];
	global $locations;
	$location = !is_null( $follow->location_id ) ? $locations[ $follow->location_id ] : NULL;
	echo '<li class="flex">' . "\n";
	echo '<div>' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<time datetime="%d">%d</time>', $season->year, $season->year ) . "\n";
	echo '</div>' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<span class="w3-tag w3-round w3-small w3-theme">%s</span>', $grade->grade_name ) . "\n";
	if ( !is_null( $location ) )
		echo sprintf( '<span class="w3-tag w3-round w3-small w3-theme">%s</span>', $location->location_name ) . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '<div style="flex-shrink: 0;">' . "\n";
	$href = site_href( 'follow-update.php', [ 'follow_id' => $follow->follow_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
	$href = site_href( 'follow-delete.php', [ 'follow_id' => $follow->follow_id ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
page_body_add( [ $panel, 'html' ], $child->select_follows() );


/********
 * exit *
 ********/

page_html();