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

	public function select_prev() {
		global $db;
		$stmt = $db->prepare( '
SELECT *
FROM `xa_season`
WHERE `year` < ?
ORDER BY `year` DESC
LIMIT 1
		' );
		$stmt->bind_param( 'i', $this->year );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'season' ) ) )
			$items[] = $item;
		$rslt->free();
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
