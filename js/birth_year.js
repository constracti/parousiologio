$( function() {

var grade_id = $( '[name="grade_id"]' );
var year = parseInt( grade_id.data( 'year' ) );
var birth_year = $( '[name="birth_year"]' );

grade_id.change( function() {
	if ( birth_year.val() !== '' )
		return;
	var age = parseInt( grade_id.val() ) + 6;
	birth_year.val( year - age );
} );

} );
