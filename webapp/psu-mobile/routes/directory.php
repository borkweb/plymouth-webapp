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
	$search_query = urlencode( $request->param( 'what' ) );

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
respond( 'POST', '/user/[:username]/?', function( $request, $response, $app ){
	// Get the user detail data from the request. That way we can save an API call.
	$user_details = json_decode( stripslashes( $request->param( 'user-details' ) ) );

	// Assign the user_details object to the template
	$app->tpl->assign( 'user_data', $user_details );

	// Display the template
	$app->tpl->assign( 'show_page', 'directory-details' );
	$app->tpl->display( '_wrapper.tpl' );
});
