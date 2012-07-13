<?php

class TrainingTracker{

	public function checklist_exists( $pidm = null, $type = null, $closed = null ){

		if (isset($pidm)){
			$sql = "SELECT pidm FROM person_checklists WHERE pidm = ? AND closed = ? AND type = ?";
			$result = \PSU::db('hr')->GetOne($sql, array($pidm, $closed, $type));

			if (!$result){
				$return_value = false;
			}
			else{
				$return_value = true;
			}
		}
		else{
			$return_value = "Error: no pidm supplied";
		}
		return $return_value;
	}//end checklist_exists

	public function checklist_type($slug){
		
		$sql = "SELECT type FROM checklist WHERE slug = ?";
		$type = PSU::db('hr')->GetOne($sql, array($slug));
	
		return $type;
	}//end checklist_type

	public function checklist_close($wpid){
		$pidm = PSUPerson::get($wpid)->pidm;
		$sql = "UPDATE person_checklists SET closed = ? WHERE closed = ? AND pidm = ?";
		$updated = PSU::db('hr')->Execute($sql, array(1, 0, $pidm));
	}

	public function checklist_open($wpid, $type){
		$pidm = PSUPerson::get($wpid)->pidm;
		$sql = "UPDATE person_checklists SET closed = ? WHERE type = ? AND pidm = ?";
		$updated = PSU::db('hr')->Execute($sql, array(0, $type, $pidm));
	}


	public function checklist_insert($pidm, $type){
		$sql = "INSERT INTO person_checklists (type, pidm, closed) VALUES (?, ?, ?)";
		$inserted = PSU::db('hr')->Execute($sql, array($type, $pidm, 0));
		return true;
	}

	public function get_comments($checklist_id){

		$sql = "SELECT notes FROM person_checklist_items WHERE response=? AND checklist_id=?";
		$comments = PSU::db('hr')->GetOne($sql, array("notes", $checklist_id));
		//if there are no saved comments this is set default
		if (!$comments){
			$comments = "Comments go here";
		}
		
		return $comments;
	}
		
	public function get_checklist_id($identifier){
		$pidm = \PSUPerson::get($identifier)->pidm;
		$sql = "SELECT id FROM person_checklists WHERE pidm=? AND closed=?";
		$checklist_id = PSU::db('hr')->GetOne($sql, array($pidm, "0"));

		return $checklist_id;
	}

	public function mail($pay, $level, $people){

		$current_pay = $pay['current'];
		$future_pay = $pay['future'];

		$current_user = $people['current'];
		$active_user = $people['active'];

		$active_user_name = $active_user->formatName("f l");

		$current_user_name = $current_user->formatName("f l"); 
		
		$usnh_id = $current_user->usnh_id;

		$email_to = "satirrell@plymouth.edu";

		$message = "$current_user_name (" . $current_user->username . ") has completed their current level of $level\nand would enjoy a pay raise from \$$current_pay to \$$future_pay.\n\nUSNH ID: $usnh_id \n\nSent by\n\t$active_user_name";

		PSU::mail($email_to,"Training Tracker - " . $current_user->formatName("f l") . " pay raise request","$message");

	}
	
	public function level_translation($user_level){
		if ($user_level == 'trainee'){
			$user_level = "Information Desk Trainee";
		}
		else if ($user_level == 'sta'){
			$user_level = "Information Desk Consultant";
		}
		else if ($user_level == 'shift_leader'){
			$user_level = "Senior Information Desk Consultant";
		}
		else if ($user_level == 'supervisor'){
			$user_level = 'Information Desk Shift Supervisor';
		}
		else {
			$user_level = "FAIL!";
		}
		return $user_level;
	}

	public function pay_translation($user_level){

		if ($user_level == 'trainee'){
			$pay = 7.25;
		}
		else if ($user_level == 'sta'){
			$pay = 7.50;
		}
		else{
			$pay = 7.75;
		}

		$future_pay = $pay + 0.25;
		$future_pay = number_format($future_pay, 2);

		$pay_info['current'] = number_format($pay, 2);
		$pay_info['future'] = $future_pay;
		
		return $pay_info;
	}


	public function get_user_level($wpid){

		$username = \PSUPerson::get($wpid)->username;
		$sql = "SELECT user_privileges FROM call_log_employee WHERE user_name=?";
		$user_level = PSU::db('calllog')->GetOne($sql, array($username));
		
		return $user_level;

	}	

	public function set_user_level($wpid, $permission){
		$sql = "UPDATE call_log_employee SET user_privileges=? WHERE user_name=?";
		PSU::db('calllog')->Execute($sql, array($permission, PSUPerson::get($wpid)->username));
	}

	public function get_teams($key = null, $type = null){
		$memcache = new \PSUMemcache('training-tracker_teams');
		if (!$key and !$type){

			if ( ! ($cached_results = $memcache->get('teams'))){

				$teams = array();
				// get all the mentors from the database
				$sql = "SELECT DISTINCT mentor FROM teams";
				$mentors = \PSU::db('hr')->GetAll($sql);

				foreach ($mentors as $mentor){
					$sql = "SELECT * FROM teams WHERE mentor = ?";
					$wpid = $mentor['mentor'];
					$team = \PSU::db('hr')->GetAll($sql, array($wpid));

					foreach($team as $people){
						$mentee_wpid = $people['mentee'];
						$mentee = \PSUPerson::get($mentee_wpid)->formatName("f l");
						$mentor_wpid = $people['mentor'];
						$mentor_name = PSUPerson::get($mentor_wpid)->formatName("f l");
						$teams["$wpid"]["mentor"]['mentor_name'] = PSUPerson::get($mentor_wpid)->formatName("f l");
						$teams["$wpid"]["mentor"]['mentor_wpid'] = $mentor_wpid;
						$teams["$wpid"]["$mentee_wpid"]['mentor_name'] = PSUPerson::get($mentor_wpid)->formatName("f l");
						$teams["$wpid"]["$mentee_wpid"]["mentor_wpid"] = $mentor_wpid;
						$teams["$wpid"]["$mentee_wpid"]["name"] = PSUPerson::get($mentee_wpid)->formatName("f l");
						$teams["$wpid"]["$mentee_wpid"]["wpid"] = $mentee_wpid;
					}
					
				}

				$sql = "SELECT p.wpid, CONCAT( p.name_first_formatted,  ' ', p.name_last_formatted ) AS name
								FROM call_log_employee e
								RIGHT OUTER JOIN phonebook.phonebook p ON p.username = e.user_name
								LEFT OUTER JOIN hr.teams t ON t.mentee = p.wpid
								WHERE e.status =  ?
								AND t.mentee IS NULL 
								AND e.user_privileges
								IN (?,  ?)
								ORDER BY p.name_last_formatted, p.name_full ASC";

				$unassigned = PSU::db('calllog')->GetAll($sql, array("active", 'trainee', 'sta'));
				foreach ($unassigned as $loner){
					$loner_wpid = $loner['wpid'];
					$teams["unassigned"]["mentor"]["mentor_name"] = "Unassigned";
					$teams["unassigned"]["mentor"]["mentor_wpid"] = "unassigned";
					$teams["unassigned"]["$loner_wpid"]["mentor_name"] = "unassigned";
					$teams["unassigned"]["$loner_wpid"]["mentor_wpid"] = "unassigned";
					$teams["unassigned"]["$loner_wpid"]["name"] = $loner['name'];
					$teams["unassigned"]["$loner_wpid"]["wpid"] = $loner['wpid'];
				}

				$memcache->set( 'teams', $teams, MEMCACHE_COMPRESSED, 60 * 5);
				$team_array = $memcache->get('teams');
			}
			else{
				$team_array = $memcache->get('teams');
			}
		}
		else{
			if (!$type){
				if ( ! ($cached_results = $memcache->get("teams-all-$key"))){
					$sql = "SELECT * FROM teams WHERE mentor = ? OR mentee = ?";
					$team_array = \PSU::db('hr')->GetAll($sql, array($key, $key));
					$memcache->set( "teams-all-$key", $team_array, MEMCACHE_COMPRESSED, 60 * 5);
					$team_array = $memcache->get("teams-all-$key");
				}
				else{
					$team_array = $memcache->get("teams-all-$key");
				}
			}
			else{
					// get all where key matches the type
					$sql = "SELECT * FROM teams WHERE $type = ?";
					$team_array = \PSU::db('hr')->GetAll($sql, array($key));
					$memcache->set( "teams-$key", $team_array, MEMCACHE_COMPRESSED, 60 * 5);
					$team_array = $memcache->get("teams-$key");
			}
			 				
		}
		return $team_array;
	}

	public function team_set_mentee($mentee, $mentor){

		$memcache = new \PSUMemcache('training-tracker_teams');
		$memcache->delete("teams");
		$memcache->delete("teams-all-$mentee");
		$memcache->delete("teams-all-$mentor");
		$memcache->delete("teams-$mentee");
		$memcache->delete("teams-$mentor");

		$sql = "UPDATE teams SET mentor=? WHERE mentee = ?";
		$result = PSU::db('hr')->Execute($sql, array($mentor, $mentee));
	}

	public function team_insert($mentee, $mentor){

		$memcache = new \PSUMemcache('training-tracker_teams');
		$memcache->delete("teams");
		$memcache->delete("teams-all-$mentee");
		$memcache->delete("teams-all-$mentor");
		$memcache->delete("teams-$mentee");
		$memcache->delete("teams-$mentor");
		
		$sql = "INSERT INTO teams (mentor, mentee) VALUES ( ?, ?)";
		$result = PSU::db('hr')->Execute($sql, array($mentor, $mentee));
	}

	public function team_delete($mentee){

		$memcache = new \PSUMemcache('training-tracker_teams');
		$memcache->delete("teams");
		$memcache->delete("teams-all-$mentee");
		$memcache->delete("teams-$mentee");

		$sql = "DELETE FROM teams WHERE mentee = ?";
		$result3 = PSU::db('hr')->Execute($sql, array($mentee));
	}	

	public function checklist_builder($user_level, $wpid){

		$type = TrainingTracker::checklist_type($user_level);

		$sql = "SELECT items.* 
						  FROM checklist_items items 
						  JOIN checklist_item_categories categories 
						    ON items.category_id = categories.id 
						 WHERE categories.type=?";	

		$checklist_builder['items'] = PSU::db('hr')->GetAll($sql, array($type));

		$checklist_id = TrainingTracker::get_checklist_id($wpid);

		$sql = "SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?";
		$checklist_checked = PSU::db('hr')->GetAll($sql, array($checklist_id, "complete"));
		
		$items = array();
		foreach ($checklist_checked as $checked){
			if ($checked['response'] == "complete"){
				$item_id = $checked['item_id'];
				$items["$item_id"] = $item_id;
			}
		}

		foreach ($checklist_builder['items'] as &$item){
			$id = $item['id'];
			if (isset($items["$id"])){
				$item['checked'] = true;
			}
			else{
				$item['checked'] = false;
			}
		}

		$sql = "SELECT * FROM checklist_item_categories WHERE type=?";

		$checklist_builder['sub_cat'] = PSU::db('hr')->GetAll($sql, array($type));

		$sql = "SELECT * FROM checklist WHERE type=?";
		$checklist_builder['category'] = PSU::db('hr')->GetAll($sql, array($type));

		return $checklist_builder;
	}	

	public function checklist_checked($checklist_id){

		$sql = "SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?";
		$checklist_checked = PSU::db('hr')->GetAll($sql, array($checklist_id, "complete"));
		
		return $checklist_checked;

	}

	public function last_modified($checklist_id){

		$sql = "SELECT max(activity_date) FROM person_checklist_items WHERE checklist_id=?";
		$last_modified['time'] = PSU::db('hr')->GetAll($sql, array($checklist_id));
		$last_modified['time'] = $last_modified['time'][0]['max(activity_date)']; 

		$sql = "SELECT updated_by FROM person_checklist_items WHERE activity_date=? AND checklist_id=?";
		$last_modified['modified_by'] = PSU::db('hr')->GetOne($sql, array($last_modified['time'], $checklist_id));

		return $last_modified;
	}	

	public function checkbox_exists($checked_id, $checklist_id){
		$sql = "SELECT item_id FROM person_checklist_items WHERE item_id=? AND checklist_id=?";
		$does_exist = PSU::db('hr')->GetOne($sql, array($checked_id, $checklist_id));
		if (!$does_exist){
			$return_value = false;
		}
		else{
			$return_value = true;
		}
		return $return_value;
	}

	public function checkbox_insert($checked_id, $checklist_id, $response, $pidm){
		$sql = "INSERT INTO person_checklist_items (item_id, checklist_id, response, updated_by) VALUES (?, ?, ?, ?)";
		$inserted = PSU::db('hr')->Execute($sql,array($checked_id, $checklist_id, $response, $pidm));
	}

	public function checkbox_update($response, $modified_by, $checked_id, $checklist_id){
		$sql = "UPDATE person_checklist_items SET response = ?, updated_by = ? WHERE item_id=? AND checklist_id=?";
		$update = PSU::db('hr')->Execute($sql, array($response, $modified_by, $checked_id, $checklist_id));
	}

	public function comment_exists($checklist_id){
		$sql = "SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?";
		$result = PSU::db('hr')->GetAll($sql, array($checklist_id, "notes"));

		if (!$result){
			$return_value = false;
		}
		else{
			$return_value = true;
		}
		return $return_value;
	}

	public function comment_update($comments, $checklist_id){
		$sql = "UPDATE person_checklist_items SET notes=? WHERE checklist_id=? AND response=?";
		$result1 = PSU::db('hr')->Execute($sql, array($comments, $checklist_id, "notes"));
	}

	public function comment_insert($checklist_id, $comments, $modified_by){
		$sql = "INSERT INTO person_checklist_items (checklist_id, item_id, response, notes, updated_by) VALUES (?, ?, ?, ?, ?)";
		$result2 = PSU::db('hr')->Execute($sql, array ($checklist_id, "007", "notes", $comments, $modified_by));
	}

	public function valid_wpid($wpid){
		if(preg_match("/^[a-z]{1}\\d{1}[a-z]{7}$/", $wpid)){
			$return_value = true;
		}
		else{
			$return_value = false;
		}
		return $return_value;
	}
	
}
