<?php

$identifier = $_GET['identifier'];
$action = PSU::nvl( $_GET['action'], 'impersonate' );
$redirect_id = $identifier;
$reason = isset($_GET['reason']) ? $_GET['reason'] : null;

try
{

	if( $action == 'impersonate' ) {

		if(!$GLOBALS['ape']->canResetPassword())
		{
			throw new Exception('You are not allowed to modify account locks.');
		}

		$person = new PSUPerson($identifier);
		$redirect_id = PSU::nvl( $person->wp_id, $person->id );

		$GLOBALS['LOG']->write('Impersonating account: '.$reason, $person->login_name);

		$_SESSION['impersonate'] = TRUE;
		$_SESSION['impersonate_store'] = array(
			'wp_id' => $_SESSION['wp_id'],
			'username' => $_SESSION['username'],
			'pidm' => $_SESSION['pidm'],
			'fullname' => $_SESSION['fullname'],
		);

		$_SESSION['wp_id'] = $person->wp_id;
		$_SESSION['username'] = PSU::nvl( $person->login_name, $person->wp_id );
		$_SESSION['pidm'] = $person->pidm;
		$_SESSION['fullname'] = $person->formatName('f m l');
		$_SESSION['phpCAS']['user'] = PSU::nvl( $person->login_name, $person->wp_id );

		unset( $_SESSION['AUTHZ'] );

		if( $_SESSION['pidm'] ) {
			PSU::get('idmobject')->loadAuthZ($_SESSION['pidm']);
		}//end if

		unset( 
			$_SESSION['AUTHZ']['admin']
		);

		foreach( (array) $_SESSION['AUTHZ']['permission'] as $key => $value ) {
			if( strpos( $key, 'ape_' ) === 0 ) {
				unset( $_SESSION['AUTHZ']['permission'][ $key ] );
			}//end if
		}//end foreach

		$message = 'Now impersonating: '.$_SESSION['username'].' ('.$_SESSION['wp_id'].')';
		$_SESSION['messages'][] = $message;

	} elseif( $action == 'cancel' ) {
		
		if( isset( $_SESSION['impersonate'] ) ) {
			
			$GLOBALS['LOG']->write('Finished impersonating account', $_SESSION['username']);

			$_SESSION['wp_id'] = $_SESSION['impersonate_store']['wp_id'];
			$_SESSION['username'] = $_SESSION['impersonate_store']['username'];
			$_SESSION['pidm'] = $_SESSION['impersonate_store']['pidm'];
			$_SESSION['fullname'] = $_SESSION['impersonate_store']['fullname'];
			$_SESSION['phpCAS']['user'] = $_SESSION['impersonate_store']['username'];

			unset( $_SESSION['AUTHZ'] );
		 
			PSU::get('idmobject')->loadAuthZ($_SESSION['pidm']);

			unset( $_SESSION['impersonate_store'], $_SESSION['impersonate'] );

		}//end if

		PSU::redirect( $_SERVER['HTTP_REFERER'] );
	} else {
		throw new Exception('Stop trying to sneak in here!!!');
	}//end else
}
catch(Exception $e)
{
	$_SESSION['errors'][] = sprintf("%s (%d)", $e->GetMessage(), $e->GetCode());
}
PSUHTML::redirect($GLOBALS['BASE_URL'] . '/user/' . $redirect_id);
