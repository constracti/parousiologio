$( function() {

$( '#presences-sidebar>.presences-event' ).click( function() {
	var event = $( this ).data( 'event' );
	var selected = !$( this ).hasClass( 'w3-theme' );
	$( '#presences-sidebar>.presences-event' ).removeClass( 'w3-theme' );
	$( '#presences-table .presences-event' ).removeClass( 'presences-event-visible' );
	if ( selected ) {
		$( '#presences-container' ).addClass( 'presences-container-expanded' );
		$( this ).addClass( 'w3-theme' );
		$( '#presences-table .presences-event[data-event="' + event + '"]' ).addClass( 'presences-event-visible' );
	} else {
		$( '#presences-container' ).removeClass( 'presences-container-expanded' );
	}
	if ( Storage !== undefined ) {
		if ( selected )
			localStorage.setItem( 'event', event );
		else
			localStorage.removeItem( 'event' );
	}
} );
if ( Storage !== undefined && localStorage.getItem( 'event' ) !== null )
	$( '#presences-sidebar>.presences-event[data-event="' + localStorage.getItem( 'event' ) + '"]' ).click();

} );