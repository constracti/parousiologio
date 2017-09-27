<?php

require_once 'php/core.php';

privilege( user::ROLE_OBSER );

page_title_set( 'Προβολή' );

page_nav_add( 'season_dropdown', [
	'href' => 'view.php',
	'text' => 'προβολή',
	'icon' => 'fa-list',
] );

$panel = new panel();
$panel->add( NULL, function( team $team ) {
	echo '<section class="flex flex-equal" style="flex-wrap: wrap; justify-content: center; align-items: flex-start;">' . "\n";
}, function( team $team ) {
	echo '</section>' . "\n";
} );
$panel->add( 'location_id', function( team $team ) {
	echo '<div class="flex-l4 flex-m6 flex-s12 w3-border w3-theme-l4">' . "\n";
	echo '<div class="flex w3-theme">' . "\n";
	echo sprintf( '<div style="font-size: large;">%s</div>', $team->location_name ) . "\n";
	echo sprintf( '<div style="flex-shrink: 0; text-align: right;">%s<br />%s</div>', $team->is_swarm ? 'ομάδα' : 'κατηχητικό', $team->on_sunday ? 'Κυριακή' : 'Σάββατο' ) . "\n";
	echo '</div>' . "\n";
}, function( team $team ) {
	echo '</div>' . "\n";
} );
$panel->add( 'team_id', function( team $team ) {
	if ( is_null( $team->team_id ) )
		return;
	$href = SITE_URL . sprintf( 'presences.php?team_id=%d', $team->team_id );
	echo sprintf( '<a href="%s" class="flex w3-button w3-block w3-border-top w3-left-align" style="white-space: normal;">', $href ) . "\n";
	echo '<div>' . "\n";
	echo sprintf( '<div>%s</div>', $team->team_name ) . "\n";
	echo '<div>' . "\n";
	$panel = new panel();
	$panel->add( 'category_id', function( grade $grade ) {
		echo '<div>' . "\n";
	}, function( grade $grade ) {
		echo '</div>' . "\n";		
	} );
	$panel->add( 'grade_id', function( grade $grade ) {
		echo sprintf( '<span class="w3-tag w3-round w3-theme" style="font-size: small;">%s</span>', $grade->grade_name ) . "\n";
	} );
	$panel->html( $team->select_grades() );
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '</a>' . "\n";
} );
page_body_add( [ $panel, 'html' ], team::select_admin() );

page_html();