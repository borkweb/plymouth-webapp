<?php

/**
 * reset the expiration date on person_phone so that they are prompted to sign up with and re-confirm mobile number when logging into myPlymouth
 */

try {
	// can the user perform this action?
	if( !IDMObject::authZ('permission', 'mis') && !APEAuthZ::infodesk() ) {
		throw new Exception('You are not authorized to reset emergency phone information.');
	} // end if

	// did we get all the needed data?
	if( !isset($_GET['wp_id']) ) {
		throw new Exception('wp_id was missing in request.');
	} // end if

	$person = PSUPerson::get( $_GET['wp_id'] );

	if( $ok = $person->emergency_phone->unconfirm() ) {
		$GLOBALS['LOG']->write('Emergency phone reset', $_GET['wp_id']);

		$response['message'] = 'Emergency number reset.  They will be prompted to confirm on next login (assuming they are a student/employee)';
		$response['status'] = 'success';
	} // end if
	else {
		throw new Exception('Error resetting: '.$ok );
	} // end else
} // end try
catch(Exception $e) {
	$response['message'] = $e->getMessage();
} // end catch

//
// ajax requests end here
//
if( isset($_GET['method']) && $_GET['method'] == 'js' ) {
	header('Content-type: application/json');
	die( json_encode($response) );
} // end if

//
// otherwise, redirect back to the user page
// 
$redirect_to = $GLOBALS['BASE_URL'];

// pass along our message
if( $response['status'] == 'success' ) {
	$_SESSION['messages'][] = $response['message'];
}
else {
	$_SESSION['errors'][] = $response['message'];
}

if( isset($_GET['wp_id']) ) {
	$redirect_to .= '/user/' . $_GET['wp_id'];
}

if( ! $_GET['noredirect'] ) {
	PSUHTML::redirect( $redirect_to ); 
}//end if
