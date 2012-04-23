<?php

$username = stripslashes($_GET['username']);
$reason = stripslashes($_GET['reason']);
$ssn = stripslashes($_GET['ssn']);
$dob = strtotime($_GET['Date_Year'].'-'.$_GET['Date_Month'].'-'.$_GET['Date_Day']);

try
{
	if(!$reason && !$ssn && !$dob)
	{
		// TODO: fallback when javascript wasn't available?
		throw new Exception('An identity confirmation must be provided.');
	}

	if(!$GLOBALS['ape']->canResetPassword()) {
		$GLOBALS['LOG']->write('Password Reset Attempt Failed: Not authorized to reset passwords.', $username);
		throw new Exception('You are not allowed to perform password resets (missing role, or not in IP whitelist).');
	}

	if( !$username ) {
		throw new Exception('Username missing from password reset request.');
	}

	$person = new PSUPerson( $username );

	if( !$reason && ($ssn != substr($person->ssn, -4) || $dob != $person->birth_date) ) {
		$GLOBALS['LOG']->write('Password Reset Attempt Failed: invalid DOB & SSN portion provided.', $username);
		throw new Exception('The identity verification failed.  Either the last 4 of the SSN OR the Date of Birth did not match.');
	}//end if

	if( $reason && !IDMObject::authZ('permission', 'ape_pw') ) {
		throw new Exception('You are not allowed to perform password resets without the last 4 of the SSN and Birth Date.');
	}//end if

	if( !$reason ) {
		$reason = 'Private Data Provided and Verified';
	}//end if

	list($username, $password) = $GLOBALS['PWMAN']->defaultCredentials($username);

	$GLOBALS['PWMAN']->setPassword($username, $password);
	$GLOBALS['PWMAN']->expire($username, 'reset');

	$GLOBALS['LOG']->write('Resetting password: ' . $reason, $username);


	$message = "Password for $username has been reset to the default.";

	if( $call_id = APE::create_ticket( $username, 'Password Reset', 'Reset password.', array( 'call_status' => 'closed' ) ) ) {
		$message .= ' Ticket #<a href="http://go.plymouth.edu/log/'.$call_id.'">'.$call_id.'</a> has been logged.';
	}//end if

	$_SESSION['messages'][] = $message;
}
catch(Exception $e)
{
	$_SESSION['errors'][] = sprintf("%s (%d)", $e->GetMessage(), $e->GetCode());
}
PSUHTML::redirect($GLOBALS['BASE_URL'] . '/user/' . $username);
