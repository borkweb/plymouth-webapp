<?php

require_once('PSUTools.class.php');

/**
 * CallLog API.
 */
class CallLog
{
	/**
	 * Filter the ticket list.
	 *
	 * Valid $args:
	 *
	 * @li $q \b string match text in call history
	 * @li $group \b int the assigned group
	 */
	public static function search_tickets( $args )
	{
		$args = PSU::params( $args );
		extract( $args );

		$where = array();
		$sql_args = array();

		if( $q ) {
			$where[] = 'MATCH (comments) AGAINST (?)';
			$sql_args[] = $q;
		}

		if( $group ) {
			$where[] = 'its_assigned_group = ?';
			$sql_args[] = $group;
		}

		if( $call_status ) {
			$where[] = 'call_status = ?';
			$sql_args[] = $call_status;
		}

		if( $current ) {
			$where[] = 'current = ?';
			$sql_args[] = $current;
		}

		$where_sql = implode(' AND ', $where);

		$sql = "
			SELECT *
			FROM call_history
			WHERE
				$where_sql
			ORDER BY
				date_assigned DESC,
				time_assigned DESC
		";
		
		return PSU::db('calllog')->GetAll($sql, $sql_args);
	}//end search_tickets

	public static function simple_search( $search, $type = 'all' ) {
		global $db;

		$phonebook_search = array('all','name_first','name_last','email','phone');

		$search_data = array();
		$search_data['search_string'] = $_SESSION['search_string'] = stripslashes( $search );
		$search_data['search_type'] = $_SESSION['search_type'] = $type;

		if(in_array($type, $phonebook_search)) {
			$search_results = phonebookSearch($search, $type);
		}

		switch( $search_data['search_type'] ) {
			case 'closed':
				$sql = "
					SELECT * 
						FROM call_view 
					 WHERE caller_username = ?
						 AND current='1' 
						 AND call_status = 'closed' 
					 ORDER BY call_date DESC, 
								 call_time DESC
				";
				$results = $db->GetAll( $sql, array( $search_data['search_string'] ) );
				foreach($results as $key) {
					$key['comments'] = substr($key['comments'],0,23).'...';
					$search_data['results'][] = array(
						'key' => $key,
						'search_string' => $search_data['search_string'],
						'search_type' => $search_data['search_type'],
					);
				}

				$search_data['fields'][1] = 'Call ID';
				$search_data['fields'][2] = 'Name';
				$search_data['fields'][3] = 'Call Date';
				$search_data['fields'][4] = 'Call Time';
				$search_data['fields'][5] = 'Comments';
				$search_data['search_results_text'] = 'Calls By';
			break;
			case 'computer':
				$hardware = searchHardwareInformation($search_data['search_string']);
				$username = explode("@", $hardware[0]['email']);
				$username = $username[0];
				foreach ($hardware as $hardware_info){
					$search_data['results'][] = array(
						'HW_Key' => $hardware_info['id'],
						'HW_IPName' => $hardware_info['computer_name'],
						'HW_Username' => $username,
						'MACAddress' => $hardware_info['mac_address'],
						'IPAddress' => $hardware_info['ip_address'],
						'search_string' => $search_data['search_string'],
						'location' => $hardware_info['NodeName'],
						'search_type' => $search_data['search_type'],
					);
				}

				$search_data['fields'][1] = 'Computer Name';
				$search_data['fields'][2] = 'MAC Address';
				$search_data['fields'][3] = 'IP Address';
				$search_data['fields'][4] = 'Location';
				$search_data['search_results_text'] = 'Computer Name';
			break;
			case 'ip':
				$HardwareInfo = $db->GetAll("SELECT * FROM hardware_inventory WHERE ip_address = ?", array($search_data['search_string']));
				$username = explode("@", $HardwareInfo[0]['email']);
				$username = $username[0];
				$search_results = phonebookSearch($username);
				$search_results = current($search_results);
				$search_data['results'][] = array(
					'HW_Key' => $HardwareInfo[0]['id'],
					'HW_IPName' => $HardwareInfo[0]['computer_name'],
					'HW_Username' => $search_results['email'],
					'HW_Name' => $search_results['name_full'],
					'MACAddress' => $HardwareInfo[0]['mac_address'],
					'IPAddress' => $HardwareInfo[0]['ip_address'],
					'search_string' => $search_data['search_string'],
					'search_type' => $search_data['search_type'],
				);

				$search_data['fields'][1] = 'IP Address';
				$search_data['fields'][2] = 'Name';
				$search_data['fields'][3] = 'Username';
				$search_data['fields'][4] = 'MAC Address';
				$search_data['fields'][5] = 'Computer Name';
				$search_data['search_results_text'] = 'IP Address';
			break;
			case 'mac':
				$HardwareInfo = $db->GetAll("SELECT * FROM hardware_inventory WHERE upper(mac_address) = ?", array(strtoupper($search_data['search_string'])));
				$username = explode("@", $HardwareInfo[0]['email']);
				$username = $username[0];
				$search_results = phonebookSearch($username);
				$search_results = current($search_results);
				$search_data['results'][] = array(
					'HW_Key' => $HardwareInfo[0]['id'],
					'HW_IPName' => $HardwareInfo[0]['computer_name'],
					'HW_Username' => $search_results['email'],
					'HW_Name' => $search_results['name_full'],
					'MACAddress' => $HardwareInfo[0]['mac_address'],
					'IPAddress' => $HardwareInfo[0]['ip_address'],
					'search_string' => $search_data['search_string'],
					'search_type' => $search_data['search_type'],
				);

				$search_data['fields'][1] = 'MAC Address';
				$search_data['fields'][2] = 'Name';
				$search_data['fields'][3] = 'Username';
				$search_data['fields'][4] = 'Computer Name';
				$search_data['search_results_text'] = 'MAC Address';
			break;
			case 'ticket':
				$ticket_number_results = searchTicketNumber($search_data['search_string']);
				if ($ticket_number_results['call_id'] != '') {
					$search_data['results'][] = array(
						'call_id' => $ticket_number_results['call_id'],
						'caller_username' => $ticket_number_results['caller_username'],
						'caller_first_name' => $ticket_number_results['caller_first_name'],
						'caller_last_name' => $ticket_number_results['caller_last_name'],
						'caller_phone_number' => $ticket_number_results['caller_phone_number'],
						'call_date' => $ticket_number_results['call_date'],
						'call_time' => $ticket_number_results['call_time'],
						'calllog_username' => $ticket_number_results['calllog_username'],
						'search_string' => $search_data['search_string'],
						'search_type' => $search_data['search_type'],
						$search_data['search_type'] . '_selected' => 'SELECTED',
					);
				}
				else
				{
					$tpl->assign('no_ticket', 'NO TICKET FOUND');
					$tpl->parse('main.searchResults.no_ticket');
				}
				$search_data['fields'][1] = 'Call ID';
				$search_data['fields'][2] = 'Name';
				$search_data['fields'][3] = 'Username';
				$search_data['fields'][4] = 'Call Date/Time';
				$search_data['fields'][5] = 'Call Log User';
				$search_data['search_results_text'] = 'Ticket #';
			break;
			case 'user':
				$results = $db->GetAll("SELECT * FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_log.calllog_username = '$search_data[search_string]' AND current='1' ORDER BY call_date DESC, call_time DESC");
				foreach($results as $key){
					if($key['comments'] != ""){
						$key['comments'] = substr($key['comments'],0,23).'...';
					}else{
						$key['comments'] = "Closed on Submit";
					}
					$search_data['results'][] = array(
						'key' => $key,
						'search_string' => $search_data['search_string'],
						'search_type' => $search_data['search_type'],
					);
				}

				$search_data['fields'][1] = 'Call ID';
				$search_data['fields'][2] = 'Name';
				$search_data['fields'][3] = 'Call Date';
				$search_data['fields'][4] = 'Call Time';
				$search_data['fields'][5] = 'Comments';
				$search_data['search_results_text'] = 'Calls By';
			break;
			case 'wp_id':
			case 'all':
			case 'name_last':
			case 'name_first':
			case 'email':
			case 'phone':
				if( $search_data['search_type'] == 'wp_id' ) {
					$sql = "
						SELECT * 
							FROM wp_users 
						 WHERE user_login LIKE ?
					";
					$search_results = PSU::db('connect')->GetAll($sql, array( $search_data['search_string'].'%' ));
					foreach( $search_results as &$record ) {
						$person = new PSUPerson( $record['user_login'] );
						$record['identifier'] = $record['wp_id'] = $person->wp_id;
						$record['name_full'] = $person->formatName('f l');
						$record['email'] = $person->username;
						$record['dept'] = 'Family Portal';
						$person->destroy();
						unset($person);
					}//end foreach
				}//end if

				foreach($search_results as $k=>$key) {
					$class_prepend = '';
					
					if($key['email']) {
						$display = "user_info";

						if( !$key['identifier'] ) {
							$key['identifier'] = $key['email'];
						}//end if
					}//end if
					else {
						$display = "no_username";

						if( !$key['identifier'] ) {
							$key['identifier'] = $key['pidm'];
						}//end if
					}//end else
					
					$portal_roles = $GLOBALS['portal']->getRoles($key['email']);
					if(is_array($portal_roles) && in_array('alumni',$portal_roles)) {
						$class_prepend = 'Alumni'.(($class_prepend)? '/ '.$class_prepend : '');
					}//end if
					
					if(is_array($portal_roles) && in_array('student_account_active',$portal_roles)) {
						$class_prepend = 'Student'.(($class_prepend)? '/ '.$class_prepend : '');
					}//end if
					
					$key['dept'] = $class_prepend.(($key['dept'])? '/ '.$key['dept'] : '');

					if( $key['pidm'] || $key['username'] || $key['wp_id'] ) {
						$where = array();
						$args = array();

						if( $key['pidm'] ) {
							$where[] = "call_log.pidm = ?";
							$args[] = $key['pidm'];
						}//end if

						if( $key['username'] ) {
							$where[] = "call_log.caller_username = ?";
							$args[] = $key['username'];
						}//end if

						if( $key['wp_id'] ) {
							$where[] = "call_log.wp_id = ?";
							$args[] = $key['wp_id'];
						}//end if

						$where = implode(" OR ", $where);
						
						$getOpenCallInfo = $db->GetOne("SELECT * FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_history.call_status = 'open' AND ({$where}) AND call_history.current='1'", $args);
						
						$num_open_calls = $db->GetOne("SELECT count(*) FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_history.call_status = 'open' AND ({$where}) AND call_history.current='1'", $args);
						if ($num_open_calls >= 1) {
							$key['call_id'] = $getOpenCallInfo;
							$key['open_call'] = "(".$num_open_calls." Open)";
						}//end if

						if ($key['major'] && $key['title']){
							$key['major_title'] = substr($key['major'].' / '.$key['title'], 0, 20);
							$key['major_title_full'] = $key['major'].' / '.$key['title'];
						}else{
							$key['major_title'] = substr($key['major'].' '.$key['title'], 0, 20);
							$key['major_title_full'] = $key['major'].' '.$key['title'];
						}//end else
					}//end if

					$search_data['results'][] = array(
						'key' => $key,
						'search_string' => $search_data['search_string'],
						'search_type' => $search_data['search_type'],
					);
				}// end foreach

				$search_data['fields'][1] = 'Name';
				$search_data['fields'][2] = 'Username';
				$search_data['fields'][3] = 'Phone';
				$search_data['fields'][4] = 'Major/Title';
				$search_data['fields'][5] = 'Class/Dept';

				switch($search_data['search_type']) {
					case 'email':
						$search_data['search_results_text'] = 'User Name';
						$search_data['five_selected'] = 'SELECTED';
					break;
					case 'name_last':
						$search_data['search_results_text'] = 'Last Name';
						$search_data['2_selected'] = 'SELECTED';
					break;
					case 'name_first':
						$search_data['search_results_text'] = 'First Name';
						$search_data['1_selected'] = 'SELECTED';
					break;
				}//end switch
			break;
		}//end switch

		return $search_data;
	}//end simple_search
}//end CallLog
