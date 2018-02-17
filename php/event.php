<?php

class event extends entity {

	const FIELDS = [
		'event_id'   => 'i',
		'event_name' => 's',
		'event_date' => 's',
		'season_id'  => 'i',
	];

	public $event_id;   # integer, primary key
	public $event_name; # varchar, nullable
	public $event_date; # date
	public $season_id;  # integer

	/* grades */

	public function check_grades(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_category`.`category_id`, `xa_category`.`category_name`, `xa_grade`.`grade_id`, `xa_grade`.`grade_name`, `xa_regard`.`event_id` IS NOT NULL AS `check`
FROM `xa_grade`
JOIN `xa_category` ON `xa_category`.`category_id` = `xa_grade`.`category_id`
JOIN `xa_event`
LEFT JOIN `xa_regard` ON `xa_grade`.`grade_id` = `xa_regard`.`grade_id` AND `xa_event`.`event_id` = `xa_regard`.`event_id`
WHERE `xa_event`.`event_id` = ?
ORDER BY `xa_grade`.`grade_id` ASC
		' );
		$stmt->bind_param( 'i', $this->event_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'grade' ) ) )
			$items[] = $item;
		$rslt->free();
		return $items;
	}

	public function insert_grade( int $grade_id ) {
		global $db;
		$stmt = $db->prepare( 'INSERT INTO `xa_regard` ( `event_id`, `grade_id` ) VALUES ( ?, ? )' );
		$stmt->bind_param( 'ii', $this->event_id, $grade_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_grade( int $grade_id ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_regard` WHERE `event_id` = ? AND `grade_id` = ?' );
		$stmt->bind_param( 'ii', $this->event_id, $grade_id );
		$stmt->execute();
		$stmt->close();
	}

	public function insert_grades( int $category_id ) {
		global $db;
		$stmt = $db->prepare( '
REPLACE INTO `xa_regard` ( `event_id`, `grade_id` )
SELECT ? AS `event_id`, `xa_grade`.`grade_id`
FROM `xa_grade`
WHERE `xa_grade`.`category_id` = ?
		' );
		$stmt->bind_param( 'ii', $this->event_id, $category_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_grades() {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_regard` WHERE `event_id` = ?' );
		$stmt->bind_param( 'i', $this->event_id );
		$stmt->execute();
		$stmt->close();
	}

	/* locations */

	public function check_locations(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_location`.`location_name`, `xa_location`.`is_swarm`, `xa_participate`.`event_id` IS NOT NULL AS `check`
FROM `xa_location`
JOIN `xa_event`
LEFT JOIN `xa_participate` ON `xa_location`.`location_id` = `xa_participate`.`location_id` AND `xa_event`.`event_id` = `xa_participate`.`event_id`
WHERE `xa_event`.`event_id` = ?
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC
		' );
		$stmt->bind_param( 'i', $this->event_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'location' ) ) )
			$items[] = $item;
		$rslt->free();
		return $items;
	}

	public function insert_location( int $location_id ) {
		global $db;
		$stmt = $db->prepare( 'INSERT INTO `xa_participate` ( `event_id`, `location_id` ) VALUES ( ?, ? )' );
		$stmt->bind_param( 'ii', $this->event_id, $location_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_location( int $location_id ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_participate` WHERE `event_id` = ? AND `location_id` = ?' );
		$stmt->bind_param( 'ii', $this->event_id, $location_id );
		$stmt->execute();
		$stmt->close();
	}

	public function insert_locations( int $is_swarm ) {
		global $db;
		$stmt = $db->prepare( '
REPLACE INTO `xa_participate` ( `event_id`, `location_id` )
SELECT ? AS `event_id`, `xa_location`.`location_id`
FROM `xa_location`
WHERE `xa_location`.`is_swarm` = ?
		' );
		$stmt->bind_param( 'ii', $this->event_id, $is_swarm );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_locations() {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_participate` WHERE `event_id` = ?' );
		$stmt->bind_param( 'i', $this->event_id );
		$stmt->execute();
		$stmt->close();
	}
}
