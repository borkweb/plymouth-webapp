<?php
class NewCall{
	var $db;

	function NewCall(&$db){
		$this->db=$db;
	}

	function addNewCall($new_call_form_vars, $call_location = ''){	
		$query_status = '';

		// Gets new call form variables from add_new_call.html
		$call_log = Array();
		$call_history = Array();
		// Vars going to addToCallLog--I laid them out in a way that matched the database.
		$call_log['call_id'] = '';

		$person = new PSUPerson( $new_call_form_vars['caller_user_name'] );

		$call_log['wp_id'] = $person->wp_id;
		$call_log['pidm'] = ($person->pidm) ? $person->pidm : 0;
		$call_log['caller_username'] = PSU::nvl( $person->username, $person->wp_id, $new_call_form_vars['caller_user_name'] ); //required
		$call_log['caller_first_name'] = stripslashes($person->formatName('f')); //required
		$call_log['caller_last_name'] = stripslashes($person->formatName('l')); //required
		$call_log['caller_phone_number'] = $new_call_form_vars['caller_phone_number']; //required
		$call_log['calllog_username'] = $_SESSION['username']; //required
		$call_log['call_type'] = $new_call_form_vars['resnet_check'];
		$call_log['call_time'] = 'NOW()';
		$call_log['call_date'] = 'NOW()';
		$call_log['keywords'] = stripslashes($new_call_form_vars['keywords_list']);
		$call_log['location_building_id'] = $new_call_form_vars['location_building_id'];
		$call_log['location_building_room_number'] = $new_call_form_vars['location_building_room_number'];
		$call_log['location_call_logged_from'] = $call_location;
		$call_log['title'] = stripslashes($new_call_form_vars['title']);
		$call_log['feelings'] = stripslashes($new_call_form_vars['feelings']);
		$call_log['feelings_face'] = $new_call_form_vars['feelings_face'];

		// If the new auto-incremented call_id was returned
		if($new_call_id = $this->addToCallLog($call_log)){

			// Vars going to addToCallHistory
			$call_history['id'] = '';
			$call_history['call_id'] = $new_call_id;
			$call_history['updated_by'] = $_SESSION['username'];
			$call_history['tlc_assigned_to'] = $new_call_form_vars['tlc_assigned_to'];
			if (($call_history['tlc_assigned_to'] != "") && ($call_history['tlc_assigned_to'] == "helpdesk")){
				$call_history['tlc_assigned_to'] = "";
			}
			$call_history['its_assigned_group'] = $new_call_form_vars['its_assigned_group'];
			if ($call_history['its_assigned_group'] != ""){
				
			}
			$call_history['comments'] = stripslashes($new_call_form_vars['problem_details']); //required
			$call_history['date_assigned'] = 'NOW()';
			$call_history['time_assigned'] = 'NOW()';
			$call_history['call_status'] = $new_call_form_vars['call_status']; //required
			$call_history['call_priority'] = $new_call_form_vars['call_priority']; //required
			$call_history['call_state'] = $new_call_form_vars['call_state']; //required
			$call_history['current'] = 1;
			
			$status = $this->addToCallHistory($call_history);
			if(!$status){
				$_SESSION['user_message'] = 'Error inserting new call into call_history table.';
			}
		}// end if
		else{
			$_SESSION['user_message'] = 'Error retrieving new auto-incremented call_id.';
		}

		return $new_call_id;
	}// end function addNewCall

	function addToCallLog($call_log){
		
		$query = "INSERT INTO call_log (
			`call_id`,
			`wp_id`,
		 	`pidm`,
		 	`caller_username`,
		 	`caller_first_name`, 
			`caller_last_name`, 
			`caller_phone_number`, 
			`calllog_username`, 
			`call_type`, 
			`call_time`, 
			`call_date`, 
			`keywords`, 
			`location_building_id`, 
			`location_building_room_number`, 
			`location_call_logged_from`, 
			`title`, 
			`feelings`, 
			`feelings_face`
		) VALUES (
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			NOW(),
			NOW(),
			?,
			?,
			?,
			?,
			?,
			?,
			?
		)";

		unset( $call_log['call_time'], $call_log['call_date'], $call_log['caller_wp_id'] );

		// Insert into call_log table, get new auto-incremented call_id, and return it.
		if($this->db->Execute($query, $call_log)){
		   return $this->db->Insert_ID();
		}// end if
		else{
			$_SESSION['user_message'] = 'Error insert call into call_log table, and getting the new call_id in addToCallLog().';
		}

	}// end function addToCallLog

	function addToCallHistory($call_info){		
		// mark all previous history entries as not being the current one
		$this->db->Execute("UPDATE call_history SET current='0' WHERE call_id='{$call_info['call_id']}'");

		$fields = tableFields('call_history');
		$call_info['comments'] = stripslashes( $call_info['comments'] );

		foreach($call_info as $key => $val) {
			if( in_array( $key, $fields ) ) {
				$keys.=$key.",";
				if( $val == 'NOW()' ) {
					$values .= 'NOW(),';
					unset( $call_info[ $key ] );
				} else {
					$values.="?,";
				}//end else
			}//end if
		}// end foreach

		$keys = trim( $keys, ',');
		$values = trim( $values, ',');
		PSU::dbug($values);
		$query = "INSERT INTO call_history ($keys) VALUES ($values)";

		if($this->db->Execute($query, $call_info)){
			die;
			return true;
		} else {
			$_SESSION['user_message'] = 'Error inserting new call into call_history table in addToCallHistory().';
			return false;
		}

	}// end function addToCallHistory

	public static function history( $call_id, $sort = null ) {
		global $db;

		$history = array();

		if( ! $sort ) {
			$sort = $GLOBALS['EMPLOYEE_INFO']['update_sort'];
		}//end if

		$sql = "
			SELECT * 
				FROM call_log, 
				     call_history 
			 WHERE call_log.call_id = ?
				 AND call_log.call_id = call_history.call_id 
			 ORDER BY date_assigned {$sort}, 
				 time_assigned {$sort}
		";
		if($call_details = $db->GetAll($sql, array( $call_id ))) {
			foreach ($call_details as $details) {
				$details['comments'] = preg_replace("/[\*]{114}/", "____________________________________________", $details['comments']);
				$details['comments'] = nl2br(strip_tags($details['comments']));
				$history[] = $details;
			}//end foreach
		}//end if

		return $history;
	}//end history


	function returnCallLoggedFromLocation($ip_address=''){
		$call_logged_from = '';
		if($ip_address==''){
		   $ip_address = $_SERVER['REMOTE_ADDR'];
		}else{
		   $subnet = explode('.', $ip_address);
		   $location = $subnet[2];
		   if($location == '150' || $location == '151'){
			  $call_logged_from = 'help_desk';	
		   }else if($location == '148' || $location == '149'){
			  $call_logged_from = 'library';
		   }else if($location == '82' || $location == '83'){
			  $call_logged_from = 'ITS 3rd Floor';	
		   }else if($location == '74' || $location == '75'){
			  $call_logged_from = 'Telecomm';
		   }else{
			  $call_logged_from = 'other';
		   }
		}
		return $call_logged_from;
	}// end function returnCallLoggedFromLocation


}
