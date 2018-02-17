<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Χρήστες' );

page_style_add( site_href( 'css/table.css', [ 'ver' => time() ] ) );

page_script_add( site_href( 'js/table.js' ) );

page_nav_add( 'bar_link', [
	'href' => site_href( 'users.php' ),
	'text' => 'χρήστες',
	'icon' => 'fa-user',
	'hide_medium' => FALSE,
] );

$table = new table( [
	'full_screen' => TRUE,
] );
$table->add_filter( 'role', 'δικαιώματα', user::ROLES );
$table->add( 'name', 'ονοματεπώνυμο', function( user $user ) {
	if ( !is_null( $user->last_name ) )
		echo sprintf( '<span>%s</span>', $user->last_name ) . "\n";
	if ( !is_null( $user->first_name ) )
		echo sprintf( '<span>%s</span>', $user->first_name ) . "\n";
	echo '<div class="w3-small">' . "\n";
	$href = site_href( 'user-update.php', [ 'user_id' => $user->user_id ] );
	echo sprintf( '<a href="%s" class="link">', $href ) . "\n";
	echo '<span class="fa fa-pencil"></span>' . "\n";
	echo '<span>επεξεργασία</span>' . "\n";
	echo '</a>' . "\n";
	$href = site_href( 'user-teams.php', [ 'user_id' => $user->user_id ] );
	echo sprintf( '<a href="%s" class="link">', $href ) . "\n";
	echo '<span class="fa fa-users"></span>' . "\n";
	echo '<span>ομάδες</span>' . "\n";
	echo '</a>' . "\n";
	$href = site_href( 'user-delete.php', [ 'user_id' => $user->user_id ] );
	echo sprintf( '<a href="%s" class="link link-delete link-ajax" data-confirm="οριστική διαγραφή;" data-remove="tr">', $href ) . "\n";
	echo '<span class="fa fa-trash"></span>' . "\n";
	echo '<span>διαγραφή</span>' . "\n";
	echo '</a>' . "\n";
	echo '</div>' . "\n";
}, FALSE );
$table->add( 'contact', 'επικοινωνία', function( user $user ) {
	echo sprintf( '<span class="fa %s"></span>', is_null( $user->password_hash ) ? 'fa-unlock-alt' : 'fa-lock' ) . "\n";
	echo sprintf( '<a href="mailto:%s">%s</a>', $user->email_address, $user->email_address ) . "\n";
	echo '<div>' . "\n";
	if ( !is_null( $user->home_phone ) )
		echo sprintf( '<a href="tel:%s">%s</a>', $user->home_phone, $user->home_phone ) . "\n";
	if ( !is_null( $user->mobile_phone ) )
		echo sprintf( '<a href="tel:%s">%s</a>', $user->mobile_phone, $user->mobile_phone ) . "\n";
	echo '</div>' . "\n";
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
$table->add( 'role', 'δικαιώματα', function( user $user ) {
	echo user::ROLES[ $user->role ] . "\n";
} );
$table->add( 'reg', 'εγγραφή', function( user $user ) {
	if ( !is_null( $user->reg_tm ) ) {
		$dt = new dtime( $user->reg_tm );
		echo sprintf( '<time datetime="%s" style="white-space: nowrap;">%s</time>', $dt->format( dtime::DATETIME ), $dt->format( dtime::DATE ) ) . "\n";
	}
	if ( !is_null( $user->reg_ip ) )
		echo sprintf( '<span style="white-space: nowrap;">από %s</span>', $user->reg_ip ) . "\n";
}, FALSE );
$table->add( 'act', 'ενέργεια', function( user $user ) {
	echo '<span style="white-space: nowrap;">';
	if ( is_null( $user->act_tm ) ) {
		echo 'ποτέ';
	} else {
		$dt = new dtime( $user->act_tm );
		$diff = $dt->human_diff();
		if ( $diff !== '' )
			echo sprintf( '%s πριν', $dt->human_diff() );
		else
			echo 'τώρα';
	}
	echo '</span>' . "\n";
}, FALSE );
$where = [];
if ( isset( $_GET['role'] ) && $_GET['role'] !== '' )
	$where['role'] = intval( $_GET['role'] );
$orderby = [];
switch ( $_GET['orderby'] ) {
	case 'reg':
		$orderby['reg_tm'] = ( $_GET['order'] !== 'desc' ) ? 'ASC' : 'DESC';
		break;
	case 'act':
		$orderby['act_tm'] = ( $_GET['order'] !== 'desc' ) ? 'ASC' : 'DESC';
		break;
	default:
		$orderby['last_name'] = ( $_GET['order'] !== 'desc' ) ? 'ASC' : 'DESC';
		$orderby['first_name'] = ( $_GET['order'] !== 'desc' ) ? 'ASC' : 'DESC';
		break;
}
$orderby['user_id'] = ( $_GET['order'] !== 'desc' ) ? 'ASC' : 'DESC';
page_body_add( [ $table, 'html' ], user::select( $where, $orderby ) );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= site_href( 'users-download.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="μεταφόρτωση">
		<span class="fa fa-download"></span>
	</a>
</section>
<?php
} );

page_html();
