<?php

require_once 'php/core.php';

privilege( user::ROLE_GUEST );

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

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$cuser->last_name = $fields['last_name']->post();
	$cuser->first_name = $fields['first_name']->post();
	$cuser->update();
	success( [
		'alert' => 'Το προφίλ ενημερώθηκε.',
	] );
}

page_title_set( 'Προφίλ' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>profile.php" title="προφίλ">
	<span class="fa fa-pencil"></span>
	<span class="w3-hide-small">προφίλ</span>
</a>
<?php
} );

page_body_add( 'form_section', $fields, [
	'responsive' => 'w3-col m6 s12',
] );

page_html();