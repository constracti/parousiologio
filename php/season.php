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

	public static function select_options(): array {
		$seasons = season::select( [], [
			'year' => 'DESC'
		] );
		$options = [];
		foreach ( $seasons as $season )
			if ( !is_null( $season->slogan_old ) )
				$options[ $season->season_id ] = sprintf( '%d: %s', $season->year, $season->slogan_old );
			else
				$options[ $season->season_id ] = sprintf( '%d', $season->year );
		return $options;
	}
}
