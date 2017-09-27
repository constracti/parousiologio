<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Ομάδες' );

page_nav_add( 'season_dropdown', [
	'href' => 'teams.php',
	'text' => 'ομάδες',
	'icon' => 'fa-users',
] );

$panel = new panel();
$panel->add( 'location_id', function( team $team ) {
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	echo '<li class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $team->location_name ) . "\n";
	echo '</li>' . "\n";
}, function( team $team ) {
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );
$panel->add( 'team_id', function( team $team ) {
	if ( is_null( $team->team_id ) )
		return;
	echo '<li class="flex">' . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
	echo '<div>' . "\n";
	foreach ( $team->select_grades() as $grade )
		echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s</span>', $grade->grade_name ) . "\n";
	echo '</div>' . "\n";
	echo '<div>' . "\n";
	foreach ( $team->select_users() as $user )
		echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s %s</span>', $user->last_name, $user->first_name ) . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '<div style="flex-shrink: 0;">' . "\n";
	$href = SITE_URL . sprintf( 'team-update.php?team_id=%d', $team->team_id );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-green" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
	$href = SITE_URL . sprintf( 'team-delete.php?team_id=%d', $team->team_id );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
} );
page_body_add( [ $panel, 'html' ], team::select_admin() );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= season_href( 'team-insert.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

page_html();