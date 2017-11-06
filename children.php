<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$count = child::count();
$page = request_int( 'page', TRUE ) ?? 1;

page_title_set( 'Παιδιά' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'children.php' ),
	'text' => 'παιδιά',
	'icon' => 'fa-child',
	'hide_medium' => FALSE,
] );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= site_href( 'child-insert.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

function pagination( int $count, int $page ) {
	$pages = ceil( $count / 100 );
	echo '<section class="w3-panel w3-content w3-right-align">' . "\n";
	echo '<form autocomplete="off" method="get">' . "\n";
	$href = site_href( 'children.php', [ 'page' => 1 ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-double-left"></span></a>', $href ) . "\n";
	$href = site_href( 'children.php', [ 'page' => $page > 1 ? $page - 1 : 1 ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-left"></span></a>', $href ) . "\n";
	echo sprintf( '<input class="w3-input" type="number" name="page" value="%d" style="display: inline-block; width: initial;" min="1" max="%d" />', $page, $pages ) . "\n";
	echo '<span>από</span>' . "\n";
	echo sprintf( '<span>%d</span>', $pages ) . "\n";
	$href = site_href( 'children.php', [ 'page' => $page < $pages ? $page + 1 : $pages ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-right"></span></a>', $href ) . "\n";
	$href = site_href( 'children.php', [ 'page' => $pages ] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-double-right"></span></a>', $href ) . "\n";
	echo '</form>' . "\n";
	echo '</section>' . "\n";
}

page_body_add( 'pagination', $count, $page );

page_body_add( function() {
	global $page;
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	$children = child::select( [], [
		'last_name' => 'ASC',
		'first_name' => 'ASC',
		'child_id' => 'ASC',
	], [ ($page - 1) * 100, 100 ] );
	foreach ( $children as $child ) {
		echo '<li class="flex">' . "\n";
		echo sprintf( '<div>%s %s</div>', $child->last_name, $child->first_name ) . "\n";
		echo '<div style="flex-shrink: 0;">' . "\n";
		$href = site_href( 'child-update.php', [ 'child_id' => $child->child_id ] );
		echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme" title="επεξεργασία"><span class="fa fa-pencil"></span></a>', $href ) . "\n";
		$href = site_href( 'follows.php', [ 'child_id' => $child->child_id ] );
		echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme" title="έτη"><span class="fa fa-calendar"></span></a>', $href ) . "\n";
		$href = site_href( 'child-delete.php', [ 'child_id' => $child->child_id ] );
		echo sprintf( '<a href="%s" class="w3-button w3-round w3-red link-ajax" title="διαγραφή" data-confirm="οριστική διαγραφή;" data-remove="li"><span class="fa fa-trash"></span></a>', $href ) . "\n";
		echo '</div>' . "\n";
		echo '</li>' . "\n";
	}
	echo '</ul>' . "\n";
	echo '</section>' . "\n";
} );

page_body_add( 'pagination', $count, $page );

page_html();