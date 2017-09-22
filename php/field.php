<?php

class field {

	public $name;
	public $atts;

	public function __construct( string $name, array $atts = [] ) {
		$this->name = $name;
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'text';
		if ( !array_key_exists( 'required', $atts ) )
			$atts['required'] = FALSE;
		elseif ( !array_key_exists( 'value', $atts ) )
			$atts['value'] = NULL;
		$this->atts = $atts;
	}

	public function post() {
		$var = request_var( $this->name, !$this->atts['required'] );
		if ( is_null( $var ) )
			return NULL;
		switch ( $this->atts['type'] ) {
			case 'text':
				$var = filter_text( $var );
				if ( is_null( $var ) && $this->atts['required'] )
					failure( 'argument_not_defined', $this->name );
				break;
			case 'email':
				$var = filter_email( $var );
				if ( is_null( $var ) )
					failure( 'argument_not_valid', $this->name );
				break;
			case 'number':
				$var = filter_int( $var );
				if ( is_null( $var ) )
					failure( 'argument_not_valid', $this->name );
				if ( array_key_exists( 'min', $this->atts ) && $this->atts['min'] > $var )
					failure( 'argument_not_valid', $this->name );
				if ( array_key_exists( 'max', $this->atts ) && $this->atts['max'] < $var )
					failure( 'argument_not_valid', $this->name );
				break;
		}
		if ( is_null( $var ) )
			return NULL;
		if ( array_key_exists( 'maxlength', $this->atts ) && mb_strlen( $var ) > $this->atts['maxlength'] )
			failure( 'argument_not_valid', $this->name );
		if ( array_key_exists( 'pattern', $this->atts ) ) {
			$var = filter_regexp( $var, $this->atts['pattern'] );
			if ( is_null( $var ) )
				failure( 'argument_not_valid', $this->name );
		}
		return $var;
	}

	public function attributes() {
		echo sprintf( ' name="%s"', $this->name );
		foreach ( $this->atts as $att => $val ) {
			if ( is_bool( $val ) ) {
				if ( !$val )
					continue;
				$val = $att;
			}
			if ( is_null( $val ) )
				continue;
			echo sprintf( ' %s="%s"', $att, $val );
		}
	}

	public function element() {
		echo '<input class="w3-input"';
		$this->attributes();
		echo ' />' . "\n";
	}

	public function html() {
		echo '<label>' . "\n";
		echo sprintf( '<span>%s</span>', $this->atts['placeholder'] ) . "\n";
		if ( $this->atts['required'] )
			echo '<span>*</span>' . "\n";
		$this->element();
		echo '</label>' . "\n";
	}
}

class field_email extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'email';
		parent::__construct( $name, $atts );
	}
}

class field_password extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'password';
		parent::__construct( $name, $atts );
	}
}

class field_year extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'number';
		if ( !array_key_exists( 'min', $atts ) )
			$atts['min'] = 1900;
		if ( !array_key_exists( 'pattern', $atts ) )
			$atts['max'] = 2099;
		parent::__construct( $name, $atts );
	}
}

class field_phone extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'tel';
		if ( !array_key_exists( 'maxlength', $atts ) )
			$atts['maxlength'] = 10;
		if ( !array_key_exists( 'pattern', $atts ) )
			$atts['pattern'] = '[0-9]{10}';
		parent::__construct( $name, $atts );
	}
}

class field_pc extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'text';
		if ( !array_key_exists( 'maxlength', $atts ) )
			$atts['maxlength'] = 5;
		if ( !array_key_exists( 'pattern', $atts ) )
			$atts['pattern'] = '[0-9]{5}';
		parent::__construct( $name, $atts );
	}
}

class field_select extends field {

	public $options;

	public function __construct( string $name, array $options, array $atts = [] ) {
		$this->options = $options;
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = NULL;
		parent::__construct( $name, $atts );
	}

	public function post() {
		$var = request_var( $this->name, !$this->atts['required'] );
		if ( is_null( $var ) )
			return NULL;
		foreach ( $this->options as $value => $label )
			if ( $var === strval( $value ) )
				return $value;
		failure( 'argument_not_valid', $this->name );
	}

	public function element() {
		# TODO optgroup
		echo '<select class="w3-select"';
		$this->attributes();
		echo '>' . "\n";
		echo '<option></option>' . "\n";
		foreach ( $this->options as $value => $label )
			echo sprintf( '<option value="%s"%s>%s</option>', $value, $value === $this->atts['value'] ? ' selected="selected"' : '', $label ) . "\n";
		echo '</select>' . "\n";
	}
}

/*
class field_checkbox extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'checkbox';
		$atts['checked'] = array_key_exists( 'value', $atts ) && $atts['value'];
		$atts['value'] = 'on';
		parent::__construct( $name, $atts );
	}

	public function post() {
		if ( request_bool( $this->name ) )
			return TRUE;
		if ( $this->atts['required'] )
			failure( 'argument_not_defined', $this->name );
		return FALSE;
	}

	public function element() {
		echo '<input class="w3-check"';
		$this->attributes();
		echo ' />' . "\n";
	}
}
*/

class field_radio extends field {

	public $options;

	public function __construct( string $name, array $options, array $atts = [] ) {
		$this->options = $options;
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'radio';
		parent::__construct( $name, $atts );
	}

	public function post() {
		$var = request_var( $this->name, !$this->atts['required'] );
		if ( is_null( $var ) )
			return NULL;
		foreach ( $this->options as $value => $label )
			if ( $var === strval( $value ) )
				return $value;
		failure( 'argument_not_valid', $this->name );
	}

	public function element() {
		echo '<input class="w3-radio"';
		$this->attributes();
		echo ' />' . "\n";
	}

	public function html() {
		$val = $this->atts['value'];
		foreach ( $this->options as $value => $label ) {
			$this->atts['value'] = $value;
			$this->atts['checked'] = $value === $val;
			echo '<label>' . "\n";
			$this->element();
			echo sprintf( '<span>%s</span>', $label ) . "\n";
			echo '</label>' . "\n";
		}
		$this->atts['value'] = $val;
		unset( $this->atts['checked'] );
	}
}