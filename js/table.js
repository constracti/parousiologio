$( function() {

$( '.xa-table td:first-child>:last-child' ).
children( '.fa' ).addClass( 'fa-plus-square-o' ).end().
click( function() {
	$( this ).
	children( '.fa' ).toggleClass( 'fa-plus-square-o' ).toggleClass( 'fa-minus-square-o' ).end().
	parents( '.xa-table tr' ).toggleClass( 'xa-table-hidden' );
} );

} );