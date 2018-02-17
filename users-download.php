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

require_once COMPOSER_DIR . 'phpexcel/vendor/autoload.php';

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()
	->setCreator( SITE_NAME )
	->setLastModifiedBy( SITE_NAME )
	->setTitle( $title );

$sheet = $objPHPExcel->getActiveSheet();

$sheet->setTitle( $title );

$r = 1;
foreach ( array_values( $cols ) as $c => $colname )
	$sheet->setCellValueByColumnAndRow( $c, $r, $colname );

foreach ( $users as $user ) {
	if ( $user->role < user::ROLE_BASIC )
		continue;
	$r++;
	foreach ( array_keys( $cols ) as $c => $col )
		$sheet->setCellValueByColumnAndRow( $c, $r, $user->$col );
}

header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
header( sprintf( 'Content-Disposition: attachment; filename="%s.xlsx"', $title ) );
$objWriter = PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel2007' );
$objWriter->save( 'php://output' );
exit;
