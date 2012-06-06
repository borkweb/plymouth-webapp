<?php

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/?', function( $request, $response, $app ){
	// Display the template
	$app->tpl->assign( 'show_page', 'directory' );
	$app->tpl->display( '_wrapper.tpl' );
});

// When someone searches
respond( '/search/[:what]/?', function( $request, $response, $app ){
	// Get the search parameter from the request (url encode it... it may contain spaces, etc)
	$search_query = $request->param( 'what' );

	// Initialize the search results array, in case the API fails
	$search_results = array();

	// Let's get our results
	$search_results = Mobile::directory_search( $search_query );

	// Assign the search results array to the template
	$app->tpl->assign( 'results', $search_results );

	// Display the template
	$app->tpl->assign( 'show_page', 'directory-results' );
	$app->tpl->display( '_wrapper.tpl' );
});

// Let's get the details of the person
respond( 'GET', '/user/[:username]/?', function( $request, $response, $app ){
	// Get the search parameter from the request (url encode it... it may contain spaces, etc)
	$username = $request->param( 'username' );

	// Let's get our results
	$user_details = Mobile::directory_search( $username );

	// If there was more than one result, someone didn't use this correctly
	if ( count( $user_details ) > 1 ) {
		// We'll let it work anyway, but let's warn the developer
		$_SESSION['warnings'][] = 'More than 1 result returned. Username may have been too ambiguous.';
	}

	// Assign the user_details object to the template
	$app->tpl->assign( 'user_data', $user_details[0] );
});

// Let's get the details of the person
respond( 'POST', '/user/[:username]/?', function( $request, $response, $app ){
	// Get the user detail data from the request. That way we can save an API call.
	$user_details = json_decode( stripslashes( $request->param( 'user-details' ) ) );

	// Assign the user_details object to the template
	$app->tpl->assign( 'user_data', $user_details );
});

// Let's get the details of the person
respond( '/user/[:username]/?', function( $request, $response, $app ){
	// Display the template
	$app->tpl->assign( 'show_page', 'directory-details' );
	$app->tpl->display( '_wrapper.tpl' );
});
