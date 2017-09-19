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
		if ( !array_key_exists( 'autocomplete', $atts ) )
			$atts['autocomplete'] = FALSE;
		elseif ( !array_key_exists( 'value', $atts ) )
			$atts['value'] = NULL;
		$this->atts = $atts;
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' )
			$this->atts['value'] = $this->post();
	}

	public function post() {
		global $field_success;
		if ( !array_key_exists( $this->name, $_POST ) ) {
			page_message_add( sprintf( 'Το πεδίο "%s" δεν είναι ορισμένο', $this->atts['placeholder'] ), 'error' );
			$field_success = FALSE;
			return NULL;
		}
		$var = $_POST[ $this->name ];
		if ( $var === '' ) {
			if ( $this->atts['required'] ) {
				page_message_add( sprintf( 'Το πεδίο "%s" είναι κενό.', $this->atts['placeholder'] ), 'error' );
				$field_success = FALSE;
			}
			return NULL;
		}
		switch ( $this->atts['type'] ) {
			case 'text':
				$var = strip_tags( $var );
				$var = preg_replace( '/\s+/', ' ', $var );
				$var = trim( $var );
				if ( $var === '' ) {
					if ( $this->atts['required'] ) {
						page_message_add( sprintf( 'Το πεδίο "%s" είναι κενό.', $this->atts['placeholder'] ), 'error' );
						$field_success = FALSE;
					}
					return NULL;
				}
				break;
			case 'email':
				$var = filter_var( $var, FILTER_VALIDATE_EMAIL );
				if ( $var === FALSE ) {
					page_message_add( sprintf( 'Το πεδίο "%s" δεν έχει έγκυρη μορφή διεύθυνσης email.', $this->atts['placeholder'] ), 'error' );
					$field_success = FALSE;
					return NULL;
				}
				break;
			case 'number':
				$options = [];
				if ( array_key_exists( 'min', $this->atts ) )
					$options['min_range'] = $this->atts['min'];
				if ( array_key_exists( 'max', $this->atts ) )
					$options['max_range'] = $this->atts['max'];
				$options = [
					'options' => $options,
				];
				$var = filter_var( $var, FILTER_VALIDATE_INT, $options );
				if ( $var === FALSE ) {
					page_message_add( sprintf( 'Το πεδίο "%s" δεν έχει έγκυρη μορφή αριθμού.', $this->atts['placeholder'] ), 'error' );
					$field_success = FALSE;
					return NULL;
				}
				break;
		}
		if ( array_key_exists( 'maxlength', $this->atts ) ) {
			if ( mb_strlen( $var ) > $this->atts['maxlength'] ) {
				page_message_add( sprintf( 'Το πεδίο "%s" υπερβαίνει τους %d χαρακτήρες.', $this->atts['placeholder'], $this->atts['maxlength'] ), 'error' );
				$field_success = FALSE;
				return NULL;
			}
		}
		if ( array_key_exists( 'pattern', $this->atts ) ) {
			$var = filter_var( $var, FILTER_VALIDATE_REGEXP, [
				'options' => [
					'regexp' => '/^' . $this->atts['pattern'] . '$/',
				],
			] );
			if ( $var === FALSE ) {
				page_message_add( sprintf( 'Το πεδίο "%s" δεν έχει έγκυρη μορφή.', $this->atts['placeholder'] ), 'error' );
				$field_success = FALSE;
				return NULL;
			}
		}
		return $var;
	}

	public function attributes() {
		echo sprintf( ' name="%s"', $this->name );
		foreach ( $this->atts as $att => $val ) {
			if ( $att === 'value' ) {
				if ( $this->atts['type'] === 'password' )
					continue;
			}
			if ( $att === 'autocomplete' ) {
				$val = $val ? 'on' : 'off';
			}
			if ( is_bool( $val ) ) {
				if ( $val )
					$val = $att;
				else
					continue;
			}
			if ( is_null( $val ) ) {
				continue;
			}
			echo sprintf( ' %s="%s"', $att, $val );
		}
	}

	public function element() {
		echo '<input class="w3-input"';
		$this->attributes();
		echo ' />' . "\n";
	}

	public function html() {
		echo '<label>';
		echo sprintf( '<span>%s</span>', $this->atts['placeholder'] );
		if ( $this->atts['required'] )
			echo '<span>*</span>';
		echo '<br />' . "\n";
		$this->element();
		echo '</label>' . "\n";
	}

	public function value() {
		return $this->atts['value'];
	}
}

class field_email extends field {

	public function __construct( string $name, array $atts = [] ) {
		if ( !array_key_exists( 'type', $atts ) )
			$atts['type'] = 'email';
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
		global $field_success;
		$var = parent::post();
		if ( is_null( $var ) )
			return NULL;
		foreach ( $this->options as $value => $label )
			if ( $var === strval( $value ) )
				return $value;
		page_message_add( sprintf( 'Το πεδίο "%s" δεν έχει έγκυρη τιμή.', $this->atts['placeholder'] ), 'error' );
		$field_success = FALSE;
		return NULL;
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