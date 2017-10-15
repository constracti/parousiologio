<?php

require_once 'php/core.php';

privilege( user::ROLE_ADMIN );

page_title_set( 'Παιδιά' );

page_nav_add( 'bar_link', [
	'href' => site_href( 'children.php' ),
	'text' => 'παιδιά',
	'icon' => 'fa-child',
	'hide_medium' => FALSE,
] );

page_body_add( function() {
?>
<section class="action">
	<a href="<?= site_href( 'child-insert.php' ) ?>" class="w3-button w3-circle w3-theme-action" title="προσθήκη">
		<span class="fa fa-plus"></span>
	</a>
</section>
<?php
} );

page_html();