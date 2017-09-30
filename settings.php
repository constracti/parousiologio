<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

$fields = [];
if ( $cuser->role_id >= user::ROLE_OBSER ) {
	$fields['index'] = new field_checkbox( 'index', [
		'placeholder' => 'προβολή όλων των ομάδων στην αρχική σελίδα',
		'value' => $cuser->get_meta( 'index' ) === 'list',
	] );
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	switch ( request_var( 'action', TRUE ) ) {
		case NULL:
			if ( $cuser->role_id >= user::ROLE_OBSER )
				$cuser->set_meta( 'index', $fields['index']->post() ? 'list' : NULL );
			$cuser->update();
			success( [
				'alert' => 'Οι προτιμήσεις αποθηκεύθηκαν.',
				'location' => site_href( 'settings.php' ),
			] );
		case 'epoint':
			$epoint = epoint::request();
			if ( $epoint->user_id !== $cuser->user_id )
				failure( 'argument_not_valid', 'epoint_id' );
			if ( $epoint->epoint_id === $cepoint->epoint_id )
				failure( 'argument_not_valid', 'epoint_id' );
			$epoint->delete();
			success();
		case 'vlink':
			$vlink = vlink::request();
			if ( $vlink->user_id !== $cuser->user_id )
				failure( 'argument_not_valid', 'vlink_id' );
			$vlink->delete();
			success();
		default:
			failure( 'argument_not_valid', 'action' );
	}
}


/********
 * main *
 ********/

require_once COMPOSER_DIR . 'whichbrowser/vendor/autoload.php';

page_title_set( 'Ρυθμίσεις' );

page_style_add( site_href( 'css/table.css' ) );
page_script_add( site_href( 'js/table.js' ) );

page_nav_add( 'bar_link', [
	'href' => site_href( 'settings.php' ),
	'text' => 'ρυθμίσεις',
	'icon' => 'fa-cog',
	'hide_medium' => FALSE,
] );

page_body_add( function() {
?>
<section class="w3-panel w3-content">
	<h3>στοιχεία λογαριασμού</h3>
	<p><a href="<?= site_href( 'chmail.php' ) ?>">διεύθυνση email</a></p>
	<p><a href="<?= site_href( 'chpass.php' ) ?>">κωδικός πρόσβασης</a></p>
</section>
<?php
} );

if ( !empty( $fields ) )
	page_body_add( 'form_section', $fields, [
		'header' => '<h3>προτιμήσεις</h3>',
	] );


/***********
 * epoints *
 ***********/

$table = new table( [
	'title' => 'σημεία εισόδου',
] );
$table->add( 'εφαρμογή', function( epoint $epoint ) {
	echo ( new WhichBrowser\Parser( $epoint->ins_ag ) )->toString() . "\n";
} );
$table->add( 'εισαγωγή', function( epoint $epoint ) {
	$dt = dtime::from_sql( $epoint->ins_tm );
	echo sprintf( '<time style="white-space: nowrap;" datetime="%s">%s</time> από <span style="white-space: nowrap;">%s</span>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ), $epoint->ins_ip ) . "\n";
} );
$table->add( 'λήξη', function( epoint $epoint ) {
	$dt = dtime::from_sql( $epoint->exp_tm );
	echo sprintf( '<time style="white-space: nowrap;" datetime="%s">%s</time>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ) ) . "\n";
} );
$table->add( 'είσοδοι', function( epoint $epoint ) {
	echo $epoint->logins . "\n";
} );
$table->add( 'ενέργειες', function( epoint $epoint ) {
	global $cepoint;
	if ( $epoint->epoint_id !== $cepoint->epoint_id ) {
		$href = site_href( 'settings.php', [ 'action' => 'epoint', 'epoint_id' => $epoint->epoint_id ] );
		echo sprintf( '<a href="%s" class="link link-delete link-ajax" data-remove="tr">', $href ) . "\n";
		echo '<span class="fa fa-trash"></span>' . "\n";
		echo '<span>διαγραφή</span>' . "\n";
		echo '</a>' . "\n";
	} else {
		$href = site_href( 'logout.php' );
		echo sprintf( '<a href="%s" class="link">', $href ) . "\n";
		echo '<span class="fa fa-sign-out" class="xa-link"></span>' . "\n";
		echo '<span>έξοδος</span>' . "\n";
		echo '</a>' . "\n";
	}
} );
page_body_add( [ $table, 'html' ], epoint::select( [
	'user_id' => $cuser->user_id,
], [
	'ins_tm' => 'DESC',
	'epoint_id' => 'DESC',
] ) );


/**********
 * vlinks *
 **********/

$table = new table( [
	'title' => 'σύνδεσμοι επαλήθευσης',
] );
$table->add( 'τύπος', function( vlink $vlink ) {
	switch ( $vlink->type ) {
		case 'register':
			echo 'εγγραφή' . "\n";
			break;
		case 'chmail':
			echo 'αλλαγή διεύθυνσης email (<i>' . $vlink->data . '</i>)' . "\n";
			break;
		case 'repass':
			echo 'επαναφορά κωδικού πρόσβασης' . "\n";
			break;
	}
} );
$table->add( 'εισαγωγή', function( vlink $vlink ) {
	$dt = dtime::from_sql( $vlink->ins_tm );
	echo sprintf( '<time style="white-space: nowrap;" datetime="%s">%s</time> από <span style="white-space: nowrap;">%s</span>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ), $vlink->ins_ip ) . "\n";
	echo '<br />' . "\n";
	echo ( new WhichBrowser\Parser( $vlink->ins_ag ) )->toString() . "\n";
} );
$table->add( 'ενεργοποίηση', function( vlink $vlink ) {
	if ( is_null( $vlink->act_tm ) ) {
		$dt = dtime::from_sql( $vlink->exp_tm );
		echo sprintf( 'μέχρι <time style="white-space: nowrap;" datetime="%s">%s</time>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ) ) . "\n";
	} else {
		$dt = dtime::from_sql( $vlink->act_tm );
		echo sprintf( '<time style="white-space: nowrap;" datetime="%s">%s</time> από <span style="white-space: nowrap;">%s</span>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ), $vlink->act_ip ) . "\n";
		echo '<br />' . "\n";
		echo ( new WhichBrowser\Parser( $vlink->act_ag ) )->toString() . "\n";
	}
} );
$table->add( 'ενέργειες', function( vlink $vlink ) {
	$href = site_href( 'settings.php', [ 'action' => 'vlink', 'vlink_id' => $vlink->vlink_id ] );
	echo sprintf( '<a href="%s" class="link link-delete link-ajax" data-remove="tr">', $href ) . "\n";
	echo '<span class="fa fa-trash"></span>' . "\n";
	echo '<span>διαγραφή</span>' . "\n";
	echo '</a>' . "\n";
} );
page_body_add( [ $table, 'html' ], vlink::select( [
	'user_id' => $cuser->user_id,
], [
	'ins_tm' => 'DESC',
	'vlink_id' => 'DESC',
] ) );


/********
 * exit *
 ********/

page_html();