$( function() {

$( '.modal-show' ).click( function() {
	$( $( this ).data( 'modal' ) ).show();
} );

$( '.modal' ).click( function() {
	$( this ).hide();
} ).find( '.w3-modal-content' ).click( function( event ) {
	event.stopPropagation();
} ).end().
find( '.w3-hover-red' ).click( function() {
	$( this ).parents( '.modal' ).hide();
} );

} );