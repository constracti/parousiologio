<?php

class follow extends entity {

	const FIELDS = [
		'follow_id'   => 'i',
		'child_id'    => 'i',
		'season_id'   => 'i',
		'grade_id'    => 'i',
		'location_id' => 'i',
	];

	public $follow_id;   # integer, primary key
	public $child_id;    # integer
	public $season_id;   # integer
	public $grade_id;    # integer
	public $location_id; # integer, nullable
}