<?php

	function displayLoginHistory($calllog_username, $sort_by=''){
		global $db;
			$login_history = Array();
			if($sort_by != ''){
			   switch($sort_by){
				   case 'ip_address':
						$query = "SELECT * FROM employee_login_history WHERE user_name = '$calllog_username' ORDER BY ip_address";
						break;
				   case 'host_name':
					   $query = "SELECT * FROM employee_login_history WHERE user_name = '$calllog_username' ORDER BY host_name";
					   break;
				   case 'login_date':
					   $query = "SELECT * FROM employee_login_history WHERE user_name = '$calllog_username' ORDER BY login_date";
					   break;
				   default:
					   $query = "SELECT * FROM employee_login_history WHERE user_name = '$calllog_username' ORDER BY login_date";
					   break;
			   }// end switch
			}// end if
			else{
				$query = "SELECT * FROM employee_login_history WHERE user_name = '$calllog_username' ORDER BY login_date";
			}
			$result = $db->Execute($query);
			while($row = $result->FetchRow()){
				  $login_history[] = $row;
			}// end while

		return $login_history;
	}// end function displayLoginHistory

	function displayLoggedCalls($calllog_username, $sort_by='', $when=''){
		global $db;
		
		$pidm = $GLOBALS['BannerIDM']->getIdentifier($calllog_username, 'login_name', 'pid');
		
		$logged_calls = Array();

		if($sort_by == 'date'){
		   $query = "SELECT * FROM call_log WHERE (pidm = '$pidm' OR calllog_username = '{$calllog_username}') ORDER BY call_date";
		}// end if
		else
		if($sort_by == 'caller_last_name'){
		   $query = "SELECT * FROM call_log WHERE (pidm = '$pidm' OR calllog_username = '{$calllog_username}') ORDER BY caller_last_name";
		}// end if
		else{
		   $query = "SELECT * FROM call_log WHERE (pidm = '$pidm' OR calllog_username = '{$calllog_username}') ORDER BY call_date";
		}
		$result = $db->Execute($query);
		while($info = $result->FetchRow()){
			  $logged_calls[] = $info;
		}// end while
	
		return $logged_calls;
	}// end function displayLoggedCalls

	function displayResolvedCalls($calllog_username, $when=''){
		global $db;

		/*	
		$when is an array that describes the following:
			- Start month
			- Start year
			- End month
			- End year
		Those things describe the time period the resolved calls 
		belong to.  This functionality has yet to be implemented.
		*/

	}// end function displayResolvedCalls


	function returnNumberOfLifeTimeCallsLogged($calllog_username){
		global $db;
		$pidm = $GLOBALS['BannerIDM']->getIdentifier($calllog_username, 'login_name', 'pid');
		$query = "SELECT COUNT(call_id) FROM call_log WHERE (pidm = '$pidm' OR calllog_username = '{$calllog_username}')";
		if($num_calls = $db->GetOne($query)){
		   return $num_calls;
		}// end if
		else{
		   $_SESSION['user_message'] = 'Error selecting user\'s calls';
		}

	}// end function returnNumberOfCallsLoggged


	function returnNumberOfLifeTimeCallsResolved($calllog_username){
		global $db;
		
		$calls_resolved = Array();
		$query = "SELECT COUNT(id) FROM call_history WHERE tlc_assigned_to = '$calllog_username' AND call_status = 'closed'";
		$calls_resolved['tlc_assigned_to'] = $db->GetOne($query);

		return $calls_resolved;
	}// end function returnNumberOfCallsResolved


	function returnNumberOfCallsLoggedThisSemester($calllog_username){
		global $db;
		$pidm = $GLOBALS['BannerIDM']->getIdentifier($calllog_username, 'login_name', 'pid');
		$todays_semester = getTodaysSemester();
		$start_date = $todays_semester['start_date'];
		$end_date = $todays_semester['end_date'];
		
		$query = "SELECT COUNT(call_id) FROM call_log WHERE (pidm = '$pidm' OR calllog_username = '{$calllog_username}') AND call_date >= '$start_date' AND call_date <= '$end_date'";
		
		$num_calls_this_semester = $db->GetOne($query);
		
		return $num_calls_this_semester;
	}// end function returnNumberOfCallsLoggedThisSemester

	function returnNumberOfCallsResolvedThisSemester($calllog_username){
		global $db;
		
		$calls_resolved_this_semester = Array();
		$todays_semester = getTodaysSemester();
		$start_date = $todays_semester['start_date'];
		$end_date = $todays_semester['end_date'];

		$query = "SELECT COUNT(id) FROM call_history WHERE tlc_assigned_to = '$calllog_username' AND call_status = 'closed' AND date_assigned >= '$start_date' AND date_assigned <= '$end_date'";
		$calls_resolved_this_semester['tlc_assigned_to'] = $db->GetOne($query);

		return $calls_resolved_this_semester;
	}// end function returnNumberOfCallsResolvedThisSemester


	function returnNumberOfTotalCallLogCalls(){
		global $db;

		$query = "SELECT COUNT(call_id) FROM call_log";
		return $db->GetOne($query);

	}// end function returnNumberOfTotalCallLogCalls

	
	function getTodaysSemester(){
		
		$todays_semester = Array();

		/*
			Get current year and month, the semesters are divided as follows:
			1 - Fall:	  September (9), October (10), November (11), December (12)
			2 - Winterim: January (1)
			3 - Spring:   February (2), March (3), April (4), May (5)
			3 - Summer:   June (6), July (7), August (8)

			In call_log table, call_date looks like 2003-11-29
		*/
		$start_date = '';
		$end_date = '';
		
		$todays_year = date('Y');		  // 2003
		$todays_month = date('m');		  // 11
		$todays_end_date = date('Y-m-d'); // 2003-11-29	

		$summer_semester = Array('06', '07', '08');
		$fall_semester = Array('09', '10', '11', '12');
		$winterim_semester = Array('01');
		$spring_semester = Array('02', '03', '04', '05');

		if(in_array($todays_month, $spring_semester)){
		   $start_date = $todays_year.'-02-01';
		   $end_date = $todays_year.'-06-01';
		}// end if
		else
		if(in_array($todays_month, $summer_semester)){
		   $start_date = $todays_year.'-06-01';
		   $end_date = $todays_year.'-09-01';
		}// end if
		else
		if(in_array($todays_month, $fall_semester)){
		   $start_date = $todays_year.'-09-01';
		   $end_date = $todays_year.'-12-31';
		}// end if
		else
		if(in_array($todays_month, $winterim_semester)){
		   $start_date = $todays_year.'-01-01';
		   $end_date = $todays_year.'01-31';
		}// end if

		$todays_semester['start_date'] = $start_date;
		$todays_semester['end_date'] = $end_date;

		return $todays_semester;
	}// end function getTodaysSemester
