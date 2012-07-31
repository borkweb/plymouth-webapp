<?php

function returnOpenCalls( $which, $caller_user_name='', $sort_by='', $what='*' ) {
	// zbt: 7/30/12 - this function is here for backwards compatibility
	return getOpenCalls( array(
								'which' => $which,
								'who' => $caller_user_name ?: $_SESSION['username'],
								'what' => $what,
								'sort_by' => $sort_by,
							) 
						);
}

function getOpenCalls( $options = array() ) {
	
	/* Options include the following:
		which: 
			all - all open calls
			mygroup - all open calls in groups that option['calllog_username'] is in
			unassigned - calls that are currently not assigned to a user or group
			caller - open calls for $option['caller_user_name']
			today - calls opened today
			my_opened - calls that I opened
			my - calls assigned to me, or opted into seeing via high priority groups setting
		who: 
			should contain the the username of the person you are searching on (could be caller or calllog_user)
		what: 
			a comma separated list of fields to fetch, defaults to * if not provided
		sort_by:
			call_date - when the call was created
			call_updated - when the call was last updated
	*/
	
	$options['what'] = $options['what'] ?: '*';
	
	$query = "SELECT {$options['what']} FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_history.call_status='open'";


	switch($options['which']) {
		case '':
		case 'none':
		case 'all':
			$query = "SELECT {$options['what']} 
						FROM call_log 
							LEFT JOIN call_history ON call_log.call_id = call_history.call_id 
							LEFT JOIN itsgroups ON its_assigned_group = itsgroups.itsgroupid 
						WHERE call_history.call_status = 'open' 
							AND (
								hide_in_all_calls != '1' 
								OR hide_in_all_calls IS NULL
								)";
			break;
		case 'mygroup':
			$query = "SELECT {$options['what']} 
						FROM call_log, call_history 
						WHERE call_log.call_id = call_history.call_id 
							AND call_history.its_assigned_group='{$options['who']}' 
							AND call_history.call_status='open'";
			break;
		case 'unassigned':
			$query .= " AND tlc_assigned_to='unassigned' AND (its_assigned_group='0' || its_assigned_group='unassigned' || its_assigned_group='')";
			break;
		case 'caller':
			$person = new PSUPerson( $options['who'] );
			$query .= " AND (call_log.wp_id = '{$person->wp_id}' OR call_log.pidm = {$person->pidm} OR call_log.caller_username='{$options['who']}')";
			break;
		case 'today':
			$query .= " AND call_log.call_date=NOW()";
			break;
		case 'my_opened':
			$query .= " AND call_log.calllog_username='{$options['who']}' AND call_history.call_status='open'";
			break;
		case 'my':
			$query .= " AND ( call_history.tlc_assigned_to='{$options['who']}'";
			
			$high_priority_groups = implode( ',', User::getHighPriorityGroups( false, $options['who'] ) );
			if( $high_priority_groups ) {
				$query .= " OR ( call_history.its_assigned_group IN ($high_priority_groups) AND call_history.call_priority = 'high' )";
			}
			
			$query .= " )";
			break;
		default:
			$query .= " AND call_history.tlc_assigned_to='{$options['who']}'";
			break;

	}// end switch

	$query .= " AND call_history.current='1'";
	
	if( !$options['sort_by'] || $options['sort_by'] == 'call_date' ) {
		$options['sort_by'] = 'call_date, call_time';
	} 
	elseif( $options['sort_by'] == 'call_updated' ) {
		$options['sort_by'] = 'date_assigned, time_assigned';
	}

	$query .= " ORDER BY {$options['sort_by']} ASC";
	
	$calls =  PSU::db('calllog')->GetAll($query);
	
	foreach( $calls as &$call ) {
		// needed for the template, but a bit redundant
		$call['call_title'] = $call['title'];
			
		// determine an assigned_to that combines person and queue/group
		$groupArray = getGroupInfo( $call['its_assigned_group'] );
		if ( ( $call['its_assigned_group'] != 0 ) || ( $groupArray[1] != '' ) ) {
			if($call['tlc_assigned_to'] != 'unassigned'){
				$call['assigned_to']['group'] = $groupArray[1];
				$call['assigned_to'][] = $call['tlc_assigned_to'];
			}
			else{
				$call['assigned_to']['group'] = $groupArray[1];
			}
		}
		elseif( $call['tlc_assigned_to'] != '' ) {
			$call['assigned_to'][] = $call['tlc_assigned_to'];
		}
		else{
			$call['assigned_to'][] = 'None';
		}

		$call['building_name'] = getBuildingName( $call['location_building_id'] );
		
		if($call['date_assigned']) {
			$assign_datetime = $call['date_assigned'].' '.$call['time_assigned'];
			$call['activity_datetime'] = time() - strtotime( $assign_datetime );
			$call['date_assigned'] = date( 'M j, Y', strtotime( $assign_datetime ) );
			$call['time_assigned'] = date( 'g:i a', strtotime( $assign_datetime ) );
		}//end if

		$call['call_activity_diff'] = \PSU::date_diff( $time, strtotime( $assign_datetime ), 'simple' );

		$call['call_summary'] = substr( $call['comments'], 0, 100) . ( ( strlen( $call['comments'] ) > 100 ) ? '...' : '' );

		$call['show_comments'] = str_replace( "\"", "&#34", addslashes( substr( strip_tags( str_replace( array("\n","\t","\r"), '', $call['comments'] ) ),0, 30 ) ) );

		$call_datetime = $call['call_date'] . ' ' . $call['call_time'];
		$call_open_time[$call['call_id']] = time() - strtotime( $call_datetime );
		$call['call_date'] = date('M j, Y', strtotime( $call_datetime ) );
		$call['call_time'] = date('g:i a', strtotime( $call_datetime ) );
		if( $call['feelings_face'] ) {
			$call['feelings_face'] = '<br/><img src="/webapp/feedback/templates/images/feedback-' . $call['feelings_face'] . '.png" class="feedback-face" title="' . $call['feelings'] . '"/>';
		}//end if

		// If the time that the call has been open (call_open_time) is greater than one week (604800 seconds)
		if ( $call_open_time[$call['call_id']] > 604800 ) {
			// Set a call age status variable and mark it as old
			$call['call_age_status'] = 'old';
		}
		else {
			// Otherwise, mark it as normal
			$call['call_age_status'] = 'normal';
		}

		// If the time since the call has been updated (activity_datetime) is greater than one week (604800 seconds)
		if ( $call['activity_datetime'] > 604800 ) {
			// Set an activity  age status variable and mark it as old
			$call['activity_age_status'] = 'old';
		}
		else {
			// Otherwise, mark it as normal
			$call['activity_age_status'] = 'normal';
		}
		
		$identifier = PSU::nvl( $call['caller_username'], $call['wp_id'], $call['pidm'] );
		//grabs the person data for the call
		$person = (array) $GLOBALS['user']->getCallerData( $identifier );
		//overrides the username that was saved in the call with the username that was found from PSUPerson
		//this is to prevent ~500 calls displaying improper information
		//that were created with wp_ids instead of usernames as the username identifier
		$call['caller_username'] = $person['username'] ?: $person['identifier'];
		
		//merges the person array and single call(row) array
		$call = array_merge( $call, $person );
	} // end foreach
	
	return $calls;
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
