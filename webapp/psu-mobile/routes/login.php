<?php

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/', function( $request, $response, $app ) {
	// Let's create a session variable, so we know where to redirect back to
	$_SESSION['called_url'] = $request->param( 'redirect_to' );

	// Authenticate the user
	IDMObject::authN();

	// If we got here, we must be authenticated
	// Redirect by changing the URL to send a success Flag to the JavaScript onLocationChange API
	header('Location: login_success/');
});

// Let's make sure to redirect them to the originally called URL if they requested to
respond( '/login_success/', function( $request, $response, $app ) {
	if (!empty($_SESSION['called_url'])) {
		// Redirect to the originally intended authentication url
		header('Location: ' . $_SESSION['called_url']);
	}
});
