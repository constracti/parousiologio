<?php

class epoint extends entity {

	const SHORT = 3600; # one hour
	const LONG = 7776000; # three months

	const DELIMITER = '_';

	const FIELDS = [
		'epoint_id' => 'i',
		'user_id'   => 'i',
		'hash'      => 's',
		'ins_tm'    => 's',
		'ins_ip'    => 's',
		'ins_ag'    => 's',
		'exp_tm'    => 's',
		'logins'    => 'i',
	];

	public $epoint_id; # integer, primary key
	public $user_id;   # integer
	public $hash;      # varchar
	public $ins_tm;    # timestamp
	public $ins_ip;    # varchar
	public $ins_ag;    # varchar
	public $exp_tm;    # timestamp, nullable
	public $logins;    # integer, default 0

	public static function write( int $user_id ) {
		$epoint = new self();
		$epoint->user_id = $user_id;
		$code = bin2hex( random_bytes( 32 ) );
		$epoint->hash = password_hash( $code, PASSWORD_DEFAULT );
		$ins_tm = $_SERVER['REQUEST_TIME'];
		$epoint->ins_tm = dtime::php2sql( $ins_tm );
		$epoint->ins_ip = $_SERVER['REMOTE_ADDR'];
		$epoint->ins_ag = $_SERVER['HTTP_USER_AGENT'];
		$exp_tm = $ins_tm + self::LONG;
		$epoint->exp_tm = dtime::php2sql( $exp_tm );
		$epoint->logins = 0;
		$epoint->insert();
		setcookie( 'epoint', $epoint->epoint_id . self::DELIMITER . $code, $exp_tm );
		header( 'location: ' . HOME_URL );
		exit;
	}

	public static function read() {
		if ( !array_key_exists( 'epoint', $_COOKIE ) )
			return NULL;
		$epoint = explode( self::DELIMITER, $_COOKIE['epoint'], 2 );
		if ( count( $epoint ) !== 2 ) {
			setcookie( 'epoint' );
			return NULL;
		}
		$epoint_id = filter_var( $epoint[0], FILTER_VALIDATE_INT );
		if ( $epoint_id === FALSE ) {
			setcookie( 'epoint' );
			return NULL;
		}
		$code = $epoint[1];
		$epoint = self::select_by( 'epoint_id', $epoint_id );
		if ( is_null( $epoint ) ) {
			setcookie( 'epoint' );
			return NULL;
		}
		if ( !password_verify( $code, $epoint->hash ) ) {
			setcookie( 'epoint' );
			return NULL;
		}
		if ( !is_null( $epoint->exp_tm ) && $_SERVER['REQUEST_TIME'] > dtime::sql2php( $epoint->exp_tm ) ) {
			return NULL;
		}
		$epoint->logins++;
		if ( !is_null( $epoint->exp_tm ) ) {
			$exp_tm = $_SERVER['REQUEST_TIME'] + self::LONG;
			$epoint->exp_tm = dtime::php2sql( $exp_tm );
			setcookie( 'epoint', $_COOKIE['epoint'], $exp_tm );
		}
		$epoint->update();
		return $epoint;
	}

	public function clear() {
		$this->delete();
		setcookie( 'epoint' );
	}
}