<?php

require_once 'php/page.php';

page_title_set( 'Επαλήθευση' );

( function() {
	if ( !array_key_exists( 'vlink_id', $_GET ) || !array_key_exists( 'code', $_GET ) ) {
		header( 'location: ' . HOME_URL );
		exit;
	}
	$vlink_id = filter_var( $_GET['vlink_id'], FILTER_VALIDATE_INT );
	if ( $vlink_id === FALSE ) {
		header( 'location: ' . HOME_URL );
		exit;
	}
	$vlink = vlink::select_by( 'vlink_id', $vlink_id );
	if ( is_null( $vlink ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης δε βρέθηκε.', 'error' );
	if ( !password_verify( $_GET['code'], $vlink->hash ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης δεν είναι σωστός.', 'error' );
	if ( !is_null( $vlink->act_tm ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης έχει ακολουθηθεί.', 'error' );
	if ( $_SERVER['REQUEST_TIME'] > dtime::sql2php( $vlink->exp_tm ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης έχει λήξει.', 'error' );
	$user = user::select_by( 'user_id', $vlink->user_id );
	$vlink->read();
	switch ( $vlink->type ) {
		case 'register':
			$user->role_id = user::ROLE_GUEST;
			$user->update();
			page_message_add( 'Η εγγραφή σου ολοκληρώθηκε επιτυχώς. Μπορείς πλέον να συνδεθείς.', 'success' );
			# TODO inform admin
			break;
		case 'repass':
			$user->password_hash = $vlink->data;
			$user->update();
			page_message_add( 'Μπορείς να συνδεθείς με τον καινούριο σου κωδικό.', 'success' );
			break;
		case 'chmail':
			$user->email_address = $vlink->data;
			$user->update();
			page_message_add( 'Η διεύθυνση email του λογαριασμού άλλαξε επιτυχώς.', 'success' );
			break;
	}
} )();

page_message_add( sprintf( 'Μετάβαση στην <a href="%s">αρχική σελίδα</a>.', HOME_URL ), 'info' );

page_html();