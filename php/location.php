<?php

class location extends entity {

	const FIELDS = [
		'location_id'   => 'i',
		'location_name' => 's',
		'is_swarm'      => 'i',
	];

	public $location_id;   # integer, primary key
	public $location_name; # varchar
	public $is_swarm;      # integer

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
