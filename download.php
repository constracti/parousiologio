<?php

require_once 'php/core.php';

privilege( user::ROLE_BASIC );

$team = team::request();
if ( !$cuser->accesses( $team->team_id ) )
	failure( 'argument_not_valid', 'team_id' );

$location = location::select_by( 'location_id', $team->location_id );

$title = sprintf( '%d %s - %s', $cseason->year, $location->location_name, $team->team_name );

$cols = array_merge( [
	'last_name' => 'επώνυμο',
	'first_name' => 'όνομα',
], child::COLS );
$children = $team->select_children();

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
			$child->meta_mobile = '';
	}
}

require_once COMPOSER_DIR . 'phpexcel/vendor/autoload.php';

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()
	->setCreator( SITE_NAME )
	->setLastModifiedBy( SITE_NAME )
	->setTitle( $title );

$sheet = $objPHPExcel->getActiveSheet();

$sheet->setTitle( sprintf( '%d', $cseason->year ) );

$r = 1;
foreach ( array_values( $cols ) as $c => $colname )
	$sheet->setCellValueByColumnAndRow( $c, $r, $colname );

foreach ( $children as $child ) {
	$r++;
	foreach ( array_keys( $cols ) as $c => $col )
		$sheet->setCellValueByColumnAndRow( $c, $r, $child->$col );
}

header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
header( sprintf( 'Content-Disposition: attachment; filename="%s.xlsx"', $title ) );
$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
$objWriter->save( 'php://output' );
exit;
