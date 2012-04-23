<?php

/**
 * Tool for account locking in the post Luminis world.
 * NOTE - This tool will not handle user spoofing.
 */

$pidm = (int)$_GET['pidm'];
$redirect_id = $pidm;
$lock = (bool)$_GET['lock'];
$reason = isset($_GET['reason']) ? $_GET['reason'] : null;

try
{
	if(!$GLOBALS['ape']->canResetPassword())
	{
		throw new Exception('You are not allowed to modify account locks.');
	}

	$person = new PSUPerson($pidm);
	$redirect_id = PSU::nvl($person->id,$person->wp_id);

	if($lock)
	{

		$GLOBALS['LOG']->write('Locking account', $person->login_name);

		$message = "Account for {$person->login_name} has been locked.";

		$reason .= ' (auto-opened via APE)';

		if( $call_id = APE::create_ticket( $person->login_name, 'Account Locked', $reason, array( 'call_source' => 'APE Locked Account' ) ) ) {
			$message .= ' Ticket #<a href="http://go.plymouth.edu/log/'.$call_id.'">'.$call_id.'</a> opened.';	
		}//end if
 
		$_SESSION['messages'][] = $message;

		$keys = array('added', 'sourced_id', 'password', 'pidm', 'login_name', 'fullname', 'reason', 'locker_pidm');
		$values = array('NOW()', '?', '?', '?', '?', '?', '?', '?');
		$args = array( $person->sourced_id, ' ', $pidm, $person->login_name, $person->formatName('f l'), $reason, $_SESSION['pidm'] );

		$sql = "
		  INSERT INTO ape_support_locks (" . implode(', ', $keys) . ")
		  VALUES (" . implode(', ', $values) . ")
		";

		if(!PSU::db('myplymouth')->Execute($sql, $args))
		{
			throw new Exception( PSU::db('myplymouth')->errorMsg() );
		}

		$person->lock_wp_account();
	}
	else
	{

		$GLOBALS['LOG']->write('Unlocking account', $person->login_name);
		$message = "Account for {$person->login_name} has been unlocked.";

		if( $call = APE::find_ticket_by_source( $person->wp_id ? $person->wp_id : $person->login_name, 'Account Locked' ) ) {

			$call_id = $call['call_id'];
			$reason = $_GET['reason']."\n\n".'Account is now unlocked. (auto-updated via APE)';

			if( APE::update_ticket( $call_id, $reason ) ) {
				$message .= ' Ticket #<a href="http://go.plymouth.edu/log/'.$call_id.'">'.$call_id.'</a> has been updated. It has <strong>not</strong> been closed.';	
			}//end if
		}//end if 

		$_SESSION['messages'][] = $message;

		$args = array();

		$sql = "DELETE FROM ape_support_locks WHERE pidm = ?";

		$args[] = $person->pidm;
		PSU::db('myplymouth')->Execute($sql, $args);

		$person->unlock_wp_account();
	}
}
catch(Exception $e)
{
	$_SESSION['errors'][] = sprintf("%s (%d)", $e->GetMessage(), $e->GetCode());
}

PSUHTML::redirect($GLOBALS['BASE_URL'] . '/user/' . $redirect_id);
