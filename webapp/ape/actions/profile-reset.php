<?php
// TODO: move the functionality in this file into an API

//
// action page for deleting windows profiles (vista roaming or terminal services)
//

$response = array('status' => 'error', 'message' => null);

try 
{
	// can the user perform this action?
	if( !IDMObject::authZ('permission', 'ape_profilereset') )
	{
		throw new Exception('You are not authorized to reset profiles.');
	}

	// did we get all the needed data?
	if( !isset($_GET['username']) || !isset($_GET['profile']) )
	{
		throw new Exception('Username or profile type was missing in request.');
	}

	$args = array(
		'username' => $_GET['username'],
		'profile' => $_GET['profile'] == 0 ? 0 : 1 // 0 == vista, 1 == terminal services
	);

	// validate the username
	$pidm = $GLOBALS['BannerIDM']->getIdentifier($args['username'], 'username', 'pid');
	if($pidm === false)
	{
		throw new Exception('An invalid username was specified (pidm not found).');
	}

	//
	// everything's good, insert the record
	//

	$systems = PSUDatabase::connect('mysql/systems');
	$sql = "INSERT INTO profile_reset (uname, profile) VALUES (?, ?)";
	$systems->Execute($sql, $args);

	$profile_type = $args['profile'] == 0 ? 'Vista roaming' : 'Terminal Services';

	$GLOBALS['LOG']->write('Profile reset (' . $profile_type . ')', $args['username']);

	$response['message'] = sprintf("%s profile queued for deletion, this may take up to three minutes.", $profile_type);
	$response['status'] = 'success';
}
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

if( isset($_GET['username']) )
{
	$redirect_to .= '/user/' . $_GET['username'];
}

if( ! $_GET['noredirect'] ) {
	PSUHTML::redirect( $redirect_to ); 
}//end if
