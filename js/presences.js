$( function() {

function calculate_child( id ) {
	var sum = $( '.xa-presence-item[data-child="' + id + '"]:checked' ).length;
	$( '.xa-presence-child[data-child="' + id + '"]' ).html( sum );
}

function calculate_event( id ) {
	var sum = $( '.xa-presence-item[data-event="' + id + '"]:checked' ).length;
	$( '.xa-presence-event[data-event="' + id + '"]' ).html( sum );
}

function calculate_total() {
	var sum = $( '.xa-presence-item:checked' ).length;
	$( '.xa-presence-total' ).html( sum );
}

$( '.xa-presence-child' ).each( function() {
	calculate_child( $( this ).data( 'child' ) );
} );

$( '.xa-presence-event' ).each( function() {
	calculate_event( $( this ).data( 'event' ) );
} );

calculate_total();

$( '.xa-presence-item' ).change( function() {
	calculate_child( $( this ).data( 'child' ) );
	calculate_event( $( this ).data( 'event' ) );
	calculate_total();
	$.post( '', {
		child_id: $( this ).data( 'child' ),
		event_id: $( this ).data( 'event' ),
		check : $( this ).prop( 'checked' ) ? 'on' : 'off',
	} );
} );

} );