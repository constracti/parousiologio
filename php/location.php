<?php

class location extends entity {

	const FIELDS = [
		'location_id'   => 'i',
		'location_name' => 's',
		#'on_sunday'     => 'i',
		'is_swarm'      => 'i',
	];

	public $location_id;   # integer, primary key
	public $location_name; # varchar
	#public $on_sunday;     # integer
	public $is_swarm;      # integer

	public function select_seasons(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_season`.*, COUNT( `xa_team`.`team_id` ) AS `location_teams`
FROM `xa_season`
LEFT JOIN `xa_team` ON `xa_season`.`season_id` = `xa_team`.`season_id` AND `xa_team`.`location_id` = ?
GROUP BY `xa_season`.`season_id`
ORDER BY `xa_season`.`year` DESC, `xa_season`.`season_id` DESC
		' );
		$stmt->bind_param( 'i', $this->location_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$seasons = [];
		while ( !is_null( $season = $rslt->fetch_object( 'season' ) ) )
			$seasons[] = $season;
		$rslt->free();
		return $seasons;
	}

	public static function select_options(): array {
		$locations = location::select( [], [
			'is_swarm' => 'DESC',
			'location_name' => 'ASC',
			'location_id' => 'ASC',
		] );
		$options = [];
		foreach ( $locations as $location )
			$options[ $location->location_id ] = $location->location_name;
		return $options;
	}
}