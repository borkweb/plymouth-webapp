<?php

//
// action page for locking and unlocking Oracle Accounts
//

$response = array('status' => 'error', 'message' => null);

try 
{
	// can the user perform this action?
	if( !IDMObject::authZ('permission', 'ape_oracle_lock') ) {
		throw new Exception('You are not authorized to lock/unlock Oracle Accounts.');
	}

	// did we get all the needed data?
	if( !isset($_GET['username']) ) {
		throw new Exception('Username was missing in request.');
	}

	$args = array(
		'username' => $_GET['username'],
	);

	// validate the username
	$pidm = $GLOBALS['BannerIDM']->getIdentifier($args['username'], 'username', 'pid');
	if($pidm === false) {
		throw new Exception('An invalid username was specified (pidm not found).');
	}

	//
	// everything's good, start the work
	//

	$person = new PSUPerson( $args['username'] );

	if( substr( $person->oracle_account_status, 0, 6 ) == 'LOCKED' ) {
		if( PSU::db('banner')->Execute("ALTER USER ".$args['username']." ACCOUNT UNLOCK") ) {
			$GLOBALS['LOG']->write('Unlocking Oracle Account', $args['username']);

			$response['message'] = "Oracle Account has been successfully unlocked.";
			$response['status'] = 'success';
		} else {
			$response['message'] = "There was a problem processing the Oracle Account unlocked.";
			$response['status'] = 'error';
		}
	} elseif( $person->oracle_account_status == 'OPEN' ) {
		if( PSU::db('banner')->Execute("ALTER USER ".$args['username']." ACCOUNT LOCK") ) {
			$GLOBALS['LOG']->write('Locking Oracle Account', $args['username']);

			$response['message'] = "Oracle Account has been successfully locked.";
			$response['status'] = 'success';
		} else {
			$response['message'] = "There was a problem processing the Oracle Account locked.";
			$response['status'] = 'error';
		}//end else
	} else {
		$response['message'] = "Oracle Account cannot be locked or unlocked as it is marked as: ".$person->oracle_account_status.".";
	}//end else
}//end try
catch(Exception $e)
{
	$response['message'] = $e->getMessage();
}

//
// ajax requests end here
//
if( isset($_GET['method']) && $_GET['method'] == 'js' ) {
	header('Content-type: application/json');
	die( json_encode($response) );
}

//
// otherwise, redirect back to the user page
// 
$redirect_to = $GLOBALS['BASE_URL'];

// pass along our message
if( $response['status'] == 'success' ) {
	$_SESSION['messages'][] = $response['message'];
} else {
	$_SESSION['errors'][] = $response['message'];
}

if( isset($_GET['username']) ) {
	$redirect_to .= '/user/' . $_GET['username'];
}

PSUHTML::redirect( $redirect_to ); 
