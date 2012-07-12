<?php

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/?', function( $request, $response, $app ) {
	// Let's create a session variable, so we know where to redirect back to
	$app->params['called_url'] = $request->param( 'redirect_to' );
	$app->params['back_button_url'] = $request->param( 'came_from' );

	// Authenticate the user
	IDMObject::authN();

	// If we got here, we must be authenticated
	// Redirect by changing the URL to send a success Flag to the JavaScript onLocationChange API
	$response->redirect( 'login-success/' );
});

// Let's make sure to redirect them to the originally called URL if they requested to
respond( '/login-success/?', function( $request, $response, $app ) {
	if ( !empty( $app->params['called_url'] ) ) {
		// Redirect to the originally intended authentication url
		$response->redirect( $app->params['called_url'] );
	}
});
