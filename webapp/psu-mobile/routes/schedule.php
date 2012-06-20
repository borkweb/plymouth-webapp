<?php

// Catch all response logic. No matter what, if this route is called, do this
respond( '[*]', function () {
	// Authenticate the user
	IDMObject::authN();
});

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/?', function( $request, $response, $app ){
	// Let's create a new person based off of who's logged in
	$person = new \PSUPerson( $_SESSION['username'] );

	// Let's create an array to hold the schedule data
	$schedule = array();
	
	// Because this data is lazy loaded, let's just ask for it so that we force the request
	$person->student->levels;

	// Let's go through each level of the student
	foreach ( $person->student->levels as $level ) {
		// Let's get the current term code for that level
		$term_code = (array) \PSU::api('backend')->get('student/term-code/' . $level);

		// PSU::api uses Guzzle for its HTTP responses. We need to catch an exception, in case the call fails
		try {
			// Now let's add the schedule data for that term into our schedule array
			$schedule[$level] = (array) \PSU::api('backend')->get(
				'student/schedule/{{identifier}}',
				array(
					'identifier' => $person->id,
				),
				array(
					'term_code' => $term_code['term_code'],
				)
			);
		}
		catch (Guzzle\Http\Message\BadResponseException $e) {
			// Lets grab the exception and put it into the session
			$_SESSION['errors'][] = $e->getMessage();

			// Let's get the response data so we can see the problem
			$response = $e->getResponse();

			// Let's grab the HTTP status and status code
			$response_data['status'] = $response->getReasonPhrase();
			$response_data['status_code'] = $response->getStatusCode();
		}
	}

	$app->tpl->assign( 'schedule', $schedule );

	// Display the template
	$app->tpl->assign( 'show_page', 'schedule' );
	$app->tpl->display( '_wrapper.tpl' );
});
