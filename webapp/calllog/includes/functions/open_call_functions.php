<?php

function returnOpenCalls($option, $caller_user_name='', $sort_by='', $what='*' ){
	global $user;
	$result = array(); 
	
	/*
	NOTE:
		$options can equal the following three things:
		'none'		 - No options, returns all open calls.
		
		'unassigned' - Unassigned calls, calls that no one has been assigned to.
		'm_rondea'	 - User name, returns all open calls for the person with the user name m_rondea,
					   I don't know who m_rondea is, but I bet he's super cool.
	*/
	
	if ($option == 'mygroup') {
		$query = "SELECT $what FROM call_log, call_history WHERE call_log.call_id = call_history.call_id";
	}
	elseif ($option == 'all') {
		$query = "SELECT $what FROM call_log LEFT JOIN call_history ON call_log.call_id = call_history.call_id LEFT JOIN itsgroups ON its_assigned_group = itsgroups.itsgroupid WHERE call_history.call_status = 'open' AND (hide_in_all_calls != '1' OR hide_in_all_calls IS NULL)";
	}
	else {
		$query = "SELECT $what FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_history.call_status='open'";
	}

	$person = new PSUPerson( $caller_user_name );
	$pidm = $person->pidm;
	$wp_id = $person->wp_id;
	
	switch($option) {
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
			$query .= " AND call_log.calllog_username='{$_SESSION['username']}' AND call_history.call_status='open'";
			break;
		case 'my':
			$query .= " AND ( call_history.tlc_assigned_to='{$_SESSION['username']}'";
			
			$high_priority_groups = implode( ',', $user->getHighPriorityGroups() );
			if( $high_priority_groups ) {
				$query .= " OR ( call_history.its_assigned_group IN ($high_priority_groups) AND call_history.call_priority = 'high' )";
			}
			
			$query .= " )";
			break;
		default:
			$query .= " AND call_history.tlc_assigned_to='{$_SESSION['username']}'";
			break;

	}// end switch

	$query .= " AND call_history.current='1'";
	
	if( !$sort_by || $sort_by == 'call_date' ) {
		$sort_by = 'call_date, call_time';
	} 
	elseif( $sort_by == 'call_updated' ) {
		$sort_by = 'date_assigned, time_assigned';
	}

	$query .= " ORDER BY $sort_by ASC";

	return PSU::db('calllog')->GetAll($query);
}// end function returnOpenCalls


function getOpenCallCount( $option, $caller_user_name='' ) {
	$count_arr = returnOpenCalls( $option, $caller_user_name, '', 'COUNT(call_log.call_id) count' );
	$count = isset( $count_arr[0]['count'] ) ? $count_arr[0]['count'] : 0;
	return $count;
}

function getOpenCallGroups() {
	global $user;
	
	$groups = array();

	$my_calls = getOpenCallCount( 'my' );
	$groups['my'] = array(
		'id' => 0,
		'num' => $my_calls,
		'type' => 'my',
		'open_call_type' => urlencode( $_SESSION['username'] ),
		'title' => 'View Open Calls Assigned To You.',
		'my_group_name' => 'My Calls',
	);

	$my_opened = getOpenCallCount( 'my_opened' );
	$groups['my_opened'] = array(
		'id' => 0,
		'num' => $my_opened,
		'type' => 'my_opened',
		'open_call_type' => urlencode( $_SESSION['username'] ),
		'title' => 'View Open Calls By You.',
		'my_group_name' => 'Active Calls I Opened',
	);

	$user_groups = $user->getUserGroups();
	foreach( $user_groups as $group ) {
		$group_calls = getOpenCallCount( 'mygroup', $group['group_id'] );
		$groups[ $group['group_id'] ] = array(
			'id' => urlencode( $group['group_id'] ),
			'num' => $group_calls,
			'type' => $group['subgroup'],
			'open_call_type' => urlencode( $group['name'] ),
			'title' => 'View Open Calls Assigned To Your Group.',
			'my_group_name' => $group['name'],
		);
	}//end foreach

	$unassigned_calls = getOpenCallCount( 'unassigned' );
	$groups['unassigned'] = array(
		'id' => 0,
		'num' => $unassigned_calls,
		'type' => 'unassigned',
		'open_call_type' => 'unassigned',
		'title' => 'View Unassigned Open Calls',
		'my_group_name' => 'Unassigned Calls',
	);

	$open_calls = getOpenCallCount( 'all' );
	$groups['all'] = array(
		'id' => 0,
		'num' => $open_calls,
		'type' => 'all',
		'open_call_type' => 'all',
		'title' => 'View All Open Calls',
		'my_group_name' => 'All Calls',
	);

	return $groups;
}//end getOpenCallGroups
