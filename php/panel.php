<?php

class panel {

	public $groups;

	public function __construct() {
		$this->groups = [];
	}

	public function add( $comp, $open, $close = NULL ) {
		$this->groups[] = [
			'comp' => $comp,
			'open' => $open,
			'close' => $close,
		];
	}

	private function open( int $lvl, $item ) {
		$open = $this->groups[ $lvl ]['open'];
		if ( is_callable( $open ) )
			$open( $item );
		elseif ( is_string( $open ) )
			echo $open . "\n";
	}

	private function close( int $lvl, $item ) {
		$close = $this->groups[ $lvl ]['close'];
		if ( is_callable( $close ) )
			$close( $item );
		elseif ( is_string( $close ) )
			echo $close . "\n";
	}

	private function diff( int $lvl, $item, $next ): bool {
		$comp = $this->groups[ $lvl ]['comp'];
		if ( is_callable( $comp ) )
			return $comp( $item ) !== $comp( $next );
		elseif ( is_string( $comp ) )
			return $item->$comp !== $next->$comp;
		elseif ( is_null( $comp ) )
			return FALSE;
		return TRUE;
	}

	public function html( array $items ) {
		$item = array_shift( $items );
		if ( is_null( $item ) )
			return;
		$top = count( $this->groups );
		for ( $lvl = 0; $lvl < $top; $lvl++ )
			$this->open( $lvl, $item );
		for ( $next = array_shift( $items ) ; !is_null( $next ); $item = $next, $next = array_shift( $items ) ) {
			for ( $bot = 0; $bot < $top; $bot++ )
				if ( $this->diff( $bot, $item, $next ) )
					break;
			for ( $lvl = $top - 1; $lvl >= $bot; $lvl-- )
				$this->close( $lvl, $item );
			for ( $lvl = $bot; $lvl < $top; $lvl++ )
				$this->open( $lvl, $next );
		}
		for ( $lvl = $top - 1; $lvl >= 0; $lvl-- )
			$this->close( $lvl, $item );
	}
}