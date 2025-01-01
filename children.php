<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$query = request_var( 'query', TRUE );
$wherec = !is_null( $query ) ? [ 'LIKE', 'last_name', '%' . $query . '%' ] : NULL;
$child_count = child::count( $wherec );
$page_size = 20;
$page_count = intval( ceil( $child_count / $page_size ) );
if ( $page_count === 0 )
	$page_count = 1;
$page = request_int( 'page', TRUE ) ?? 1;
$child_list = child::select( $wherec, [
	'last_name' => 'ASC',
	'first_name' => 'ASC',
	'child_id' => 'ASC',
], [ ($page - 1) * $page_size, $page_size ] );

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

page_body_add( function( ?string $query, int $page_count, int $page ) {
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<form autocomplete="off" method="get" class="w3-row">' . "\n";
	echo '<div class="w3-col l6 s12">' . "\n";
	echo sprintf( '<input class="w3-input" type="text" name="query" value="%s" placeholder="Επώνυμο" style="display: inline-block; width: initial;">', $query ) . "\n";
	echo '<button class="w3-button w3-round w3-theme" type="submit">Αναζήτηση</button>' . "\n";
	echo '</div>' . "\n";
	echo '<div class="w3-col l6 s12 w3-right-align">' . "\n";
	$href = site_href( 'children.php', [
		'query' => $query,
		'page' => 1,
	] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-double-left"></span></a>', $href ) . "\n";
	$href = site_href( 'children.php', [
		'query' => $query,
		'page' => $page > 1 ? $page - 1 : 1,
	] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-left"></span></a>', $href ) . "\n";
	echo sprintf( '<input class="w3-input" type="number" name="page" value="%d" style="display: inline-block; width: initial;" min="1" max="%d" />', $page, $page_count ) . "\n";
	echo '<span>από</span>' . "\n";
	echo sprintf( '<span>%d</span>', $page_count ) . "\n";
	$href = site_href( 'children.php', [
		'query' => $query,
		'page' => $page < $page_count ? $page + 1 : $page_count,
	] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-right"></span></a>', $href ) . "\n";
	$href = site_href( 'children.php', [
		'query' => $query,
		'page' => $page_count,
	] );
	echo sprintf( '<a href="%s" class="w3-button w3-round w3-theme"><span class="fa fa-angle-double-right"></span></a>', $href ) . "\n";
	echo '</div>' . "\n";
	echo '</form>' . "\n";
	echo '</section>' . "\n";
}, $query, $page_count, $page );

page_body_add( function( array $child_list ) {
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
	foreach ( $child_list as $child ) {
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
}, $child_list );

page_html();
