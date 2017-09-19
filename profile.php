<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . SITE_URL );
	exit;
}

$field_success = TRUE;
$fields = [
	'last_name' => new field( 'last_name', [
		'placeholder' => 'επώνυμο',
		'value' => $cuser->last_name,
	] ),
	'first_name' => new field( 'first_name', [
		'placeholder' => 'όνομα',
		'value' => $cuser->first_name,
	] ),
];

$field_success && ( function( array $fields ) {
	global $cuser;
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return;
	$cuser->last_name = $fields['last_name']->value();
	$cuser->first_name = $fields['first_name']->value();
	$cuser->update();
	return page_message_add( 'Το προφίλ ενημερώθηκε.', 'success' );
} )( $fields );

page_title_set( 'Προφίλ' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>profile.php" title="προφίλ">
	<span class="fa fa-pencil"></span>
	<span class="w3-hide-small">προφίλ</span>
</a>
<?php
} );

page_body_add( 'form_html', $fields, [
	'responsive' => 'w3-col m6 s12',
] );

page_html();