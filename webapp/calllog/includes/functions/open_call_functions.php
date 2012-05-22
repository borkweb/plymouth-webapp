<?php

function returnOpenCalls($options, $caller_user_name='', $sort_by=""){
	global $db;
	$result = array(); 
	/*
	NOTE:
		$options can equal the following three things:
		'none'		 - No options, returns all open calls.
		
		'unassigned' - Unassigned calls, calls that no one has been assigned to.
		'm_rondea'	 - User name, returns all open calls for the person with the user name m_rondea,
					   I don't know who m_rondea is, but I bet he's super cool.
	*/

	if ($options == "mygroup"){
		$query = "SELECT * FROM call_log, call_history WHERE call_log.call_id = call_history.call_id";
	}else if ($options == 'all'){
		$query = "SELECT * FROM call_log LEFT JOIN call_history ON call_log.call_id = call_history.call_id LEFT JOIN itsgroups ON its_assigned_group = itsgroups.itsgroupid WHERE call_history.call_status = 'open' AND (hide_in_all_calls != '1' OR hide_in_all_calls IS NULL)";
	}else{
		$query = "SELECT * FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_history.call_status='open'";
	}

	$person = new PSUPerson( $caller_user_name );
	$pidm = $person->pidm;
	$wp_id = $person->wp_id;
	
	switch($options){
		case '':
			break;
		case 'none':
			break;
		case 'all':
			break;
		case 'unassigned':
			$query .= " AND tlc_assigned_to='unassigned' AND (its_assigned_group='0' || its_assigned_group='unassigned' || its_assigned_group='')";
			break;
		case 'caller':
			$query .= " AND (call_log.wp_id = '$wp_id' OR call_log.pidm = $pidm OR call_log.caller_username='$caller_user_name')";
			break;
		case 'today':
			$query .= " AND call_log.call_date=NOW()";
			break;
		case 'mygroup':
			$query .= " AND call_history.its_assigned_group='$caller_user_name' AND call_history.call_status='open'";
			break;
		case 'my_opened':
			$query .= " AND call_log.calllog_username='$_SESSION[username]' AND call_history.call_status='open'";
			break;
		default:
			$user_name = $options;
			$query .= " AND call_history.tlc_assigned_to='$_SESSION[username]'";
			break;

	}// end switch

	$query .= " AND current='1'";
	if( !$sort_by || $sort_by == 'call_date' ) {
		$sort_by = 'call_date, call_time';
	} elseif( $sort_by == 'call_updated' ) {
		$sort_by = 'date_assigned, time_assigned';
	}

	$query .= " ORDER BY $sort_by ASC";

	$result = $db->GetAll($query);

	return $result;
}// end function returnOpenCalls

/*
	UPDATE: 12/19/03 - I just noticed something, I was nearly shocked.
	Notice that the function directly above is called, "returnCallerOpenCalls(),"
	and the function directly below is called, "returnCalls()," now, what I don't
	understand is what the hell the difference is, and I wondered why I have two
	functions that [almost] do one thing--do the same thing, but I'm not sure 
	from what page the returnCalls() function is used, but it seems that by making
	a minor adjustment, the functin above me and below me might as well be the 
	same function, maybe they shouldn't be the same function, I don't know.

	Zach: not sure his plan, but sorta fixed it up.
*/

function returnCallerOpenCalls($user_name='', $notused1='', $notused2=''){
	return returnOpenCalls('',$user_name);
}// end function returnCallerOpenCalls


function returnCalls($call_type){
	return returnOpenCalls('today');
}// end function returnCalls

function displayOpenCalls($template_file, $level=""){
	$tpl = new XTemplate($template_file);
	$open_calls = getOpenCallGroups();
	foreach( $open_calls as $group ) {
		$tpl->assign('my_group', $group['id'] );
		$tpl->assign('numberOfRows', $group['num'] );
		$tpl->assign('open_call_type', $group['open_call_type'] );
		$tpl->assign('type', $group['type'] );
		$tpl->assign('title', $group['title'] );
		$tpl->assign('my_group_name', $group['my_group_name'] );
		$tpl->parse('main'.$level.'.group');
	}//end foreach

	return $tpl->text('main'.$level.'.group');
}

function getOpenCallGroups() {
	global $db;
	
	$groups = array();

	$my_calls = returnOpenCalls('my');
	$groups['my'] = array(
		'id' => 0,
		'num' => count( $my_calls ),
		'type' => 'my',
		'open_call_type' => urlencode( $_SESSION['username'] ),
		'title' => 'View Open Calls Assigned To You.',
		'my_group_name' => 'My Calls',
	);

	$my_calls = returnOpenCalls('my_opened');
	$groups['my_opened'] = array(
		'id' => 0,
		'num' => count( $my_calls ),
		'type' => 'my_opened',
		'open_call_type' => urlencode( $_SESSION['username'] ),
		'title' => 'View Open Calls By You.',
		'my_group_name' => 'Active Calls I Opened',
	);

	// gets information about the user and what groups they are in, also parsing out unassigned and all calls
	$sql = "
		SELECT * 
			FROM its_employee_groups, 
			itsgroups 
		 WHERE itsgroups.deleted = 0 
			 AND its_employee_groups.employee_id = {$GLOBALS['EMPLOYEE_INFO']['call_log_user_id']} 
			 AND itsgroups.itsgroupid = its_employee_groups.group_id 
			 AND its_employee_groups.option_id != '0' 
		 ORDER BY subgroupName ASC
	";

	if ($results = $db->Execute( $sql ) ) {
		foreach( $results as $row ) {
			$groupArray = getGroupInfo($row['itsgroupid'], $loop=1);
			$group_calls = returnOpenCalls('mygroup', $groupArray[2]);

			$groups[ $row['itsgroupid'] ] = array(
				'id' => urlencode( $row['itsgroupid'] ),
				'num' => count( $group_calls ),
				'type' => 'mygroup',
				'open_call_type' => urlencode( $row['subgroupName'] ),
				'title' => 'View Open Calls Assigned To Your Group.',
				'my_group_name' => $row['subgroupName'],
			);
		}//end foreach
	}//end if

	$unassigned_calls = returnOpenCalls('unassigned');
	$groups['unassigned'] = array(
		'id' => 0,
		'num' => count( $unassigned_calls ),
		'type' => 'unassigned',
		'open_call_type' => 'unassigned',
		'title' => 'View Unassigned Open Calls',
		'my_group_name' => 'Unassigned Calls',
	);

	$open_calls = returnOpenCalls('all');
	$groups['all'] = array(
		'id' => 0,
		'num' => count( $open_calls ),
		'type' => 'all',
		'open_call_type' => 'all',
		'title' => 'View All Open Calls',
		'my_group_name' => 'All Calls',
	);

	return $groups;
}//end getOpenCallGroups
