<?php

require_once 'php/page.php';

page_title_set( 'Επαλήθευση' );

( function() {
	$vlink = vlink::request( 'vlink_id' );
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

page_message_add( sprintf( 'Μετάβαση στην <a href="%s">αρχική σελίδα</a>.', SITE_URL ), 'info' );

page_html();