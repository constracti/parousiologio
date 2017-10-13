$( function() {

var properties = [
	'grade_name',
];
if ( Storage !== undefined && localStorage.getItem( 'properties' ) !== null ) {
	if ( localStorage.getItem( 'properties' ) !== '' )
		properties = localStorage.getItem( 'properties' ).split( ';' );
	else
		properties = [];
}
$( '.property-toggle' ).each( function() {
	var property = $( this ).data( 'property' );
	var index = properties.indexOf( property );
	if ( index !== -1 ) {
		$( this ).addClass( 'w3-theme' );
		$( '.presences-property[data-property="' + property + '"]' ).addClass( 'presences-property-show' );
	}
} ).click( function() {
	var property = $( this ).data( 'property' );
	var index = properties.indexOf( property );
	if ( index !== -1 ) {
		$( this ).removeClass( 'w3-theme' );
		$( '.presences-property[data-property="' + property + '"]' ).removeClass( 'presences-property-show' );
		properties.splice( index, 1 );
	} else {
		$( this ).addClass( 'w3-theme' );
		$( '.presences-property[data-property="' + property + '"]' ).addClass( 'presences-property-show' );
		properties.push( property );
	}
	if ( Storage !== undefined )
		localStorage.setItem( 'properties', properties.join( ';' ) );
} );

} );