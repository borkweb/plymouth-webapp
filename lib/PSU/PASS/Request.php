<?php

/**
 * A single request object.
 */
class PSU_PASS_Request extends PSU_PASS_PASSObject {

	/**
	 * Strings for extra query arguments
	 */
	public $extra_inserts = null;
	public $extra_values = null;
	public $extra_sets = null;

	public function accepted() {
		return $this->assigned_tutor_pidm != null;
	}//end accepted

	/*
	 * Retrive course information if necessary
	 */
	public function course() {
		if ($this->course_info) {
			return $this->course_info;
		}
		if ($this->course_id != 'ss') {
			$this->course_info = new PSUCourseSection($this->course_id,$this->term_code);
		} else {
			$this->course_info = 'ss';
		}
		return $this->course_info;
	}

 /**
  * Create a new tutor request given an array of information 
  */
	public function create($arr) {
		// Get sequence number for insert.
		if (!$arr['course_id'] || !$arr['request_date']) {
			//return false;
		} 

		$arr['request_date'] = PSU_PASS_PASSObject::checkdate($arr['request_date']);

		// Setup SQL $args
		$params = array (
			'term_code' => $arr['term_code'],
			'student_pidm' => $arr['student_pidm'],
			'course_id' => $arr['course_id'],
			'request_date' => date('Y-m-d',strtotime($arr['request_date'])),
			'request_status' => $arr['request_status']
		);

		self::extra_info(&$arr, &$params, &$extra_inserts, &$extra_values, &$extra_sets);

		// Setup $sql query. 
		$sql = "INSERT INTO psu.pass_tutor_request (
							term_code,
							student_pidm,
							course_id,
							request_date,
							$extra_inserts	
							request_status)
						VALUES (
							:term_code,
							:student_pidm,
							:course_id,
							to_date(:request_date,'YYYY-MM-DD'),
							$extra_values
							:request_status)
						";
		// Execute and return results
		if ($results = PSU::db('banner')->Execute($sql,$params)) {
			$params['pidm'] = $arr['student_pidm'];
			$params['session_type'] = 'ZTUR';
			PASS::update_tutor_service_counts($params);
			$params['session_type'] = 'PF';
			PASS::update_tutor_service_counts($params);
			PASS::update_tutor_service_counts($params);
			$params['session_type'] = 'CA';
			PASS::update_tutor_service_counts($params);
			return true;
		}
		return false;
	}

	/*
	 * Returns the current tutor associated with the request as a person object 
	 */
	public function current_tutor() {
		if ($this->assigned_tutor_pidm) {
			if (!$this->current_tutor) {
				$this->current_tutor = new PSUPerson($this->assigned_tutor_pidm);
			}
			return $this->current_tutor;
		}
		return false;
	}	

	/* 
	 * Gets extra info for queries
	 */
	public function extra_info($arr, $params, $extra_inserts, $extra_values, $extra_sets) {
		// Add more data to the insert if more than minimum amount is passed.
		if(strlen($arr['decision_date']) > 1) {
			$arr['decision_date'] = PSU_PASS_PASSObject::checkdate($arr['decision_date']);
			$params['decision_date'] = date('Y-m-d',strtotime($arr['decision_date']));

			$extra_inserts .= "decision_date,";
			$extra_values .= "to_date(:decision_date,'YYYY-MM-DD'),";	
			$extra_sets .= "decision_date = to_date(:decision_date,'YYYY-MM-DD'),";	
		}
		
		if(strlen($arr['tutor_id']) > 1) {
			$tutor = new PSUPerson($arr['tutor_id']);
			$params['assigned_tutor_pidm'] = $tutor->pidm;

			$extra_inserts .= "assigned_tutor_pidm,";
			$extra_values .= ":assigned_tutor_pidm,";
			$extra_sets .= "assigned_tutor_pidm = :assigned_tutor_pidm,";
		}
	}
 
	/*
	 * Updates an existing request object.
	 */
	public function update($arr) {
		// Setup SQL $args
		$params['request_status'] = $arr['request_status'];
		$params['id'] = $arr['request_id'];
		$extra_sets = '';

		self::extra_info(&$arr, &$params, &$extra_inserts, &$extra_values, &$extra_sets);

		// Setup $sql query. 
		$sql = "UPDATE psu.pass_tutor_request 
							SET 
							$extra_sets
							request_status = :request_status
						WHERE (
							id = :id)
						";
		// Execute and return results
		return PSU::db('banner')->Execute($sql,$params);
	}

}//end PSU_PASS_Request
