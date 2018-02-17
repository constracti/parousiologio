<?php

function success( array $array = [] ) {
	header( 'content-type: application/json' );
	exit( json_encode( $array ) );
}

function failure( string $error = '', ...$args ) {
	global $errors;
	if ( array_key_exists( $error, $errors ) )
		$html = sprintf( $errors[ $error ], ...$args );
	else
		$html = $error;
	# TODO multibyte string replace
	exit( strip_tags( str_replace( '<br />', "\n", $html ) ) );
}
