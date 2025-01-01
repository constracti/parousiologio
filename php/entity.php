<?php

abstract class entity {

	private static function wherec( array $struct ): array {
		if ( // equality of key and value
				count( array_filter( array_keys( $struct ), function( $key ): bool {
					return !is_string( $key ) || !array_key_exists( $key, static::FIELDS );
				} ) ) === 0
				) {
			$struct = array_map( function( string $key, $value ): array {
				return [ '=', $key, $value ];
			}, array_keys( $struct ), array_values( $struct ) );
			array_unshift( $struct, 'AND' );
		}
		$c = count( $struct );
		if (
				$c > 1
				&&
				is_string( $struct[0] )
				&&
				in_array( $struct[0] = mb_strtoupper( $struct[0] ), [ 'AND', 'OR' ], TRUE )
				) {
			// and & or
			$where = [];
			$type_list = [];
			$value_list = [];
			for ( $i = 1; $i < $c; $i++ )
				list( $where[], $type_list[], $value_list[] ) = static::wherec( $struct[$i] );
			$where = '(' . implode( ' ' . $struct[0] . ' ', $where ) . ')';
			$type_list = array_merge( ...$type_list );
			$value_list = array_merge( ...$value_list );
		} elseif (
				$c === 3
				&&
				is_string( $struct[0] )
				&&
				in_array( $struct[0] = mb_strtoupper( $struct[0] ), [ '=', 'LIKE' ], TRUE )
				&&
				is_string( $struct[1] )
				&&
				array_key_exists( $struct[1], static::FIELDS )
				) {
			// leaf-2
			$where = '(`' . $struct[1] . '` ' . $struct[0] . ' ?)';
			$type = static::FIELDS[$struct[1]];
			$value = $struct[2];
			$type_list = [ $type, ];
			$value_list = [ $value, ];
		} else {
			// error
			exit( 'wherec: struct not valid' );
		}
		return [ $where, $type_list, $value_list, ];
	}

	public static function select( ?array $wherec = NULL, array $orderby = [], $limit = NULL ): array {
		global $db;
		$class = get_called_class();
		$fields = $class::FIELDS;
		$id = array_key_first( $fields );
		// SELECT
		$props = array_map( function( string $field ): string {
			return sprintf( '`%s`', $field );
		}, array_keys( $fields ) );
		$select = sprintf( 'SELECT %s FROM `xa_%s`', implode( ', ', $props ), $class );
		// WHERE
		if ( $wherec === [] )
			$wherec = NULL;
		if ( !is_null( $wherec ) ) {
			list( $where, $type_list, $value_list ) = static::wherec( $wherec );
			$where = ' WHERE ' . $where;
		} else {
			$where = '';
		}
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
		elseif ( is_array( $limit ) )
			$limit = sprintf( ' LIMIT %d, %d', $limit[0], $limit[1] );
		else
			$limit = sprintf( ' LIMIT %d', $limit );
		// QUERY
		$sql = $select . $where . $orderby . $limit;
		$stmt = $db->prepare( $sql );
		if ( !is_null( $wherec ) )
			$stmt->bind_param( implode( $type_list ), ...$value_list );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( $class ) ) )
			$items[ $item->$id ] = $item;
		$rslt->free();
		return $items;
	}

	public static function select_by( string $prop, $value ) {
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

	public static function count( ?array $wherec = NULL ): int {
		global $db;
		$class = static::NAME;
		$fields = static::FIELDS;
		$id = array_key_first( $fields );
		// SELECT
		$select = sprintf( 'SELECT COUNT(`%s`) AS `count` FROM `xa_%s`', $id, $class );
		// WHERE
		if ( !is_null( $wherec ) ) {
			list( $where, $type_list, $value_list ) = static::wherec( $wherec );
			$where = ' WHERE ' . $where;
		} else {
			$where = '';
		}
		// QUERY
		$sql = $select . $where;
		$stmt = $db->prepare( $sql );
		if ( !is_null( $wherec ) )
			$stmt->bind_param( implode( $type_list ), ...$value_list );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$item = $rslt->fetch_object();
		$rslt->free();
		return $item->count;
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
		if ( !is_null( $value ) )
			$meta[ $key ] = $value;
		elseif ( array_key_exists( $key, $meta ) )
			unset( $meta[ $key ] );
		if ( empty( $meta ) )
			$this->meta = NULL;
		else
			$this->meta = serialize( $meta );
	}
}
