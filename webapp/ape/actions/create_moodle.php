<?php

//
// action page for creating moodle accounts manually
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
	if( !isset($_GET['username']) || !isset($_GET['pidm']) )
	{
		throw new Exception('Username or pidm was missing in request.');
	}
}
catch(Exception $e)
{
	$response['message'] = $e->getMessage();
}

$pidm = $_GET['pidm'];
$p = new PSUPerson($pidm);
$username = $p->username;
$time = mktime();
$hash = md5($username . $time . $config->get('moodle', 'ape_secret') );

$get_data = http_build_query( compact('username', 'pidm', 'time', 'hash' ) );
$ch = curl_init();

$moodle = 'moodle' . $_GET['version'];

curl_setopt($ch, CURLOPT_URL, $config->get( $moodle, 'base_url') . '/course/report/portal/create_account.php?' . $get_data);

curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
$response['message'] = curl_exec($ch);
curl_close($ch);

$response['status'] = strpos($response['message'], 'Inserted') ? 'success' : '';

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

if( isset($_GET['pidm']) )
{
	$redirect_to .= '/user/' . $_GET['pidm'];
}

PSUHTML::redirect( $redirect_to );
