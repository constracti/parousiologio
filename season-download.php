<?php

require_once 'php/core.php';

privilege( user::ROLE_OBSER );

$title = sprintf( 'Παιδιά %d', $cseason->year );

$cols = array_merge( [
	'location_name' => 'περιοχή',
	'last_name' => 'επώνυμο',
	'first_name' => 'όνομα',
], child::COLS );
$children = ( function(): array {
	global $db;
	global $cseason;
	$stmt = $db->prepare( '
SELECT `xa_child`.*, `xa_location`.`location_name`, `xa_grade`.`grade_name`
FROM `xa_follow`
JOIN `xa_child` ON `xa_follow`.`child_id` = `xa_child`.`child_id`
JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_follow`.`grade_id`
LEFT JOIN `xa_location` ON `xa_location`.`location_id` = `xa_follow`.`location_id`
WHERE `xa_follow`.`season_id` = ?
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_location`.`location_id` ASC,
`xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC
	' );
	$stmt->bind_param( 'i', $cseason->season_id );
	$stmt->execute();
	$rslt = $stmt->get_result();
	$stmt->close();
	$children = [];
	while ( !is_null( $child = $rslt->fetch_object( 'child' ) ) )
		$children[ $child->child_id ] = $child;
	$rslt->free();
	return $children;
} )();

$cols['meta_mobile'] = 'κινητό ενημέρωσης';
foreach ( $children as $child ) {
	switch ( $child->get_meta( 'mobile' ) ) {
		case 'self':
			$child->meta_mobile = $child->mobile_phone;
			break;
		case 'fath':
			$child->meta_mobile = $child->fath_mobile;
			break;
		case 'moth':
			$child->meta_mobile = $child->moth_mobile;
			break;
		default:
			$child->meta_mobile = NULL;
	}
}

$cols['meta_comments'] = 'σχόλια';
foreach ( $children as $child ) {
	$child->meta_comments = $child->get_meta( 'comments' );
}

$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$spreadsheet->getProperties()
	->setCreator( SITE_NAME )
	->setLastModifiedBy( SITE_NAME )
	->setTitle( $title );

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle( $title );

$r = 1;
foreach ( array_values( $cols ) as $c => $colname )
	$sheet->setCellValueByColumnAndRow( $c + 1, $r, $colname );

foreach ( $children as $child ) {
	$r++;
	foreach ( array_keys( $cols ) as $c => $col )
		$sheet->setCellValueByColumnAndRow( $c + 1, $r, $child->$col );
}

header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
header( sprintf( 'Content-Disposition: attachment; filename="%s.xlsx"', $title ) );
$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
$writer->save( 'php://output' );
exit;
