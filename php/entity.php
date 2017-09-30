<?php

abstract class entity {

	public static function select( array $where = [], array $orderby = [], $limit = NULL ): array {
		global $db;
		$class = get_called_class();
		$fields = $class::FIELDS;
		// PROPERTIES
		$props = [];
		$id = NULL;
		foreach ( $fields as $prop => $type ) {
			if ( is_null( $id ) ) {
				$id = $prop;
			}
			$props[] = sprintf( '`%s`', $prop );
		}
		$props = implode( ', ', $props );
		// WHERE
		$wh = [];
		$types = [];
		$values = [];
		foreach ( $where as $prop => $value ) {
			if ( !array_key_exists( $prop, $fields ) )
				continue;
			if ( is_null( $value ) ) {
				$wh[] = sprintf( '`%s` IS NULL', $prop );
			} else {
				$wh[] = sprintf( '`%s` = ?', $prop );
				$types[] = $fields[ $prop ];
				$values[] = $value;
			}
		}
		$types = implode( $types );
		if ( empty( $wh ) )
			$where = '';
		else
			$where = ' WHERE ' . implode( ' AND ', $wh );
		// ORDER BY
		if ( empty( $orderby ) )
			$orderby = [ $id => 'ASC' ];
		$order = [];
		foreach ( $orderby as $prop => $mode ) {
			if ( !array_key_exists( $prop, $fields ) )
				continue;
			if ( !in_array( $mode, [ 'ASC', 'DESC' ] ) )
				continue;
			$order[] = sprintf( '`%s` %s', $prop, $mode );
		}
		if ( empty( $order ) )
			$orderby = '';
		else
			$orderby = ' ORDER BY ' . implode( ', ', $order );
		// LIMIT
		if ( is_null( $limit ) )
			$limit = '';
		else
			$limit = sprintf( ' LIMIT %d', $limit );
		// QUERY
		$sql = sprintf( 'SELECT %s FROM `xa_%s`', $props, $class ) . $where . $orderby . $limit;
		$stmt = $db->prepare( $sql );
		if ( !empty( $values ) )
			$stmt->bind_param( $types, ...$values );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( $class ) ) )
			$items[ $item->$id ] = $item;
		$rslt->free();
		return $items;
	}

	public function select_by( string $prop, $value ) {
		$items = self::select( [ $prop => $value ], [], 1 );
		return array_shift( $items );
	}

	public function insert() {
		global $db;
		$class = get_called_class();
		$fields = $class::FIELDS;
		$props = [];
		$marks = [];
		$types = [];
		$values = [];
		$id = NULL;
		foreach ( $fields as $prop => $type ) {
			if ( is_null( $id ) ) {
				$id = $prop;
				continue;
			}
			$props[] = sprintf( '`%s`', $prop );
			$marks[] = '?';
			$types[] = $type;
			$values[] = $this->$prop;
		}
		$props = implode( ', ', $props );
		$marks = implode( ', ', $marks );
		$types = implode( $types );
		$sql = sprintf( 'INSERT INTO `xa_%s` ( %s ) VALUES ( %s )', $class, $props, $marks );
		$stmt = $db->prepare( $sql );
		$stmt->bind_param( $types, ...$values );
		$stmt->execute();
		$this->$id = $stmt->insert_id;
		$stmt->close();
	}

	public function update() {
		global $db;
		$class = get_called_class();
		$fields = $class::FIELDS;
		$set = [];
		$types = [];
		$values = [];
		$id = NULL;
		foreach ( $fields as $prop => $type ) {
			if ( is_null( $id ) ) {
				$id = $prop;
				continue;
			}
			$set[] = sprintf( '`%s` = ?', $prop );
			$types[] = $type;
			$values[] = $this->$prop;
		}
		$types[] = $fields[ $id ];
		$values[] = $this->$id;
		$set = implode( ', ', $set );
		$types = implode( $types );
		$sql = sprintf( 'UPDATE `xa_%s` SET %s WHERE `%s` = ?', $class, $set, $id );
		$stmt = $db->prepare( $sql );
		$stmt->bind_param( $types, ...$values );
		$stmt->execute();
		$stmt->close();
	}

	public function delete() {
		global $db;
		$class = get_called_class();
		$fields = $class::FIELDS;
		$id = NULL;
		foreach ( $fields as $prop => $type ) {
			if ( is_null( $id ) ) {
				$id = $prop;
				break;
			}
		}
		$sql = sprintf( 'DELETE FROM `xa_%s` WHERE `%s` = ?', $class, $id );
		$stmt = $db->prepare( $sql );
		$stmt->bind_param( $fields[ $id ], $this->$id );
		$stmt->execute();
		$stmt->close();
	}

	public static function request( string $key = '', bool $nullable = FALSE ) {
		$class = get_called_class();
		$fields = $class::FIELDS;
		$id = NULL;
		foreach ( $fields as $prop => $type ) {
			if ( is_null( $id ) ) {
				$id = $prop;
				break;
			}
		}
		if ( $key === '' )
			$key = $id;
		$var = request_int( $key, $nullable );
		if ( is_null( $var ) )
			return NULL;
		$var = $class::select_by( $id, $var );
		if ( !is_null( $var ) )
			return $var;
		if ( $nullable )
			return NULL;
		failure( 'argument_not_valid', $key );
	}

	/* meta */

	public function get_meta( string $key ) {
		if ( is_null( $this->meta ) )
			return NULL;
		$meta = unserialize( $this->meta );
		if ( !array_key_exists( $key, $meta ) )
			return NULL;
		return $meta[ $key ];
	}

	public function set_meta( string $key, $value = NULL ) {
		if ( is_null( $this->meta ) )
			$meta = [];
		else
			$meta = unserialize( $this->meta );
		if ( is_null( $value ) ) {
			if ( array_key_exists( $key, $meta ) )
				unset( $meta[ $key ] );
			if ( empty( $meta ) )
				$this->meta = NULL;
		} else {
			$meta[ $key ] = $value;
			$this->meta = serialize( $meta );
		}
	}
}