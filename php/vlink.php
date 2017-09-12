<?php

class vlink extends entity {

	const PERIOD = 604800; # seven days

	const FIELDS = [
		'vlink_id' => 'i',
		'user_id'  => 'i',
		'hash'     => 's',
		'type'     => 's',
		'data'     => 's',
		'ins_tm'   => 's',
		'ins_ip'   => 's',
		'ins_ag'   => 's',
		'act_tm'   => 's',
		'act_ip'   => 's',
		'act_ag'   => 's',
		'exp_tm'   => 's',
	];

	public $vlink_id; # integer, primary key
	public $user_id;  # integer
	public $hash;     # varchar
	public $type;     # varchar
	public $data;     # varchar, nullable
	public $ins_tm;   # timestamp
	public $ins_ip;   # varchar
	public $ins_ag;   # varchar
	public $act_tm;   # timestam, nullable
	public $act_ip;   # varchar, nullable
	public $act_ag;   # varchar, nullable
	public $exp_tm;   # timestamp

	public function url(): string {
		return sprintf( '%sverify.php?vlink_id=%d&code=%s', HOME_URL, $this->vlink_id, $this->code );
	}

	public static function write( int $user_id, string $type, $data = NULL ): self {
		$vlink = new self();
		$vlink->user_id = $user_id;
		$vlink->code = bin2hex( random_bytes( 32 ) );
		$vlink->hash = password_hash( $vlink->code, PASSWORD_DEFAULT );
		$vlink->type = $type;
		$vlink->data = $data;
		$ins_tm = $_SERVER['REQUEST_TIME'];
		$vlink->ins_tm = dtime::php2sql( $ins_tm );
		$vlink->ins_ip = $_SERVER['REMOTE_ADDR'];
		$vlink->ins_ag = $_SERVER['HTTP_USER_AGENT'];
		$exp_tm = $ins_tm + self::PERIOD;
		$vlink->exp_tm = dtime::php2sql( $exp_tm );
		$vlink->insert();
		return $vlink;
	}

	public function read() {
		$this->act_tm = dtime::php2sql( $_SERVER['REQUEST_TIME'] );
		$this->act_ip = $_SERVER['REMOTE_ADDR'];
		$this->act_ag = $_SERVER['HTTP_USER_AGENT'];
		$this->update();
	}
}