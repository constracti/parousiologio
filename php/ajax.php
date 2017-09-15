<?php

require_once 'core.php';

function success( array $array = [] ) {
	header( 'content-type: application/json' );
	exit( json_encode( $array ) );
}

function failure() {
	exit;
}