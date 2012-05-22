<?php
require_once('PrintUser.class.php');

if(in_array($_SESSION['tlc_position'], $_SESSION['priv_users']) || $_SESSION['tlc_position'] == 'shift_leader' || $_SESSION['tlc_position'] == 'supervisor') {
	$print_user = new PrintUser($_GET['pidm']);
	
	if($print_user->username && $_GET['action'] == 'update') {
		$print_increased = false;
		$increase = $_GET['fund_increase'];
	
		if(in_array($increase, array(-20, -10, -5, -1, -0.1, 0.1, 1, 5, 10, 20))) {
			$print_increased = $print_user->adjustBalance($increase);
			$person = new PSUPerson($_GET['pidm']);
			
			if($print_increased === true) {
				$call_data = array(
					'call_log_username' => $_SESSION['username'],
					'caller_first_name' => $person->formatName('f'),
					'caller_last_name' => $person->formatName('l'),
					'caller_user_name' => $person->username,
					'call_status' => 'closed',
					'call_priority' => 'normal',
					'problem_details' => 'Added '.number_format($increase,2).' in print funds',
					'keywords_list' => 'pquota'
				);
		
				$call_location = $GLOBALS['new_call']->returnCallLoggedFromLocation($_SERVER['REMOTE_ADDR']);		
				$GLOBALS['new_call']->addNewCall($call_data, $call_location);
			
				$call_added='call_added';
				echo number_format($print_user->balance + $increase, 2);
			}//end if
		}//end if
		else {
			echo (isset($print_user->balance)) ? number_format($print_user->balance,2) : number_format(20,2);
		}//end else
	}//end if
}//end if
else {
	echo 'invalid_privs';
}//end else
