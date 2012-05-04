<?php

function getEmployeeCalls($employee_username){
	global $db;

	$pidm = $GLOBALS['BannerIDM']->getIdentifier($employee_username, 'login_name', 'pid');

	$getCalls = $db->GetAll("SELECT * FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND (call_log.pidm = $pidm OR call_log.calllog_username = '$employee_username') AND current='1' ORDER BY call_date DESC, call_time DESC LIMIT 200");
	prePrint($getCalls);
}

?>