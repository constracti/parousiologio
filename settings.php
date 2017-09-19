<?php

require_once 'php/page.php';

if ( is_null( $cuser ) ) {
	header( 'location: ' . SITE_URL );
	exit;
}

# TODO epoint & vlink administration

page_title_set( 'Ρυθμίσεις' );

page_nav_add( function() {
?>
<a class="w3-bar-item w3-button" href="<?= SITE_URL ?>settings.php" title="ρυθμίσεις">
	<span class="fa fa-cog"></span>
	<span class="w3-hide-small">ρυθμίσεις</span>
</a>
<?php
} );

page_body_add( function() {
	global $cuser;
?>
<section class="w3-panel w3-content">
	<h3>στοιχεία λογαριασμού</h3>
	<p><a href="<?= SITE_URL ?>chmail.php">διεύθυνση email</a></p>
	<p><a href="<?= SITE_URL ?>chpass.php">κωδικός πρόσβασης</a></p>
</section>
<?php
} );

page_html();