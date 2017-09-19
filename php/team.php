<?php

# TODO move `on_sunday` from `team` to `location` - see classes

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

	public function get_children(): array {
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
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'child' ) ) )
			$items[ $item->child_id ] = $item;
		$rslt->free();
		return $items;
	}

	public function get_events(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_event`.*
FROM `xa_team`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
JOIN `xa_participate` ON `xa_team`.`location_id` = `xa_participate`.`location_id`
JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
JOIN `xa_regard` ON `xa_regard`.`grade_id` = `xa_target`.`grade_id` AND `xa_regard`.`event_id` = `xa_event`.`event_id`
WHERE `xa_team`.`team_id` = ?
ORDER BY `xa_event`.`date` ASC, `xa_event`.`event_id` ASC;
		' );
		$stmt->bind_param( 'i', $this->team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'event' ) ) )
			$items[ $item->event_id ] = $item;
		$rslt->free();
		return $items;
	}

	public function get_grades(): array {
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
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'grade' ) ) )
			$items[ $item->grade_id ] = $item;
		$rslt->free();
		return $items;
	}

	public function get_presences( string $mode ): array {
		global $db;
		$sql = '
SELECT `xa_child`.`child_id`, `xa_event`.`event_id`, `xa_presence`.`child_id` IS NOT NULL AS `check`
FROM `xa_team`
JOIN `xa_follow` ON `xa_team`.`location_id` = `xa_follow`.`location_id` AND `xa_team`.`season_id` = `xa_follow`.`season_id`
JOIN `xa_child` ON `xa_child`.`child_id` = `xa_follow`.`child_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id`
JOIN `xa_participate` ON `xa_team`.`location_id` = `xa_participate`.`location_id`
JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
JOIN `xa_regard` ON `xa_regard`.`grade_id` = `xa_follow`.`grade_id` AND `xa_regard`.`event_id` = `xa_event`.`event_id`
LEFT JOIN `xa_presence` ON `xa_child`.`child_id` = `xa_presence`.`child_id` AND `xa_event`.`event_id` = `xa_presence`.`event_id`
WHERE `xa_team`.`team_id` = ?
		';
		if ( $mode === 'desktop' )
			$sql .= '
ORDER BY `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC, `xa_event`.`date` ASC, `xa_event`.`event_id` ASC
			';
		elseif ( $mode === 'mobile' )
			$sql .= '
ORDER BY `xa_event`.`date` DESC, `xa_event`.`event_id` DESC, `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC
			';
		$stmt = $db->prepare( $sql );
		$stmt->bind_param( 'i', $this->team_id );
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
}