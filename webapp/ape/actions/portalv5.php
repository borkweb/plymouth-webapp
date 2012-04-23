<?php
try
{
	$person = new PSUPerson($_GET['identifier']);

	if( !$person->pidm ) {
		throw new Exception('The user provided cannot opt-in/opt-out of myPlymouth v5 Beta');
	}

	if(!(IDMObject::authZ('permission', 'mis') || $person->wp_id == $_SESSION['wp_id'] || IDMObject::authZ('permission', 'ape_wordpress_admin') ))
	{
		throw new Exception('You are not authorized to toggle myPlymouth v5 Beta Opt-Ins.');
	}

	$user = get_userdatabypidm( $person->pidm );

	if( $user->portalv5 ) {
		delete_usermeta( $user->ID, 'portalv5');
		$status = 'disabled';
	} else {
		update_usermeta( $user->ID, 'portalv5', 1 );
		$status = 'enabled';
	}//end if

	if( $_GET['portal'] ) {
		if( $status == 'enabled' ) {
			$response['message'] = 'Hooray! You have enabled the myPlymouth v5 Beta on your account!';
		} else {
			$response['message'] = 'You have temporarily disabled the myPlymouth v5 Beta for your account.';
		}//end else
	} else {
		$response['message'] = 'Portal v5 Opt-In has been '.$status.'.';
	}//end else

	$GLOBALS['LOG']->write($response['message'], $person->login_name);

	$response['status'] = 'success';
}//end try
catch(Exception $e)
{
	$response['message'] = $e->getMessage();
}
//
// ajax requests end here
//
if( isset($_GET['method']) && $_GET['method'] == 'js' )
{
	header('Content-type: application/json');
	die( json_encode($response) );
}

//
// otherwise, redirect back to the user page
// 
$redirect_to = $GLOBALS['BASE_URL'];

// pass along our message
if( $response['status'] == 'success' )
{
	$_SESSION['messages'][] = $response['message'];
}
else
{
	$_SESSION['errors'][] = $response['message'];
}

if( isset( $_GET['portal'] ) ) {
	$redirect_to = 'http://go.plymouth.edu/my5';
} else {
	if( isset($_GET['identifier']) )
	{
		$redirect_to .= '/user/' . $_GET['identifier'];
	}
}//end else

PSUHTML::redirect( $redirect_to ); 
