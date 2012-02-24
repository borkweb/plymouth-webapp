<?php
respond( '/?', function( $request, $response, $app ) {
	die('Bork');
});
//when a form is posted with the location of /save it will require a first name and then it will check to see if that is there. if it is not there, it will respond with an error.
respond( 'POST', '/save', function( $request, $response, $app ) {
	$first_name = $request->param('first_name');

	if( ! $first_name ) {
		$_SESSION['errors'][] = 'First name not found';
		$response->redirect( $GLOBALS['BASE_URL'] . '/bork' );
	}//end if

	$response->redirect( $GLOBALS['BASE_URL'] . '/bork/success' );
});

respond( 'GET', '/save', function( $request, $response, $app ) {
	die('omg IM A GET');
});

respond( '/event' , function( $request, $response, $app ){
	die('TESDT');
});
