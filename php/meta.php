<?php

class meta {

	public $meta_id;
	public $meta_key;
	public $meta_type;
	public $meta_value;

	public function build() {
		if ( is_int( $this->meta_value ) ) {
			$this->meta_type = 'i';
			$this->meta_value = strval( $this->meta_value );
		} elseif ( is_string( $this->meta_value ) ) {
			$this->meta_type = 's';
			$this->meta_value = strval( $this->meta_value );
		} elseif ( is_array( $this->meta_value ) ) {
			$this->meta_type = 'a';
			$this->meta_value = serialize( $this->meta_value );
		} else {
			$this->meta_type = 'n';
			$this->meta_value = '';
		}
	}

	public function parse() {
		switch ( $this->meta_type ) {
			case 'i':
				$this->meta_value = intval( $this->meta_value );
				break;
			case 's':
				$this->meta_value = strval( $this->meta_value );
				break;
			case 'a':
				$this->meta_value = unserialize( $this->meta_value );
				break;
			default:
				$this->meta_vaue = NULL;
				break;
		}
		$this->meta_type = NULL;
	}
}