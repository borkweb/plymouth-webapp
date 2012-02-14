<?php

respond( '/?', function( $request, $response, $app ) {
	die('Bork');
});

respond( 'POST', '/save', function( $request, $response, $app ) {
	$first_name = $request->param('first_name');

	if( ! $first_name ) {
		$_SESSION['errors'][] = 'OH NOES';
		$response->redirect( $GLOBALS['BASE_URL'] . '/bork' );
	}//end if

	$response->redirect( $GLOBALS['BASE_URL'] . '/bork/success' );
});

respond( 'GET', '/save', function( $request, $response, $app ) {
	die('omg IM A GET');
});
