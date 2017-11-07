<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Έτη' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'seasons.php' ),
	'text' => 'έτη',
	'icon' => 'fa-calendar',
	'hide_medium' => FALSE,
] );


/********
 * exit *
 ********/

page_body_add( function() {
	$seasons = season::select( [], [
		'year' => 'DESC',
		'season_id' => 'DESC',
	] );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<div class="w3-border w3-theme-l4">' . "\n";
	echo '<div class="flex w3-theme">' . "\n";
	echo '<span style="font-size: large;">έτη</span>' . "\n";
	$href = site_href( 'season-insert.php' );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme-action" title="προσθήκη">', $href ) . "\n";
	echo '<span class="fa fa-plus"></span>' . "\n";
	echo '<span class="w3-hide-small">προσθήκη</span>' . "\n";
	echo '</a>' . "\n";
	echo '</div>' . "\n";
	foreach ( $seasons as $season_id => $season ) {
		$href = site_href( 'season-update.php', [ 'season_id' => $season_id ] );
		echo sprintf( '<a href="%s" class="flex w3-button w3-border-top w3-left-align">', $href ) . "\n";
		echo '<div class="w3-panel w3-leftbar" style="white-space: normal;">' . "\n";
		if ( !is_null( $season->slogan_old ) ) {
			echo '<p>' . "\n";
			echo sprintf( '<i>%s</i>', $season->slogan_old ) . "\n";
			if ( !is_null( $season->source ) )
				echo sprintf( '<span style="font-size: small; white-space: nowrap;">(%s)</span>', $season->source ) . "\n";
			echo '</p>' . "\n";
		}
		if ( !is_null( $season->slogan_new ) )
			echo sprintf( '<span>%s</span>', $season->slogan_new ) . "\n";
		echo '</div>' . "\n";
		echo sprintf( '<span style="flex-shrink: 0; font-size: large;">%d</span>', $season->year ) . "\n";
		echo '</a>' . "\n";
	}
	echo '</div>' . "\n";
	echo '</section>' . "\n";
} );


/********
 * exit *
 ********/

page_html();