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
}