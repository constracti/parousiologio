<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

$title = 'Χρήστες';

$cols = user::COLS;
$users = user::select( [], [
	'last_name' => 'ASC',
	'first_name' => 'ASC',
	'user_id' => 'ASC',
] );

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

foreach ( $users as $user ) {
	if ( $user->role < user::ROLE_BASIC )
		continue;
	$r++;
	foreach ( array_keys( $cols ) as $c => $col )
		$sheet->setCellValueByColumnAndRow( $c + 1, $r, $user->$col );
}

header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
header( sprintf( 'Content-Disposition: attachment; filename="%s.xlsx"', $title ) );
$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
$writer->save( 'php://output' );
exit;
