<?php

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/?', function( $request, $response, $app ) {
	// Let's create a session variable, so we know where to redirect back to
	$redirect_to = $request->param( 'redirect_to' );

	// Let's log the user out
	IDMObject::unauthN( $redirect_to );
});

// Let's create a success page
respond( '/logout-success/?', function( $request, $response, $app ) {
	// Display the template
	$app->tpl->assign( 'show_page', 'logout-success' );
	$app->tpl->display( '_wrapper.tpl' );
});

// Let's create a cute little message page... so that PhoneGap users just see a flashing page
respond( '/logout-message/?', function( $request, $response, $app ) {
	// Display the template
	$app->tpl->assign( 'show_page', 'logout-message' );
	$app->tpl->display( '_wrapper.tpl' );
});
