<?php

	function returnNumberOfCallsToday(){
		global $db;

		$query = "SELECT COUNT(call_id) FROM call_log WHERE call_date = NOW()";
		$num_calls_today = $db->GetOne($query);
		
	return $num_calls_today;
	}// end function returnNumberOfCallsToday


	function returnTodaysTopCallLoggers(){
		global $db;
		
		$today = date('Y-m-d');
		$top_call_loggers = Array();
		$query = "SELECT CONCAT(SUBSTRING(call_log_employee.first_name,1,1),'.', call_log_employee.last_name) as last_name, COUNT(call_log.call_id) AS callcount FROM call_log, call_log_employee WHERE call_log.calllog_username = call_log_employee.user_name AND call_log.call_date = '$today' GROUP BY call_log.calllog_username ORDER BY callcount DESC";

		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
			  $top_call_loggers[] = $row;	
		}// end while

		return $top_call_loggers;

	}// end function returnTodaysTopCallLoggers


	function returnTopCallLoggers($time_delimit, $start_date='', $end_date='')
	{
		global $db;
		//$db->debug=true;
		
		$top_call_loggers = Array();
		$mysql_time_delimit_string = $time_delimit == 'date_range' ? processTimeDelimeter($time_delimit, $start_date, $end_date) : processTimeDelimeter($time_delimit);
		$query = "SELECT CONCAT(SUBSTRING(call_log_employee.first_name,1,1),'.', call_log_employee.last_name) as last_name, COUNT(call_log.call_id) AS callcount FROM call_log, call_log_employee WHERE call_log.calllog_username = call_log_employee.user_name ". $mysql_time_delimit_string ." GROUP BY call_log.calllog_username ORDER BY callcount DESC";

		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
			  $top_call_loggers[] = $row;	
		}// end while

		return $top_call_loggers;
	}// end function returnTodaysTopCallLoggers


	function returnSemestersTopCallLoggers(){
		global $db;

		$todays_semester = returnCurrentSemester();
		$start_date = $todays_semester['start_date'];
		$end_date = $todays_semester['end_date'];
				
		$top_call_loggers = Array();
		$query = "SELECT call_log_employee.last_name, COUNT(call_log.call_id) AS callcount FROM call_log, call_log_employee WHERE call_log.calllog_username = call_log_employee.user_name AND call_log.call_date >= '$start_date' AND call_log.call_date <= '$end_date' GROUP BY call_log.calllog_username ORDER BY callcount DESC";

		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
			  $top_call_loggers[] = $row;	
		}// end while
		
	return $top_call_loggers;
	}// end function returnTodaysTopCallLoggers

	function returnCurrentSemester(){
		
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

	function returnTopCallers($time_delimit, $start_date='', $end_date='')
	{
		global $db;

		$top_callers = Array();
		$query = "SELECT caller_username, COUNT(call_id) AS callcount FROM call_log WHERE 1 ".processTimeDelimeter($time_delimit, $start_date, $end_date)." GROUP BY caller_username ORDER BY callcount DESC LIMIT 15";

		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
			  $top_callers[] = $row;	
		}// end while
		
		return $top_callers;
	} // end function returnTopCallers

	function returnCallsByDate($time_delimit, $start_date='', $end_date='')
	{
		global $db;

		/* This query is sloppy and someone should fix it...  1=1 is so the AND put in by processTimeDelimeter doesn't break anything */
		$query = "SELECT call_date, COUNT(call_id) AS callcount FROM call_log WHERE 1=1 ".processTimeDelimeter($time_delimit, $start_date, $end_date)." GROUP BY call_date ORDER BY call_date ASC";

		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
			  $calls[] = $row;	
		}// end while
		
		return $calls;
	} // end function returnCallsByDate

	function returnCallsByType($time_delimit, $start_date='', $end_date='')
	{
		global $db;

		/* This query is also sloppy and someone should fix it...  1=1 is so the AND put in by processTimeDelimeter doesn't break anything */
		$query = "SELECT call_type, COUNT(call_id) AS callcount FROM call_log WHERE 1=1 ".processTimeDelimeter($time_delimit, $start_date, $end_date)." GROUP BY call_type ORDER BY callcount DESC";


		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
				$calls[] = $row;
		} // end while

		return $calls;
	} // end function returnCallsByType

	function returnCallsByCategory($time_delimit, $start_date='', $end_date='')
	{
		global $db;

		$query = "SELECT call_log_keywords.name AS call_category, COUNT(call_log.call_id) AS callcount FROM call_log, call_log_keywords WHERE call_log.keywords LIKE call_log_keywords.keyword ".processTimeDelimeter($time_delimit, $start_date, $end_date)." GROUP BY call_category ORDER BY callcount DESC";

		$result = $db->Execute($query);
		while($row = $result->FetchRow()){
				$calls[] = $row;
		} // end while

		return $calls;
	} // end function returnCallsByCategory

	function processTimeDelimeter($time_delimit, $start_date='', $end_date='', $db_date_field='call_log.call_date')
	{
		$mysql_delimit = '';

		if($time_delimit=='forever')
		{
			$mysql_delimit = '';
		}
		if($time_delimit == 'today')
		{
			$mysql_delimit = "AND $db_date_field = CURDATE()";
		}
		else if ($time_delimit=='' || $time_delimit=='today')
		{
			$mysql_delimit =  "AND $db_date_field = NOW()";
		}
		else if ($time_delimit=='yesterday')
		{
			$mysql_delimit = "AND $db_date_field = CURDATE() - 1";
		}
		else if ($time_delimit=='this_semester')
		{
			$month = date(m);
			if ($month == 1)
			{
				//$semester = 'winterim';
				$start_date = date('Y-01-01');
				$end_date = date('Y-01-31');
			}
			else if ($month >= 2 && $month <= 5)
			{
				//$semester == 'spring';
				$start_date = date('Y-02-01');
				$end_date = date('Y-05-30');
			}
			else if ($month >= 6 && $month <= 8)
			{
				//$semester == 'summer';
				$start_date = date('Y-06-01');
				$end_date = date('Y-08-31');
			}
			else if ($month >= 9 && $month <= 12)
			{
				//$semester == 'fall';
				$start_date = date('Y-09-01');
				$end_date = date('Y-12-31');
			}
			$mysql_delimit = "AND $db_date_field BETWEEN '$start_date' AND '$end_date'";
		}
		else if($time_delimit=='date_range')
		{
			$mysql_delimit = "AND $db_date_field BETWEEN '$start_date' AND '$end_date'";
		}

		return $mysql_delimit;

	} // end function processTimeDelimit
?>
