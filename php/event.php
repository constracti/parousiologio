<?php

# TODO rename `name` to `description`
# TODO rename `date` to `event_date`

# TODO check 'smart' queries: LEFT JOIN

class event extends entity {

	const FIELDS = [
		'event_id'  => 'i',
		'name'      => 's',
		'date'      => 's',
		'season_id' => 'i',
	];

	public $event_id;  # integer, primary key
	public $name;      # varchar, nullable
	public $date;      # date
	public $season_id; # integer

	public static function select_admin(): array {
		global $db;
		global $cyear;
		$stmt = $db->prepare( '
SELECT `xa_event`.*, `xa_grade`.*
FROM `xa_event`
LEFT JOIN `xa_regard` ON `xa_event`.`event_id` = `xa_regard`.`event_id`
LEFT JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_regard`.`grade_id`
JOIN `xa_season` ON `xa_season`.`season_id` = `xa_event`.`season_id`
WHERE `xa_season`.`year` = ?
ORDER BY `xa_event`.`date` DESC, `xa_event`.`event_id` DESC, `xa_grade`.`grade_id` ASC
		' );
		$stmt->bind_param( 'i', $cyear );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'event' ) ) )
			$items[] = $item;
		$rslt->free();
		return $items;
	}
}