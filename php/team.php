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

	public function select_children(): array {
		global $db;
		$sql = file_get_contents( SITE_DIR . 'sql/team_children.sql' );
		$stmt = $db->prepare( $sql );
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

	public function select_events(): array {
		global $db;
		$sql = file_get_contents( SITE_DIR . 'sql/team_events.sql' );
		$stmt = $db->prepare( $sql );
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

	public function select_presences(): array {
		global $db;
		$sql = file_get_contents( SITE_DIR . 'sql/team_presences.sql' );
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
		$sql = file_get_contents( SITE_DIR . 'sql/team_child.sql' );
		$stmt = $db->prepare( $sql );
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
		$sql = file_get_contents( SITE_DIR . 'sql/team_event.sql' );
		$stmt = $db->prepare( $sql );
		$stmt->bind_param( 'ii', $this->team_id, $event_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$value = $rslt->num_rows > 0;
		$rslt->free();
		return $value;
	}
}

/*
define( 'TEAM_VIEW', '(
SELECT `xa_location`.`location_id`, `xa_location`.`location_name`, `xa_team`.`on_sunday`, `xa_location`.`is_swarm`, `xa_team`.`team_id`, `xa_team`.`team_name`, `xa_team`.`season_id`, MIN( `xa_target`.`grade_id` ) as `rank`
FROM `xa_team`
JOIN `xa_location` ON `xa_location`.`location_id` = `xa_team`.`location_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
GROUP BY `xa_team`.`team_id`
) AS `xa_team_view`' );
*/