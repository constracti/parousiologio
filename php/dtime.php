<?php

class dtime extends DateTime {

	const FORMAT = 'Y-m-d H:i:s';

	public static function from_php( int $php ): self {
		$dt = new self();
		$dt->setTimestamp( $php );
		return $dt;
	}

	public static function from_sql( string $sql ): self {
		$dt = self::createFromFormat( self::FORMAT, $sql );
		return self::from_php( $dt->getTimestamp() );
	}

	public function to_php(): int {
		return $this->getTimestamp();
	}

	public function to_sql(): string {
		return $this->format( self::FORMAT );
	}

	public static function php2sql( int $php ): string {
		return self::from_php( $php )->to_sql();
	}

	public static function sql2php( string $sql ): int {
		$dt = self::createFromFormat( self::FORMAT, $sql );
		return $dt->getTimestamp();;
	}
}