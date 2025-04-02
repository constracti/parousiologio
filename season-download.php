<?php

require_once 'php/core.php';

privilege( user::ROLE_OBSER );

$title = sprintf( 'Παιδιά %d', $cseason->year );

$cols = array_merge( [
	'location_name' => 'περιοχή',
	'last_name' => 'επώνυμο',
	'first_name' => 'όνομα',
], child::COLS );

$follow_list = follow::select( [
	'season_id' => $cseason->season_id,
] );

$child_list = child::select();

$grades = grade::select();

$location_list = location::select();

$location_null = (object) [
	'is_swarm' => -1,
	'location_name' => NULL,
	'location_id' => NULL,
];

foreach ( $follow_list as $follow ) {
	$follow->child = $child_list[$follow->child_id];
	$follow->grade = $grades[$follow->grade_id];
	$follow->location = !is_null( $follow->location_id ) ? $location_list[$follow->location_id] : $location_null;
	$follow->cols = [
		'location_name' => $follow->location->location_name,
		'grade_name' => $follow->grade->grade_name,
	];
}

usort( $follow_list, sorter(
	'~location/is_swarm',
	'location/location_name',
	'location/location_id',
	'child/last_name',
	'child/first_name',
	'child/child_id',
) );

$cols['meta_mobile'] = 'κινητό ενημέρωσης';
foreach ( $follow_list as $follow ) {
	$child = $follow->child;
	$follow->cols['meta_mobile'] = match ( $child->get_meta( 'mobile' ) ) {
		'self' => $child->mobile_phone,
		'fath' => $child->fath_mobile,
		'moth' => $child->moth_mobile,
		default => NULL,
	};
}

$cols['meta_comments'] = 'σχόλια';
foreach ( $follow_list as $follow ) {
	$child = $follow->child;
	$follow->cols['meta_comments'] = $child->get_meta( 'comments' );
}

$cols['presence_count'] = 'πλήθος παρουσιών';
$precense_count = ( function( season $season ): array {
	global $db;
	$stmt = $db->prepare( '

SELECT `xa_presence`.`child_id`, COUNT(`xa_event`.`event_id`) AS `child_presences`
FROM `xa_event`
LEFT JOIN `xa_presence`
ON `xa_presence`.`event_id` = `xa_event`.`event_id`
WHERE `xa_event`.`season_id` = ?
GROUP BY `xa_presence`.`child_id`
	' );
	$stmt->bind_param( 'i', $season->season_id );
	$stmt->execute();
	$rslt = $stmt->get_result();
	$stmt->close();
	$item_list = [];
	while ( !is_null( $item = $rslt->fetch_assoc() ) )
		$item_list[$item['child_id']] = $item['child_presences'];
	$rslt->free();
	return $item_list;
} )( $cseason );
foreach ( $follow_list as $follow )
	$follow->cols['presence_count'] = isset( $precense_count[$follow->child_id] ) ? $precense_count[$follow->child_id] : 0;

$cols['last_presence'] = 'τελευταία παρουσία';
$date_list = ( function(): array {
	global $db;
	$stmt = $db->prepare( '
SELECT `xa_presence`.`child_id`, MAX(`xa_event`.`event_date`) AS `event_date`
FROM `xa_presence`
JOIN `xa_event`
ON `xa_event`.`event_id` = `xa_presence`.`event_id`
GROUP BY `xa_presence`.`child_id`
	' );
	$stmt->execute();
	$rslt = $stmt->get_result();
	$stmt->close();
	$item_list = [];
	while ( !is_null( $item = $rslt->fetch_assoc() ) )
		$item_list[$item['child_id']] = $item['event_date'];
	$rslt->free();
	return $item_list;
} )();
foreach ( $follow_list as $follow )
	$follow->cols['last_presence'] = isset( $date_list[$follow->child_id] ) ? $date_list[$follow->child_id] : NULL;

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

foreach ( $follow_list as $follow ) {
	$r++;
	foreach ( array_keys( $cols ) as $c => $col ) {
		$value = array_key_exists( $col, $follow->cols ) ? $follow->cols[$col] : $follow->child->$col;
		$sheet->setCellValueByColumnAndRow( $c + 1, $r, $value );
	}
}

header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
header( sprintf( 'Content-Disposition: attachment; filename="%s.xlsx"', $title ) );
$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
$writer->save( 'php://output' );
exit;
