<?php

namespace PSU\Moodle; 
require_once 'autoload.php';

class Enrollment {

	public $course;
	public $population;

	/**
	 * _construct
	 *
	 * magic constructor method
	 *
	 * @param    $url    Optional url parameted to post generated xml to.
	 */
	public function __construct( $course, $population, $args = '' ){

		$this->course = $course;
		$this->population = $population;
		$this->population->query( $args );

	}//end __construct

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
