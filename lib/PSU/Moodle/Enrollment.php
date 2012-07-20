<?php

namespace PSU\Moodle; 

use PSU\Moodle\Enrollment\Exception; 

class Enrollment {

	public $course;
	public $population;

	/**
	 * magic constructor method
	 */
	public function __construct( $course, $population, $role = 'student', $args = '' ){

		$this->course = $course;
		$this->population = $population;
		$this->role = $role;
		if( is_object( $this->population ) ) {
			$this->population->query( $args );
		}

	}//end __construct

	/**
	 * Assign a role to the user for the course they have been enrolled in.
	 */
	public function assign_role( $userid, $courseid ) {

		$sql = "
			INSERT INTO mdl_role_assignments (
				roleid,
				contextid,
				userid,
				timemodified,
				component
			) VALUES ( ?, ?, ?, ?, ? )
		";

		$args = array(
			self::roleid( $this->role ),
			self::contextid( $courseid ),
			$userid,
			time(),
			'',
		);

		\PSU::db('moodle2')->Execute( $sql, $args );
	}//end assign_role

	/**
	 * Retieve the contextid for a course for role assignment
	 */
	public function contextid( $courseid ) {

		$sql = "
			SELECT id 
              FROM mdl_context 
             WHERE instanceid = ?	
		";

		if( $contextid = \PSU::db('moodle2')->GetOne( $sql, array( $courseid ) ) ) {
			return $contextid;
		}else {
			throw new Exception( 'No context id for course with id: '.$courseid );
		}//end else	

	}//end contextid

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
	public function enrolid( $method, $courseid = null, $role = 'student' ) {

		$args = array(
			$courseid ?: $this->course,
			$method,
		);

		$sql = "
			SELECT id
			  FROM mdl_enrol
			 WHERE courseid = ? 
			   AND enrol = ?	
		";
		\PSU::db('moodle2')->debug = true;

		if( $enrolid = \PSU::db('moodle2')->GetOne( $sql, $args ) ) {
			$this->enrolid = $enrolid;
			return $this->enrolid;
		}//end if

		$sql = "
			INSERT INTO mdl_enrol (
				enrol,
				status,
				courseid,
				sortorder,
				roleid,
				timecreated,
				timemodified
			) 
			SELECT ?,
				   0,
				   ?,
				   MAX(sortorder)+1,
				   ?,
				   ?,
				   ?
			  FROM mdl_enrol
			 WHERE courseid = ?
		";

		$insert_time = time();

		$args = array(
			$method,
			$courseid ?: $this->course,
			self::roleid( $role ),
			$insert_time,				
			$insert_time,				
			$courseid ?: $this->course,
		);

		if( \PSU::db('moodle2')->Execute($sql, $args) ) {
			$this->enrolid = mysql_insert_id();
			return $this->enrolid;
		}else {
			throw new Exception( 'There was an error retrieving the Enrollment ID for this course: '.$courseid );
		}//end else

	}//end enrolid

	/**
	 * Take in the arguments from the sub-enrollment types and insert into the database
	 * Assign perscribed role to the user for the course
	 */
	public function perform_enrollment( $userid, $courseid, $method = 'psu_auto_enroller' ) {
		$insert_time = time();
		$enrolid = $this->enrolid ?: self::enrolid( $method, $courseid );

		$args = array(
			0,
			$enrolid,
			$userid,
			$insert_time,
			0,
			0,
			$insert_time,
			$insert_time,
			$insert_time,
		);
	
		$sql = "
			INSERT INTO mdl_user_enrolments (
				status, 
				enrolid, 
				userid, 
				timestart, 
				timeend, 
				modifierid, 
				timecreated, 
				timemodified
			) VALUES(
				?,
				?,
				?,
				?,
				?,
				?,
				?,
				?
			) 
			ON DUPLICATE KEY UPDATE timemodified=?";

		\PSU::db('moodle2')->Execute( $sql, $args);

		self::assign_role( $userid, $courseid );
	}//end perform_enrollment

	/**
	 * Retrieve the id associated with the given role shortname.
	 */
	public function roleid( $role ) {
		if( isset( $this->roleid ) ) {
			return $this->roleid;
		}//end if
		
		$sql = "
			SELECT id
              FROM mdl_role
             WHERE shortname = ?
		";

		if( $roleid = \PSU::db('moodle2')->GetOne( $sql, array( $role ) ) ) {
			$this->roleid = $roleid;
			return $this->roleid;
		}else{
			throw new Exception( 'Invalid role shortname provided' );
		}//end else

	}//end roleid

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
