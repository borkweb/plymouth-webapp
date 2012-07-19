<?php

namespace PSU\Moodle; 

class Enrollment {

	public $course;
	public $population;

	/**
	 * magic constructor method
	 */
	public function __construct( $course, $population, $args = '' ){

		$this->course = $course;
		$this->population = $population;
		if( is_object( $this->population ) ) {
			$this->population->query( $args );
		}

	}//end __construct

	/**
	 * Return the courseid from moodle for the given course idnumber (crn.termcode) 
	 */
	public function courseid( $idnumber ) {

		$sql = "
			SELECT id
			  FROM mdl_course
			 WHERE idnumber = ? 
		";

		return \PSU::db('moodle2')->GetOne( $sql, array( $idnumber ) );
	}//end enrolid

	/**
	 * Return the enrolid from moodle for the given course and enrollment method
	 */
	public function enrolid( $method = 'manual', $courseid = null) {

		$args = array(
			$method,
			$courseid ?: $this->course,
		);

		$sql = "
			SELECT id
			  FROM mdl_enrol
			 WHERE courseid = ? 
			   AND enrol = ?	
		";

		return \PSU::db('moodle2')->GetOne( $sql, $args );
	}//end enrolid

	/**
	 * Take in a PSU idnumber, and return the associated moodle 2 userid
	 */
	public function userid( $psu_idnumber ) {

		$args = array(
			$psu_idnumber,
		);

		$sql = "
			SELECT id
			  FROM mdl_user 
			 WHERE idnumber = ?
		";

		return \PSU::db('moodle2')->GetOne( $sql, $args );
	}//end userid
}//end PSU_Moodle_Enrollment	
