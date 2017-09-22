<?php

class child extends entity {

	const FIELDS = [
		'child_id'      => 'i',
		'last_name'     => 's',
		'first_name'    => 's',
		'home_phone'    => 's',
		'mobile_phone'  => 's',
		'email_address' => 's',
		'school'        => 's',
		'birth_year'    => 'i',
		'fath_name'     => 's',
		'fath_mobile'   => 's',
		'fath_occup'    => 's',
		'fath_email'    => 's',
		'moth_name'     => 's',
		'moth_mobile'   => 's',
		'moth_occup'    => 's',
		'moth_email'    => 's',
		'address'       => 's',
		'city'          => 's',
		'postal_code'   => 's',
	];

	public $child_id;      # integer, primary key
	public $last_name;     # varchar
	public $first_name;    # varchar
	public $home_phone;    # varchar, nullable
	public $mobile_phone;  # varchar, nullable
	public $email_address; # varchar, nullable
	public $school;        # varchar, nullable
	public $birth_year;    # integer, nullable
	public $fath_name;     # varchar, nullable
	public $fath_mobile;   # varchar, nullable
	public $fath_occup;    # varchar, nullable
	public $fath_email;    # varchar, nullable
	public $moth_name;     # varchar, nullable
	public $moth_mobile;   # varchar, nullable
	public $moth_occup;    # varchar, nullable
	public $moth_email;    # varchar, nullable
	public $address;       # varchar, nullable
	public $city;          # varchar, nullable
	public $postal_code;   # varchar, nullable

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

	public function select_follows(): array {
		global $db;
		$stmt = $db->prepare( '
SELECT `xa_follow`.*
FROM `xa_follow`
JOIN `xa_season` ON `xa_season`.`season_id` = `xa_follow`.`season_id`
WHERE `xa_follow`.`child_id` = ?
ORDER BY `xa_season`.`year` DESC
		' );
		$stmt->bind_param( 'i', $this->child_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'follow' ) ) )
			$items[ $item->follow_id ] = $item;
		$rslt->free();
		return $items;
	}
}