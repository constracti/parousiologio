<?php

require_once 'php/core.php';

privilege( user::ROLE_OBSER );

$child_list = ( function(): array {
    global $db;
	global $cseason;
	$stmt = $db->prepare( '
SELECT `xa_child`.`child_id`, `xa_child`.`last_name`, `xa_child`.`first_name`, `xa_grade`.`grade_id`, `xa_grade`.`grade_name`
FROM `xa_follow`
LEFT JOIN `xa_child` ON `xa_child`.`child_id` = `xa_follow`.`child_id`
LEFT JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_follow`.`grade_id`
WHERE `xa_follow`.`season_id` = ? AND `xa_follow`.`location_id` IS NULL
ORDER BY `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC
	' );
	$stmt->bind_param( 'i', $cseason->season_id );
	$stmt->execute();
	$rslt = $stmt->get_result();
	$stmt->close();
	$item_list = [];
	while ( !is_null( $item = $rslt->fetch_object() ) )
		$item_list[] = $item;
	$rslt->free();
	return $item_list;
} )();

page_title_set( sprintf( 'Παιδιά χωρίς Περιοχή - %d', $cseason->year ) );

page_nav_add( 'season_dropdown', [
	'href' => 'location-null.php',
] );

page_body_add( function( array $child_list ): void {
	if ( empty( $child_list ) ) {
		echo '<div class="w3-panel w3-content">' . "\n";
		echo '<div class="w3-border w3-theme-l4 w3-padding">' . "\n";
		echo '<p>δεν βρέθηκαν εγγραφές</p>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	} else {
		echo '<section class="w3-panel w3-content">' . "\n";
		echo '<ul class="w3-ul w3-border w3-theme-l4">' . "\n";
		foreach ( $child_list as $child ) {
			echo '<li class="flex">' . "\n";
			echo sprintf( '<a href="%s">%s %s</a>',
				site_href( 'child-update.php', [ 'child_id' => $child->child_id ] ),
				$child->last_name,
				$child->first_name,
			) . "\n";
			echo sprintf( '<div>%s</div>', $child->grade_name ) . "\n";
			echo '</li>' . "\n";
		}
		echo '</ul>' . "\n";
		echo '</section>' . "\n";
	}
}, $child_list );

page_html();
