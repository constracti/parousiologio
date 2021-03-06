<?php

class dtime extends DateTime {

	const DATE = 'Y-m-d';
	const DATETIME = 'Y-m-d H:i:s';

	const INTERVALS = [
		'y' => [
			's' => 'έτος',
			'p' => 'έτη',
		],
		'm' => [
			's' => 'μήνας',
			'p' => 'μήνες',
		],
		'd' => [
			's' => 'ημέρα',
			'p' => 'ημέρες',
		],
		'h' => [
			's' => 'ώρα',
			'p' => 'ώρες',
		],
		'i' => [
			's' => 'λεπτό',
			'p' => 'λεπτά',
		],
		's' => [
			's' => 'δευτερόλεπτο',
			'p' => 'δευτερόλεπτα',
		],
	];

	public static function from_php( int $php ): self {
		$dt = new self();
		$dt->setTimestamp( $php );
		return $dt;
	}

	public static function from_sql( string $sql, string $format = self::DATETIME ): self {
		$dt = self::createFromFormat( $format, $sql );
		return self::from_php( $dt->getTimestamp() );
	}

	public function to_php(): int {
		return $this->getTimestamp();
	}

	public function to_sql( string $format = self::DATETIME ): string {
		return $this->format( $format );
	}

	public static function php2sql( int $php, string $format = self::DATETIME ): string {
		return self::from_php( $php )->to_sql( $format );
	}

	public static function sql2php( string $sql, string $format = self::DATETIME ): int {
		$dt = self::createFromFormat( $format, $sql );
		return $dt->getTimestamp();;
	}

	public function month_name(): string {
		switch ( intval( $this->format( 'n' ) ) ) {
			case  1: return 'Ιανουάριος';
			case  2: return 'Φεβρουάριος';
			case  3: return 'Μάρτιος';
			case  4: return 'Απρίλιος';
			case  5: return 'Μάιος';
			case  6: return 'Ιούνιος';
			case  7: return 'Ιούλιος';
			case  8: return 'Αύγουστος';
			case  9: return 'Σεπτέμβριος';
			case 10: return 'Οκτώβριος';
			case 11: return 'Νοέμβριος';
			case 12: return 'Δεκέμβριος';
			default: return '';
		}
	}

	public function weekday_name(): string {
		switch ( intval( $this->format( 'w' ) ) ) {
			case 0: return 'Κυριακή';
			case 1: return 'Δευτέρα';
			case 2: return 'Τρίτη';
			case 3: return 'Τετάρτη';
			case 4: return 'Πέμπτη';
			case 5: return 'Παρασκευή';
			case 6: return 'Σάββατο';
			default: return '';
		}
	}

	public function weekday_short_name(): string {
		switch ( intval( $this->format( 'w' ) ) ) {
			case 0: return 'Κυ';
			case 1: return 'Δε';
			case 2: return 'Τρ';
			case 3: return 'Τε';
			case 4: return 'Πε';
			case 5: return 'Πα';
			case 6: return 'Σα';
			default: return '';
		}
	}

	public function human_diff( $self = NULL ): string {
		if ( is_null( $self ) )
			$self = new self();
		$di = $this->diff( $self );
		foreach ( self::INTERVALS as $prop => $val ) {
			if ( $di->$prop === 0 )
				continue;
			if ( $di->$prop === 1 )
				return sprintf( '%d %s', $di->$prop, $val['s'] );
			return sprintf( '%d %s', $di->$prop, $val['p'] );
		}
		return '';
	}
}
