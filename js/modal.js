$( function() {

$( '.xa-modal-show' ).click( function() {
	$( $( this ).data( 'modal' ) ).show();
} );

$( '.xa-modal' ).click( function() {
	$( this ).hide();
} ).find( '.w3-modal-content' ).click( function( event ) {
	event.stopPropagation();
} ).end().
find( '.w3-hover-red' ).click( function() {
	$( this ).parents( '.xa-modal' ).hide();
} );

} );