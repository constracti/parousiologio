<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . HOME_URL );
	exit;
}

( function() {
	global $cuser;
	if ( !array_key_exists( 'last_name', $_POST ) )
		return;
	if ( !array_key_exists( 'first_name', $_POST ) )
		return;
	$cuser->last_name = $_POST['last_name'] !== '' ? $_POST['last_name'] : NULL;
	$cuser->first_name = $_POST['first_name'] !== '' ? $_POST['first_name'] : NULL;
	$cuser->update();
	return page_message_add( 'Το προφίλ ενημερώθηκε.', 'success' );
} )();

page_title_set( 'Προφίλ' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= HOME_URL ?>profile.php" title="προφίλ">
	<span class="fa fa-pencil"></span>
	<span class="w3-hide-small">προφίλ</span>
</a>
<?php
} );

page_body_add( function() {
	global $cuser;
?>
<section class="w3-panel w3-content">
	<form class="w3-card-4 w3-round w3-theme-l4" method="post">
		<div class="w3-row-padding">
			<div class="w3-margin-top w3-half">
				<label>επώνυμο *</label>
				<input class="w3-input" name="last_name" type="text" placeholder="επώνυμο" value="<?= $_POST['last_name'] ?? $cuser->last_name ?? '' ?>" />
			</div>
			<div class="w3-margin-top w3-half">
				<label>όνομα *</label>
				<input class="w3-input" name="first_name" type="text" placeholder="όνομα" value="<?= $_POST['first_name'] ?? $cuser->first_name ?? '' ?>" />
			</div>
		</div>
		<div class="w3-container">
			<div class="w3-section">
				<button class="w3-button w3-round w3-theme-action" type="submit">
					<span class="fa fa-floppy-o"></span>
					<span>αποθήκευση</span>
				</button>
			</div>
		</div>
	</form>
</section>
<?php
} );

page_html();