<?php

class follow extends entity {

	const FIELDS = [
		'follow_id'   => 'i',
		'child_id'    => 'i',
		'season_id'   => 'i',
		'grade_id'    => 'i',
		'location_id' => 'i',
	];

	public $follow_id;
	public $child_id;
	public $season_id;
	public $grade_id;
	public $location_id;
}