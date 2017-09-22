<?php

class table {

	public $arguments;
	public $cols;

	public function __construct( array $arguments = [] ) {
		if ( !array_key_exists( 'full_screen', $arguments ) )
			$arguments['full_screen'] = FALSE;
		if ( !array_key_exists( 'title', $arguments ) )
			$arguments['title'] = NULL;
		$this->arguments = $arguments;
		$this->cols = [];
	}

	public function add( string $name, callable $cell ) {
		$this->cols[] = [
			'name' => $name,
			'cell' => $cell,
		];
	}

	public function html( array $items = [] ) {
		if ( $this->arguments['full_screen'] )
			echo '<section class="w3-panel">' . "\n";
		else
			echo '<section class="w3-panel w3-content">' . "\n";
		if ( !is_null( $this->arguments['title'] ) )
			echo sprintf( '<h3>%s</h3>', $this->arguments['title'] ) . "\n";
		echo '<table class="xa-table w3-border w3-bordered w3-striped">' . "\n";
		echo '<thead class="w3-theme">' . "\n";
		echo '<tr>' . "\n";
		foreach ( $this->cols as $col )
			echo sprintf( '<th>%s</th>', $col['name'] ) . "\n";
		echo '</tr>' . "\n";
		echo '</thead>' . "\n";
		echo '<tbody>' . "\n";
		foreach ( $items as $item ) {
			echo '<tr class="xa-table-hidden">' . "\n";
			$primary = TRUE;
			foreach ( $this->cols as $col ) {
				echo sprintf( '<td data-colname="%s">', $col['name'] ) . "\n";
				$col['cell']( $item );
				if ( $primary )
					echo '<div><span class="fa"></div>' . "\n";
				echo '</td>' . "\n";
				$primary = FALSE;
			}
			echo '</tr>' . "\n";
		}
		echo '</tbody>' . "\n";
		echo '<tfoot class="w3-theme">' . "\n";
		echo '<tr>' . "\n";
		foreach ( $this->cols as $col )
			echo sprintf( '<th>%s</th>', $col['name'] ) . "\n";
		echo '</tr>' . "\n";
		echo '</tfoot>' . "\n";
		echo '</table>' . "\n";
		echo '</section>' . "\n";
	}
}