<?php

class dtime extends DateTime {

	const DATE = 'Y-m-d';
	const DATETIME = 'Y-m-d H:i:s';

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
}