<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	switch ( request_var( 'delete' ) ) {
		case 'epoint':
			$epoint = epoint::request( 'epoint_id' );
			if ( $epoint->user_id !== $cuser->user_id )
				failure( 'argument_not_valid', 'epoint_id' );
			if ( $epoint->epoint_id === $cepoint->epoint_id )
				failure( 'argument_not_valid', 'epoint_id' );
			$epoint->delete();
			success();
		case 'vlink':
			$vlink = vlink::request( 'vlink_id' );
			if ( $vlink->user_id !== $cuser->user_id )
				failure( 'argument_not_valid', 'vlink_id' );
			$vlink->delete();
			success();
		default:
			failure( 'argument_not_valid', 'delete' );
	}
}

require_once COMPOSER_DIR . 'whichbrowser/vendor/autoload.php';

page_title_set( 'Ρυθμίσεις' );

page_style_add( SITE_URL . 'css/table.css' );
page_script_add( SITE_URL . 'js/table.js' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>settings.php" title="ρυθμίσεις">
	<span class="fa fa-cog"></span>
	<span class="w3-hide-small">ρυθμίσεις</span>
</a>
<?php
} );

page_body_add( function() {
?>
<section class="w3-panel w3-content">
	<h3>στοιχεία λογαριασμού</h3>
	<p><a href="<?= SITE_URL ?>chmail.php">διεύθυνση email</a></p>
	<p><a href="<?= SITE_URL ?>chpass.php">κωδικός πρόσβασης</a></p>
</section>
<?php
} );

page_body_add( function() {
	global $cuser;
	$epoints = epoint::select( [ 'user_id' => $cuser->user_id ], [ 'ins_tm' => 'DESC', 'epoint_id' => 'DESC' ] );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<h3>σημεία εισόδου</h3>' . "\n";
	$table = new table();
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
			$href = SITE_URL . sprintf( 'settings.php?delete=epoint&epoint_id=%d', $epoint->epoint_id );
			echo sprintf( '<a href="%s" class="link link-delete link-ajax" data-remove="tr">', $href ) . "\n";
			echo '<span class="fa fa-trash"></span>' . "\n";
			echo '<span>διαγραφή</span>' . "\n";
			echo '</a>' . "\n";
		} else {
			$href = SITE_URL . 'logout.php';
			echo sprintf( '<a href="%s" class="link">', $href ) . "\n";
			echo '<span class="fa fa-sign-out" class="xa-link"></span>' . "\n";
			echo '<span>έξοδος</span>' . "\n";
			echo '</a>' . "\n";
		}
	} );
	$table->html( $epoints );
	echo '</section>' . "\n";
} );

page_body_add( function() {
	global $cuser;
	$vlinks = vlink::select( [ 'user_id' => $cuser->user_id ], [ 'ins_tm' => 'DESC', 'vlink_id' => 'DESC' ] );
	echo '<section class="w3-panel w3-content">' . "\n";
	echo '<h3>σύνδεσμοι επαλήθευσης</h3>' . "\n";
	$table = new table();
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
		$href = SITE_URL . sprintf( 'settings.php?delete=vlink&vlink_id=%d', $vlink->vlink_id );
		echo sprintf( '<a href="%s" class="link link-delete link-ajax" data-remove="tr">', $href ) . "\n";
		echo '<span class="fa fa-trash"></span>' . "\n";
		echo '<span>διαγραφή</span>' . "\n";
		echo '</a>' . "\n";
	} );
	$table->html( $vlinks );
	echo '</section>' . "\n";
} );

page_html();