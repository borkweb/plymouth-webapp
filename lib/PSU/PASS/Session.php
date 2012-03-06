<?php

/**
 * A single session object.
 */
class PSU_PASS_Session extends PSU_PASS_PASSObject {

	/*
	 * Create a new session
	 */
	public function create($arr) {
		$arr['session_date'] = PSU_PASS_PASSObject::checkdate($arr['session_date']);

		// Setup SQL $args
		$params = array (
			'request_id' => $arr['request_id'],
			'session_date' => date('Y-m-d',strtotime($arr['session_date'])),
			'session_duration' => $arr['session_duration'],
			'session_type' => $arr['session_type'],
			'tutor_pidm' => $arr['tutor_pidm']
		);

		// Setup $sql query. 
		$sql = "INSERT INTO psu.pass_session (
							request_id,
							session_date,
							session_duration,
							session_type,
							tutor_pidm)
						VALUES (
							:request_id,
							to_date(:session_date,'YYYY-MM-DD'),
							:session_duration,
							:session_type,
							:tutor_pidm)
						";
		// Execute and return results
		if ($results = PSU::db('banner')->Execute($sql,$params)) {
			$params['pidm'] = $arr['pidm'];
			$params['term_code'] = $arr['term_code'];
			PASS::update_tutor_service_counts($params);
			return true;
		}
		return false;
	}

	/*
	 *  Removes a session
	 */
	public function delete($arr) {
		$sql = "DELETE FROM psu.pass_session WHERE id = :id";
		
		$params = array('id'=>$arr['id']);

		return PSU::db('banner')->Execute($sql,$params);
	}

	/*
	 *  Loads tutor information as a person object from a pidm
	 */
	public function load_tutor($tutor_pidm) {
		$this->tutor_info = new PSUPerson($tutor_pidm);	
	}


	/* 
	 * Returns aggregate session time for all sessions
	 */
	public function total_time() {
		return $this->session_duration;
	}

}//end PSU_PASS_Session
