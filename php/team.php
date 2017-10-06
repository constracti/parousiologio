<?php

class team extends entity {

	const FIELDS = [
		'team_id'     => 'i',
		'location_id' => 'i',
		'team_name'   => 's',
		'season_id'   => 'i',
		'on_sunday'   => 'i',
	];

	public $team_id;     # integer, primary key
	public $location_id; # integer
	public $team_name;   # varchar
	public $season_id;   # integer
	public $on_sunday;   # integer

	public function select_children(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_child`.*, `xa_grade`.`grade_name`
FROM `xa_team`
JOIN `xa_follow` ON `xa_team`.`location_id` = `xa_follow`.`location_id` AND `xa_team`.`season_id` = `xa_follow`.`season_id`
JOIN `xa_child` ON `xa_child`.`child_id` = `xa_follow`.`child_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id`
JOIN `xa_grade` ON `xa_follow`.`grade_id` = `xa_grade`.`grade_id`
WHERE `xa_team`.`team_id` = ?
ORDER BY `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC
		' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$children = [];
		while ( !is_null( $child = $rslt->fetch_object( 'child' ) ) )
			$children[ $child->child_id ] = $child;
		$rslt->free();
		return $children;
	}

	public function select_events(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_event`.*, `xa_event`.`event_date` - INTERVAL ( `xa_event`.`event_name` IS NULL AND NOT `xa_team`.`on_sunday` ) DAY AS `event_date_fixed`
FROM `xa_team`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
JOIN `xa_participate` ON `xa_team`.`location_id` = `xa_participate`.`location_id`
JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
JOIN `xa_regard` ON `xa_regard`.`grade_id` = `xa_target`.`grade_id` AND `xa_regard`.`event_id` = `xa_event`.`event_id`
WHERE `xa_team`.`team_id` = ?
GROUP BY `xa_event`.`event_id`
ORDER BY `event_date_fixed` ASC, `xa_event`.`event_id` ASC
		' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$events = [];
		while ( !is_null( $event = $rslt->fetch_object( 'event' ) ) )
			$events[ $event->event_id ] = $event;
		$rslt->free();
		return $events;
	}

	public function select_grades(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_grade`.*
FROM `xa_grade`
JOIN `xa_target` ON `xa_grade`.`grade_id` = `xa_target`.`grade_id`
WHERE `xa_target`.`team_id` = ?
ORDER BY `xa_grade`.`grade_id` ASC
		' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$grades = [];
		while ( !is_null( $grade = $rslt->fetch_object( 'grade' ) ) )
			$grades[ $grade->grade_id ] = $grade;
		$rslt->free();
		return $grades;
	}

	public function check_presences(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_child`.`child_id`, `xa_event`.`event_id`, `xa_presence`.`child_id` IS NOT NULL AS `check`
FROM `xa_team`
JOIN `xa_child` ON `xa_child`.`child_id` IN (
	SELECT `xa_follow`.`child_id`
	FROM `xa_team`
	JOIN `xa_follow` ON `xa_follow`.`season_id` = `xa_team`.`season_id` AND `xa_follow`.`location_id` = `xa_team`.`location_id`
	JOIN `xa_target` ON `xa_target`.`team_id` = `xa_team`.`team_id` AND `xa_target`.`grade_id` = `xa_follow`.`grade_id`
	WHERE `xa_team`.`team_id` = ?
)
JOIN `xa_event` ON `xa_event`.`event_id` IN (
	SELECT `xa_event`.`event_id`
	FROM `xa_team`
	JOIN `xa_target` ON `xa_target`.`team_id` = `xa_team`.`team_id`
	JOIN `xa_participate` ON `xa_participate`.`location_id` = `xa_team`.`location_id`
	JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
	JOIN `xa_regard` ON `xa_regard`.`event_id` = `xa_event`.`event_id` AND `xa_regard`.`grade_id` = `xa_target`.`grade_id`
	WHERE `xa_team`.`team_id` = ?
	GROUP BY `xa_event`.`event_id`
)
LEFT JOIN `xa_presence` ON `xa_child`.`child_id` = `xa_presence`.`child_id` AND `xa_event`.`event_id` = `xa_presence`.`event_id`
WHERE `team_id` = ?
ORDER BY `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC,
`xa_event`.`event_date` - INTERVAL ( `xa_event`.`event_name` IS NULL AND NOT `xa_team`.`on_sunday` ) DAY ASC, `xa_event`.`event_id` ASC		' );
		$stmt->bind_param( 'iii', $this->team_id, $this->team_id, $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object() ) )
			$items[] = $item;
		$rslt->free();
		return $items;
	}

	public function has_child( int $child_id ): bool {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_team`.`team_id`
FROM `xa_team`
JOIN `xa_follow` ON `xa_team`.`location_id` = `xa_follow`.`location_id` AND `xa_team`.`season_id` = `xa_follow`.`season_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id`
WHERE `xa_team`.`team_id` = ? AND `xa_follow`.`child_id` = ?
		' );
		$stmt->bind_param( 'ii', $this->team_id, $child_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$value = $rslt->num_rows > 0;
		$rslt->free();
		return $value;
	}

	public function has_event( int $event_id ): bool {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_team`.`team_id`
FROM `xa_team`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
JOIN `xa_participate` ON `xa_team`.`location_id` = `xa_participate`.`location_id`
JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
JOIN `xa_regard` ON `xa_regard`.`grade_id` = `xa_target`.`grade_id` AND `xa_regard`.`event_id` = `xa_event`.`event_id`
WHERE `xa_team`.`team_id` = ? AND `xa_event`.`event_id` = ?
		' );
		$stmt->bind_param( 'ii', $this->team_id, $event_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$value = $rslt->num_rows > 0;
		$rslt->free();
		return $value;
	}

	/* grades */

	public function check_grades(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_category`.`category_id`, `xa_category`.`category_name`, `xa_grade`.`grade_id`, `xa_grade`.`grade_name`, `xa_target`.`grade_id` IS NOT NULL AS `check`
FROM `xa_grade`
JOIN `xa_category` ON `xa_category`.`category_id` = `xa_grade`.`category_id`
LEFT JOIN `xa_target` ON `xa_grade`.`grade_id` = `xa_target`.`grade_id` AND `xa_target`.`team_id` = ?
ORDER BY `xa_grade`.`grade_id` ASC
		' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$grades = [];
		while ( !is_null( $grade = $rslt->fetch_object( 'grade' ) ) )
			$grades[ $grade->grade_id ] = $grade;
		$rslt->free();
		return $grades;
	}

	public function insert_grade( int $grade_id ) {
		global $db;
		$stmt = $db->prepare( 'INSERT INTO `xa_target` ( `team_id`, `grade_id` ) VALUES ( ?, ? )' );
		$stmt->bind_param( 'ii', $this->team_id, $grade_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_grade( int $grade_id ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_target` WHERE `team_id` = ? AND `grade_id` = ?' );
		$stmt->bind_param( 'ii', $this->team_id, $grade_id );
		$stmt->execute();
		$stmt->close();
	}

	public function insert_grades( int $category_id ) {
		global $db;
		$stmt = $db->prepare( '
REPLACE INTO `xa_target` ( `team_id`, `grade_id` )
SELECT ? AS `team_id`, `xa_grade`.`grade_id`
FROM `xa_grade`
WHERE `xa_grade`.`category_id` = ?
		' );
		$stmt->bind_param( 'ii', $this->team_id, $category_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_grades() {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_target` WHERE `team_id` = ?' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$stmt->close();
	}

	/* users */

	public function check_users(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_user`.`user_id`, `xa_user`.`last_name`, `xa_user`.`first_name`, `xa_access`.`user_id` IS NOT NULL AS `check`
FROM `xa_user`
LEFT JOIN `xa_access` ON `xa_user`.`user_id` = `xa_access`.`user_id` AND `xa_access`.`team_id` = ?
ORDER BY `xa_user`.`last_name` ASC, `xa_user`.`first_name` ASC, `xa_user`.`user_id` ASC
		' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$users = [];
		while ( !is_null( $user = $rslt->fetch_object( 'user' ) ) )
			$users[ $user->user_id ] = $user;
		$rslt->free();
		return $users;
	}

	public function insert_user( int $user_id ) {
		global $db;
		$stmt = $db->prepare( 'INSERT INTO `xa_access` ( `team_id`, `user_id` ) VALUES ( ?, ? )' );
		$stmt->bind_param( 'ii', $this->team_id, $user_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_user( int $user_id ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_access` WHERE `team_id` = ? AND `user_id` = ?' );
		$stmt->bind_param( 'ii', $this->team_id, $user_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_users() {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_access` WHERE `team_id` = ?' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$stmt->close();
	}
}