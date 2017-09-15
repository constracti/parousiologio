$( function() {

var properties = [
	'grade_name',
];
if ( Storage !== undefined && localStorage.properties !== undefined && localStorage.properties !== '' )
	properties = localStorage.properties.split( ';' );
$( '.xa-property-toggle' ).each( function() {
	var property = $( this ).data( 'property' );
	var index = properties.indexOf( property );
	if ( index !== -1 )
		$( this ).addClass( 'w3-theme' );
	else
		$( '.xa-property[data-property="' + property + '"]' ).hide();
} ).click( function() {
	var property = $( this ).data( 'property' );
	var index = properties.indexOf( property );
	if ( index !== -1 ) {
		$( this ).removeClass( 'w3-theme' );
		$( '.xa-property[data-property="' + property + '"]' ).hide();
		properties.splice( index, 1 );
	} else {
		$( this ).addClass( 'w3-theme' );
		$( '.xa-property[data-property="' + property + '"]' ).show();
		properties.push( property );
	}
	if ( Storage !== undefined )
		localStorage.properties = properties.join( ';' );
} );

} );