$( function() {

var months = [];
if ( Storage !== undefined && localStorage.getItem( 'months' ) !== null && localStorage.getItem( 'months' ) !== '' )
	months = localStorage.getItem( 'months' ).split( ';' );
$( '.month-toggle' ).each( function() {
	var month = $( this ).data( 'month' );
	var index = months.indexOf( month );
	if ( index !== -1 )
		$( '.presences-month[data-month="' + month + '"]' ).addClass( 'presences-month-hide' );
	else
		$( this ).addClass( 'w3-theme' );
} ).click( function() {
	var month = $( this ).data( 'month' );
	var index = months.indexOf( month );
	if ( index !== -1 ) {
		$( this ).addClass( 'w3-theme' );
		$( '.presences-month[data-month="' + month + '"]' ).removeClass( 'presences-month-hide' );
		months.splice( index, 1 );
	} else {
		$( this ).removeClass( 'w3-theme' );
		$( '.presences-month[data-month="' + month + '"]' ).addClass( 'presences-month-hide' );
		months.push( month );
	}
	if ( Storage !== undefined )
		localStorage.setItem( 'months', months.join( ';' ) );
} );

} );