<?php

# TODO rename `role_id` to `role` and decrease by 1
# TODO rename `reg_time` to `reg_tm`

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
		'role_id'       => 'i',
		'reg_time'      => 's',
		'reg_ip'        => 's',
	];

	public $user_id;       # integer, primary key
	public $email_address; # varchar
	public $password_hash; # varchar, nullable
	public $last_name;     # varchar, nullable
	public $first_name;    # varchar, nullable
	public $role_id;       # integer, default 1
	public $reg_time;      # timestamp
	public $reg_ip;        # varchar

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

	public function select_index_teams( int $season_id ): array {
		global $db;
		# TODO on_sunday move
		$stmt = $db->prepare( '
SELECT `xa_location`.`location_id`, `xa_location`.`location_name`, `xa_location`.`is_swarm`, `xa_team`.`on_sunday`, `xa_team`.`team_id`, `xa_team`.`team_name`, `xa_team`.`season_id`, `xa_grade`.`grade_id`, `xa_grade`.`grade_name`
FROM `xa_access`
JOIN `xa_team` ON `xa_team`.`team_id` = `xa_access`.`team_id`
JOIN `xa_location` ON `xa_location`.`location_id` = `xa_team`.`location_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
JOIN `xa_grade` ON `xa_grade`.`grade_id` = `xa_target`.`grade_id`
WHERE `xa_access`.`user_id` = ? AND `xa_team`.`season_id` = ?
ORDER BY `xa_location`.`is_swarm` DESC, `xa_location`.`location_name` ASC, `xa_grade`.`grade_id` ASC;
		' );
		$stmt->bind_param( 'ii', $this->user_id, $season_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$items = [];
		while ( !is_null( $item = $rslt->fetch_object( 'team' ) ) )
			$items[] = $item;
		$rslt->free();
		return $items;
	}

	public function has_team( int $team_id ): bool {
		global $db;
		$stmt = $db->prepare( 'SELECT `user_id`, `team_id` FROM `xa_access` WHERE `user_id` = ? AND `team_id` = ?;' );
		$stmt->bind_param( 'ii', $this->user_id, $team_id );
		$stmt->execute();
		$rslt = $stmt->get_result();
		$stmt->close();
		$value = $rslt->num_rows > 0;
		$rslt->free();
		return $value;
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
}