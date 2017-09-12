<?php

class season extends entity {

	const FIELDS = [
		'season_id'  => 'i',
		'year'       => 'i',
		'slogan_old' => 's',
		'source'     => 's',
		'slogan_new' => 's',
	];

	public $season_id;  # integer, primary key
	public $year;       # integer, unique
	public $slogan_old; # varchar, nullable
	public $source;     # varchar, nullable
	public $slogan_new; # varchar, nullable

	public static function select_last() {
		$items = self::select( [], [ 'year' => 'DESC' ], 1);
		return array_shift( $items );
	}
}