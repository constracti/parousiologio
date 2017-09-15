$( function() {

var months = [];
if ( Storage !== undefined && localStorage.months !== undefined && localStorage.months !== '' )
	months = localStorage.months.split( ';' );
$( '.xa-month-toggle' ).each( function() {
	var month = $( this ).data( 'month' );
	var index = months.indexOf( month );
	if ( index !== -1 )
		$( '.xa-month[data-month="' + month + '"]' ).hide();
	else
		$( this ).addClass( 'w3-theme' );
} ).click( function() {
	var month = $( this ).data( 'month' );
	var index = months.indexOf( month );
	if ( index !== -1 ) {
		$( this ).addClass( 'w3-theme' );
		$( '.xa-month[data-month="' + month + '"]' ).show();
		months.splice( index, 1 );
	} else {
		$( this ).removeClass( 'w3-theme' );
		$( '.xa-month[data-month="' + month + '"]' ).hide();
		months.push( month );
	}
	if ( Storage !== undefined )
		localStorage.months = months.join( ';' );
} );

} );