<?php

class child extends entity {

	const FIELDS = [
		'child_id'   => 'i',
		'last_name'  => 's',
		'first_name' => 's',
		'school'     => 's',
		'city'       => 's',
	];

	public $child_id;   # integer, primary key
	public $last_name;  # varchar, nullable
	public $first_name; # varchar, nullable
	public $school;     # varchar, nullable
	public $city;       # varchar, nullable

	public function insert_event( int $event_id ) {
		global $db;
		$stmt = $db->prepare( 'INSERT INTO `xa_presence` ( `child_id`, `event_id` ) VALUES ( ?, ? )' );
		$stmt->bind_param( 'ii', $this->child_id, $event_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_event( int $event_id ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_presence` WHERE `child_id` = ? AND `event_id` = ?' );
		$stmt->bind_param( 'ii', $this->child_id, $event_id );
		$stmt->execute();
		$stmt->close();
	}
}