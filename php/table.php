<?php

class table {

	public $arguments;
	public $filters;
	public $cols;

	public function __construct( array $arguments = [] ) {
		if ( !array_key_exists( 'full_screen', $arguments ) )
			$arguments['full_screen'] = FALSE;
		if ( !array_key_exists( 'title', $arguments ) )
			$arguments['title'] = NULL;
		if ( !array_key_exists( 'filters', $arguments ) )
			$arguments['filters'] = [];
		$this->arguments = $arguments;
		$this->filters = [];
		$this->cols = [];
	}

	public function add( string $key, string $name, callable $cell, $sort = NULL ) {
		$this->cols[$key] = [
			'name' => $name,
			'cell' => $cell,
			'sort' => $sort,
		];
	}

	public function add_filter( string $key, string $name, array $options = [] ) {
		$this->filters[$key] = [
			'name' => $name,
			'options' => $options,
		];
	}

	public function html( array $items = [] ) {
		if ( $this->arguments['full_screen'] )
			echo '<section class="w3-panel">' . "\n";
		else
			echo '<section class="w3-panel w3-content">' . "\n";
		if ( !is_null( $this->arguments['title'] ) )
			echo sprintf( '<h3>%s</h3>', $this->arguments['title'] ) . "\n";
		if ( !empty( $this->filters ) ) {
			echo '<form>' . "\n";
			echo sprintf( '<input type="hidden" name="orderby" value="%s" />', htmlspecialchars( $_GET['orderby'] ) ) . "\n";
			echo sprintf( '<input type="hidden" name="order" value="%s" />', htmlspecialchars( $_GET['order'] ) ) . "\n";
			foreach ( $this->filters as $key => $filter ) {
				echo sprintf( '<select class="w3-select" name="%s" style="width: initial;">', $key ) . "\n";
				echo sprintf( '<option value="">%s</option>', $filter['name'] ) . "\n";
				foreach ( $filter['options'] as $value => $option ) {
					$selected = ( $_GET[$key] === strval( $value ) ) ? ' selected="selected"' : '';
					echo sprintf( '<option value="%s"%s>%s</option>', $value, $selected, $option ) . "\n";
				}
				echo '</select>' . "\n";
			}
			echo '<button class="w3-btn w3-round w3-theme-action" type="submit">φιλτράρισμα</button>' . "\n";
			echo '</form>' . "\n";
		}
		echo '<table class="xa-table w3-border w3-bordered w3-striped">' . "\n";
		echo '<thead class="w3-theme">' . "\n";
		echo '<tr>' . "\n";
		foreach ( $this->cols as $key => $col ) {
			if ( !is_null( $col['sort'] ) ) {
				parse_str( $_SERVER['QUERY_STRING'], $result );
				$result['orderby'] = $key;
				if ( $_GET['orderby'] === $key ) {
					if ( $_GET['order'] !== 'desc' ) {
						$icon = 'fa-sort-up';
						$result['order'] = 'desc';
						$hover = 'fa-sort-down';
					} else {
						$icon = 'fa-sort-down';
						$result['order'] = 'asc';
						$hover = 'fa-sort-up';
					}
				} else {
					$icon = 'fa-sort';
					if ( $col['sort'] ) {
						$result['order'] = 'asc';
						$hover = 'fa-sort-asc';
					} else {
						$result['order'] = 'desc';
						$hover = 'fa-sort-desc';
					}
				}
				echo sprintf( '<th class="xa-table-sort" data-col="%s">', $key ) . "\n";
				echo sprintf( '<a href="?%s">', http_build_query( $result ) ) . "\n";
				echo sprintf( '<span>%s</span>', $col['name'] ) . "\n";
				echo sprintf( '<span class="xa-table-sort-default fa %s"></span>', $icon ) . "\n";
				echo sprintf( '<span class="xa-table-sort-hover fa %s"></span>', $hover ) . "\n";
				echo '</a>' . "\n";
				echo '</th>' . "\n";
			} else {
				echo sprintf( '<th data-col="%s">%s</th>', $key, $col['name'] ) . "\n";
			}
		}
		echo '</tr>' . "\n";
		echo '</thead>' . "\n";
		echo '<tbody>' . "\n";
		foreach ( $items as $item ) {
			echo '<tr class="xa-table-hidden">' . "\n";
			$primary = TRUE;
			foreach ( $this->cols as $key => $col ) {
				echo sprintf( '<td data-col="%s" data-colname="%s">', $key, $col['name'] ) . "\n";
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
			echo sprintf( '<th data-col="%s">%s</th>', $key, $col['name'] ) . "\n";
		echo '</tr>' . "\n";
		echo '</tfoot>' . "\n";
		echo '</table>' . "\n";
		echo '</section>' . "\n";
	}
}