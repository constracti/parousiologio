<?php

class user extends entity {

	const ROLE_UNVER = 1;
	const ROLE_GUEST = 2;
	const ROLE_BASIC = 3;
	const ROLE_OBSER = 4;
	const ROLE_ADMIN = 5;
	const ROLE_SUPER = 6;

	const ROLES = [
		user::ROLE_UNVER => 'εγγεγραμμένος',
		user::ROLE_GUEST => 'επισκέπτης',
		user::ROLE_BASIC => 'βασικός',
		user::ROLE_OBSER => 'παρατηρητής',
		user::ROLE_ADMIN => 'διαχειριστής',
		user::ROLE_SUPER => 'ιδρυτής',
	];

	const FIELDS = [
		'user_id'       => 'i',
		'email_address' => 's',
		'password_hash' => 's',
		'last_name'     => 's',
		'first_name'    => 's',
		'home_phone'    => 's',
		'mobile_phone'  => 's',
		'occupation'    => 's',
		'first_year'    => 's',
		'address'       => 's',
		'city'          => 's',
		'postal_code'   => 's',
		'role_id'       => 'i',
		'reg_time'      => 's',
		'reg_ip'        => 's',
		'active_time'   => 's',
		'active_ip'     => 's',
		'meta'          => 's',
	];

	public $user_id;       # integer, primary key
	public $email_address; # varchar
	public $password_hash; # varchar, nullable
	public $last_name;     # varchar, nullable
	public $first_name;    # varchar, nullable
	public $home_phone;    # varchar, nullable
	public $mobile_phone;  # varchar, nullable
	public $occupation;    # varchar, nullable
	public $first_year;    # integer, nullable
	public $address;       # varchar, nullable
	public $city;          # varchar, nullable
	public $postal_code;   # varchar, nullable
	public $role_id;       # integer
	public $reg_time;      # timestamp, nullable
	public $reg_ip;        # varchar, nullable
	public $active_time;   # timestamp, nullable
	public $active_ip;     # varchar, nullable
	public $meta;          # varchar, nullable

	const COLS = [
		'last_name'     => 'επώνυμο',
		'first_name'    => 'όνομα',
		'home_phone'    => 'σταθερό τηλέφωνο',
		'mobile_phone'  => 'κινητό τηλέφωνο',
		'email_address' => 'διεύθυνση email',
		'occupation'    => 'απασχόληση',
		'grade_name'    => 'τάξη',
		'first_year'    => 'πρώτο έτος διακονίας',
		'address'       => 'διεύθυνση',
		'city'          => 'πόλη',
		'postal_code'   => 'ταχυδρομικός κώδικας',
	];

	public static function select_by_email_address( string $email_address ) {
		global $db;
		$stmt = $db->prepare( 'SELECT `xa_user`.* FROM `xa_user` WHERE `email_address` = ? AND `role_id` > ? LIMIT 1' );
		$stmt->bind_param( 'si', $email_address, $role_id = user::ROLE_UNVER );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$item = $rslt->fetch_object( 'user' );
		$rslt->free();
		return $item;
	}

	public static function clear_by_email_address( string $email_address ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_user` WHERE `email_address` = ? AND `role_id` = ?' );
		$stmt->bind_param( 'si', $email_address, $role_id = user::ROLE_UNVER );
		$stmt->execute();
		$stmt->close();
		$stmt = $db->prepare( 'DELETE FROM `xa_vlink` WHERE `type` = ? AND `data` = ? AND `act_tm` IS NOT NULL' );
		$stmt->bind_param( 'ss', $type = 'chmail', $email_address );
		$stmt->execute();
		$stmt->close();
	}

	public function has_team( int $team_id ): bool {
		global $db;
		$stmt = $db->prepare( 'SELECT `user_id`, `team_id` FROM `xa_access` WHERE `user_id` = ? AND `team_id` = ?' );
		$stmt->bind_param( 'ii', $this->user_id, $team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$value = $rslt->num_rows > 0;
		$rslt->free();
		return $value;
	}

	public function accesses( int $team_id ): bool {
		return $this->role_id >= user::ROLE_OBSER || $this->has_team( $team_id );
	}

	public function inform() {
		require_once SITE_DIR . 'php/mailer.php';
		$mail = new mailer();
		$mail->addAddress( MAIL_USER );
		$mail->addReplyTo( $user->email_address );
		$mail->Subject = sprintf( '%s - %s', SITE_NAME, 'εγγραφή' );
		$mail->msgHTML( implode( mailer::NEWLINE, [
			sprintf( '<p>Ο χρήστης με διεύθυνση email <i>%s</i> ολοκλήρωσε την εγγραφή του στο Παρουσιολόγιο.</p>', $this->email_address ),
		] ) );
		$mail->send();
	}

	/* teams */

	public function check_teams(): array {
		global $db;
		global $cseason;
		$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_location`.`location_name`, `xa_location`.`is_swarm`,
`xa_team`.`team_id`, `xa_team`.`team_name`, `xa_team`.`season_id`, `xa_team`.`on_sunday`,
`xa_access`.`team_id` IS NOT NULL AS `check`
FROM `xa_location`
LEFT JOIN `xa_team` ON `xa_location`.`location_id` = `xa_team`.`location_id` AND `xa_team`.`season_id` = ?
LEFT JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
LEFT JOIN `xa_access` ON `xa_team`.`team_id` = `xa_access`.`team_id` AND `xa_access`.`user_id` = ?
GROUP BY `xa_location`.`location_id`, `xa_team`.`team_id`
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_location`.`location_id` ASC,
MIN( `xa_target`.`grade_id` ) ASC
		' );
		$stmt->bind_param( 'ii', $cseason->season_id, $this->user_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$teams = [];
		while ( !is_null( $team = $rslt->fetch_object( 'team' ) ) )
			$teams[] = $team;
		$rslt->free();
		return $teams;
	}

	public function insert_team( int $team_id ) {
		global $db;
		$stmt = $db->prepare( 'INSERT INTO `xa_access` ( `user_id`, `team_id` ) VALUES ( ?, ? )' );
		$stmt->bind_param( 'ii', $this->user_id, $team_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_team( int $team_id ) {
		global $db;
		$stmt = $db->prepare( 'DELETE FROM `xa_access` WHERE `user_id` = ? AND `team_id` = ?' );
		$stmt->bind_param( 'ii', $this->user_id, $team_id );
		$stmt->execute();
		$stmt->close();
	}

	public function insert_teams( int $location_id ) {
		global $db;
		global $cseason;
		$stmt = $db->prepare( '
REPLACE INTO `xa_access` ( `user_id`, `team_id` )
SELECT ?, `xa_team`.`team_id`
FROM `xa_team`
WHERE `xa_team`.`location_id` = ? AND `xa_team`.`season_id` = ?
		' );
		$stmt->bind_param( 'iii', $this->user_id, $location_id, $cseason->season_id );
		$stmt->execute();
		$stmt->close();
	}

	public function delete_teams() {
		global $db;
		global $cseason;
		$stmt = $db->prepare( '
DELETE FROM `xa_access`
WHERE `user_id` = ? AND `team_id` IN (
	SELECT `xa_team`.`team_id`
	FROM `xa_team`
	WHERE `xa_team`.`season_id` = ?
)
		' );
		$stmt->bind_param( 'ii', $this->user_id, $cseason->season_id );
		$stmt->execute();
		$stmt->close();
	}
}