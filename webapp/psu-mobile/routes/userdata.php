<?php

// Catch all response logic. No matter what, if this route is called, do this
respond( '[*]', function () {
	// Authenticate the user
	IDMObject::authN();
});

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/?', function( $request, $response, $app ){
	$userdata = (array) \PSU::api('backend')->get('user/' . $_SESSION['username']);

	$app->tpl->assign( 'userdata', $userdata );

	// Display the template
	$app->tpl->assign( 'show_page', 'userdata' );
	$app->tpl->display( '_wrapper.tpl' );
});
