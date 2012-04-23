<?php
try
{
	if(!IDMObject::authZ('permission', 'ape_wp_email_reset'))
	{
		throw new Exception('You are not authorized to reset profiles.');
	}

	require_once '/web/connect.plymouth.edu/wp-includes/registration.php';

	$person = new PSUPerson($_GET['identifier']);
	if(!$person->pidm)
	{
		throw new Exception('Could not load person for pidm: '.$_GET['pidm']);
	}//end if

	$user = get_userdatabypidm( $person->pidm );

	update_usermeta( $user->ID, 'psuname', $person->login_name );
	delete_usermeta( $user->ID, 'ac_pwreset' );

	$response['message'] = 'WP psuname has been synched.';

	$email = trim($_GET['email']);

	if($email)
	{
		if( $_GET['type'] == 'primary' ) {
			$user_data = array(
				'ID' => $user->ID,
				'user_email' => $email
			);

			$which = "Email";
			$old_email = $user->user_email;
			$result = wp_update_user( (array)$user_data );
		} else {
			$which = "Alt. email";
			$old_email = $user->email_alt;
			$result = update_usermeta( $user->ID, 'email_alt', $email );
		}

		if( $result ) {
			$response['message'] .= sprintf(' %s changed from "%s" to "%s"', $which, $old_email, $email);
		}//end if
	}//end if

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

if( isset($_GET['identifier']) )
{
	$redirect_to .= '/user/' . $_GET['identifier'];
}

PSUHTML::redirect( $redirect_to ); 
