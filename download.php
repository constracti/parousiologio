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
			$child->meta_mobile = NULL;
	}
}

$cols['meta_comments'] = 'σχόλια';
foreach ( $children as $child ) {
	$child->meta_comments = $child->get_meta( 'comments' );
}

$cols['presence_count'] = 'πλήθος παρουσιών';
$cols['presence_last'] = 'τελευταία παρουσία';
foreach ( $children as $child ) {
	$child->presence_list = [];
	$child->presence_last = NULL;
}
$events = $team->select_events();
foreach ( $team->check_presences() as $presence ) {
	if ( !$presence->check )
		continue;
	$child = $children[$presence->child_id];
	$child->presence_list[] = $events[ $presence->event_id ];
}
foreach ( $children as $child ) {
	usort( $child->presence_list, sorter( '~event_date_fixed' ) );
	$child->presence_count = count( $child->presence_list );
	if ( !empty( $child->presence_list ) )
		$child->presence_last = $child->presence_list[0]->event_date_fixed;
}

$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$spreadsheet->getProperties()
	->setCreator( SITE_NAME )
	->setLastModifiedBy( SITE_NAME )
	->setTitle( $title );

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle( sprintf( '%d', $cseason->year ) );

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
