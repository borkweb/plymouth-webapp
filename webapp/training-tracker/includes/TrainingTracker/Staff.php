<?php

namespace TrainingTracker;

class Staff extends \PSU_DataObject {
	function person(){
		$person = \PSUPerson::get($this->wpid);
		return $person;
	}

	/**
	 * returns a team as an associative array (by wpid) 
	 * OR an individual element of the array if an index is passed.
	 *
	 * @param $index string Associative index (optional)
	 */
	function team( $index = null ){
		// static variable is instantiated the FIRST time this is run
		static $team = array();

		// if the static variable's wpid index has not been set, the 
		// team data has not been loaded.
		if( ! $team[ $mentor_wpid ][ $this->wpid ] ) {
			$sql = "SELECT mentor, mentee FROM teams WHERE mentee=?";
			$result = \PSU::db('hr')->GetRow($sql,array($this->wpid));

			if (isset($result['mentor'])){
				$mentor = $result['mentor'];

				// set the results from the query into the static variable
				// for later use.
				$mentee['wpid'] = $this->wpid;
				$mentee['name'] = $this->person()->formatName("f l");
				$team[ $mentor ][ $this->wpid ] = $mentee;
				
			}//end if
			else{
					$mentor = 'unassigned';
					$mentee['wpid'] = $this->wpid;
					$mentee['name'] = $this->person()->formatName("f l");
					$team[ $mentor ][ $this->wpid ] = $mentee;
			}//end if

		}
		//   Return either the full $team[ $wpid ] or an idividual index
		return $index ? $team[$mentor][$index] : $team;
		//	return $team[ $this->wpid ];
		
	}

	public function stats($parameter = null){
		$wpid = $this->wpid;
		$person = \PSUPerson::get($wpid);
		$pidm = $person->pidm;
		$username = $person->username;

		$checklist_id = \PSU::db('hr')->GetOne("SELECT id FROM person_checklists WHERE pidm=?",array($pidm));	

		$checkboxes = \PSU::db('hr')->GetAll("SELECT * FROM person_checklist_items WHERE checklist_id=? AND response=?", array($checklist_id, "complete"));

		$current_level = \PSU::db('calllog')->GetOne("SELECT user_privileges FROM call_log_employee WHERE user_name=?", array($username));
		$completed = sizeof($checkboxes); 

		if ($current_level == 'trainee'){
			$search = array("13","14","15","16","31");
		}
		else if ($current_level == 'sta'){
			$search = array("17","18","19","20","21","22","23","24");
		}
		else{
			$search = array("25","26","27","28","29","30");
		}
		$stats = array();
		foreach ($search as $item){

			$stat = \PSU::db('hr')->GetAll("SELECT items.item_id	FROM person_checklist_items items 
																													  JOIN person_checklists checklist 
																													    ON items.checklist_id = checklist.id 
																													  JOIN checklist_item_categories categories 
																													    ON categories.type = checklist.type
																													  JOIN checklist_items checklist_items
																													    ON checklist_items.id = items.item_id
																													 WHERE items.checklist_id = checklist.id 
																													   AND checklist.type = categories.type
																												     AND categories.id=?
																													   AND items.response=?
																													   AND checklist_items.category_id = categories.id
																													   AND checklist.pidm=?", array($item,"complete", $pidm)); 
		
		
			$stat = sizeof($stat);
			if ($item == 13){
				$stat = $stat/5;
			}
			else if ($item == 14){
				$stat = $stat/9;
			}
			else if ($item == 31){
				$stat = $stat/5;
			}
			else if ($item == 15){
				$stat = $stat/8;
			}
			else if ($item == 16){
				$stat = $stat/5;
			}
			else if ($item == 17){
				$stat = $stat/6;
			}
			else if ($item == 18){
				$stat = $stat/4;
			}
			else if ($item == 19){
				$stat = $stat/8;
			}
			else if ($item == 20){
				$stat = $stat/3;
			}
			else if ($item == 21){
				if ($stat > 2){
					$stat = 2;
				}
				$stat = $stat/2;
			}
			else if ($item == 22){
				$stat = $stat/4;
			}
			else if ($item == 23){
				if ($stat  > 1){
					$stat = 1;
				}
			}
			else if ($item == 24){
				$stat = $stat/4;
			}
			else if ($item == 25){
				$stat = $stat/5;
			}
			else if ($item == 26){
				$stat = $stat/3;
			}
			else if ($item == 27){
				$stat = $stat/2;
			}
			else if ($item == 28){
				$stat = $stat/2;
			}
			else if ($item == 29){
				$stat = $stat/4;
			}
			else if ($item == 30){
				$stat = $stat/4;
			} 
			
			$stats["$item"] = round(($stat*100), 2);
		}

		$total = 0;
		$ct = 0;
		foreach ($stats as $statistic){
			$ct++;
			$total += ($statistic);
		}
		$progress = round(($total/$ct), 2);

		$stats['progress'] = $progress;

		return (isset($parameter)?$stats[$parameter]:$stats);
	
	}//end stats

}//end class



