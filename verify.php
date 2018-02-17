<?php

require_once 'php/core.php';

page_title_set( 'Επαλήθευση' );

( function() {
	$vlink = vlink::request();
	$code = request_var( 'code' );
	if ( !password_verify( $code, $vlink->hash ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης δεν είναι σωστός.', 'error' );
	if ( !is_null( $vlink->act_tm ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης έχει ακολουθηθεί.', 'error' );
	if ( $_SERVER['REQUEST_TIME'] > dtime::sql2php( $vlink->exp_tm ) )
		return page_message_add( 'Ο σύνδεσμος επαλήθευσης έχει λήξει.', 'error' );
	$user = user::select_by( 'user_id', $vlink->user_id );
	$vlink->read();
	switch ( $vlink->type ) {
		case 'register':
			$user->role = user::ROLE_GUEST;
			$user->update();
			$user->inform();
			page_message_add( 'Η εγγραφή σου ολοκληρώθηκε επιτυχώς. Μπορείς πλέον να συνδεθείς.', 'success' );
			user::clear_by_email_address( $user->email_address );
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
			user::clear_by_email_address( $user->email_address );
			break;
	}
} )();

page_message_add( sprintf( 'Μετάβαση στην <a href="%s">αρχική σελίδα</a>.', site_href() ), 'info' );

page_html();
