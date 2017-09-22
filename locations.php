<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Περιοχές' );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . 'locations.php',
	'text' => 'περιοχές',
	'icon' => 'fa-globe',
	'hide_medium' => FALSE,
] );

$panel = new panel();
$panel->add( 'is_swarm', function( location $location ) {
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4 list">' . "\n";
	echo '<li class="w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $location->is_swarm ? 'ομάδες' : 'κατηχητικά' ) . "\n";
	echo '</li>' . "\n";
}, function( location $location ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'location_id', function( location $location ) {
	echo '<li>' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $location->location_name ) . "\n";
	echo '<div>' . "\n";
	foreach ( $location->select_seasons() as $season )
		if ( $season->location_teams !== 0 )
			echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%d: %d</span>', $season->year, $season->location_teams ) . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '<div>' . "\n";
	$href = SITE_URL . sprintf( 'location-update.php?location_id=%d', $location->location_id );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-green" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
	$href = SITE_URL . sprintf( 'location-delete.php?location_id=%d', $location->location_id );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
page_body_add( [ $panel, 'html' ], location::select( [], [
	'is_swarm' => 'DESC',
	'location_name' => 'ASC',
	'location_id' => 'ASC',
] ) );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= SITE_URL . 'location-insert.php' ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

page_html();