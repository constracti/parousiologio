<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Χρήστες' );

page_style_add( SITE_URL . 'css/table.css' );

page_script_add( SITE_URL . 'js/table.js' );

page_nav_add( 'bar_link', [
	'href' => SITE_URL . 'users.php',
	'text' => 'χρήστες',
	'icon' => 'fa-user',
	'hide_medium' => FALSE,
] );

$table = new table( [
	'full_screen' => TRUE,
] );
$table->add( 'ονοματεπώνυμο', function( user $user ) {
	echo sprintf( '%s %s', $user->last_name, $user->first_name ) . "\n";
} );
$table->add( 'επικοινωνία', function( user $user ) {
	echo sprintf( '<span class="fa %s"></span>', is_null( $user->password_hash ) ? 'fa-unlock-alt' : 'fa-lock' ) . "\n";
	echo sprintf( '<a href="mailto:%s">%s</a>', $user->email_address, $user->email_address ) . "\n";
	echo '<div>' . "\n";
	if ( !is_null( $user->home_phone ) )
		echo sprintf( '<a href="tel:%s">%s</a>', $user->home_phone, $user->home_phone ) . "\n";
	if ( !is_null( $user->mobile_phone ) )
		echo sprintf( '<a href="tel:%s">%s</a>', $user->mobile_phone, $user->mobile_phone ) . "\n";
	echo '</div>' . "\n";
	# TODO currently we have empty strings, not NULL values
	$address = [];
	if ( !is_null( $user->address ) )
		$address[] = $user->address;
	if ( !is_null( $user->city ) )
		$address[] = $user->city;
	if ( !is_null( $user->postal_code ) )
		$address[] = $user->postal_code;
	if ( !empty( $address ) )
		echo sprintf( '<address>%s</address>', implode( ', ', $address ) ) . "\n";
} );
$table->add( 'δικαιώματα', function( user $user ) {
	echo user::ROLES[ $user->role_id ] . "\n";
} );
$table->add( 'εγγραφή', function( user $user ) {
	$dt = dtime::from_sql( $user->reg_time );
	echo sprintf( '<time datetime="%s" style="white-space: nowrap;">%s</time>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ) ) . "\n";
	# TODO reg_ip NULL
	if ( $user->reg_ip !== '0.0.0.0' )
		echo sprintf( '<span style="white-space: nowrap;">από %s</span>', $user->reg_ip ) . "\n";
} );
$table->add( 'τελευταία είσοδος', function( user $user ) {
	echo '<span style="white-space: nowrap;">';
	if ( is_null( $user->active_time ) ) {
		echo 'ποτέ';
	} else {
		$dt = new dtime( $user->active_time );
		$diff = $dt->human_diff();
		if ( $diff !== '' )
			echo sprintf( '%s πριν', $dt->human_diff() );
		else
			echo 'τώρα';
	}
	echo '</span>' . "\n";
} );
$table->add( 'ενέργειες', function( user $user ) {
	$href = SITE_URL . sprintf( 'user-update.php?user_id=%d', $user->user_id );
	echo sprintf( '<a href="%s" class="link">', $href ) . "\n";
	echo '<span class="fa fa-pencil"></span>' . "\n";
	echo '<span>επεξεργασία</span>' . "\n";
	echo '</a>' . "\n";
	$href = SITE_URL . sprintf( 'user-teams.php?user_id=%d', $user->user_id );
	echo sprintf( '<a href="%s" class="link">', $href ) . "\n";
	echo '<span class="fa fa-users"></span>' . "\n";
	echo '<span>ομάδες</span>' . "\n";
	echo '</a>' . "\n";
	$href = SITE_URL . sprintf( 'user-delete.php?user_id=%d', $user->user_id );
	echo sprintf( '<a href="%s" class="link link-delete link-ajax" data-confirm="οριστική διαγραφή;" data-remove="tr">', $href ) . "\n";
	echo '<span class="fa fa-trash"></span>' . "\n";
	echo '<span>διαγραφή</span>' . "\n";
	echo '</a>' . "\n";
} );
page_body_add( [ $table, 'html' ], user::select( [], [
	'last_name' => 'ASC',
	'first_name' => 'ASC',
	'user_id' => 'ASC',
] ) );

page_html();