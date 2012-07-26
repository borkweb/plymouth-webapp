<?php

namespace PSU\Moodle; 

use PSU\Moodle\Enrollment\Exception; 

class Enrollment {

	public $course;
	public $flush = false;
	public $population;
	public $to_write = array();

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
	 * Leverage the flatfile enrollment plugin to carry out enrollments
	 * 
	 * $user is the idnumber of the user aka PSU_ID
	 * $course is the idnumber from the course table aka crn.termcode
	 * $role that will be used is declared at instantiation
	 */
	public function add_to_flatfile( $user, $course, $action = 'add', $timestart = NULL, $timestop = NULL ) {
		$entry = array(
			$action,
			$this->role,
			$user,
			$course,
		);

		// Only add these values if they are actually set
		// We don't want to be adding extra commas to our file
		if( $timestart ) {
			$entry[] = $timestart;
			if( $timestop ) {
				$entry[] = $timestop;
			}//end if
		}//end if

		$this->to_write[] = $entry;
	}//end add_to_flatfile

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

		return \PSU::db('moodle2')->Execute( $sql, $args );
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
	}//end courseid

	/**
	 * Return the courseidnumber from moodle for the given course id 
	 */
	public function courseidnumber( $id ) {

		$sql = "
			SELECT idnumber
			  FROM mdl_course
			 WHERE id = ? 
		";

		return \PSU::db('moodle2')->GetOne( $sql, array( $id ) );
	}//end courseidnumber

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
	public function perform_enrollment( $userid, $courseid, $method = 'manual' ) {
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

		if( \PSU::db('moodle2')->Execute( $sql, $args) ) {
			return self::assign_role( $userid, $courseid );
		}else {
			throw new Exception( 'User with id '.$userid.' was not succesfully enrolled in course with id: '.$courseid );
		}//end else

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

	/**
	 * Write the contents to $this->to_write to the target
	 * flatfile for the Moodle flatfile enrollment plugin to 
	 * pick up on.
	 */
	public function write_to_flatfile() {
		$mode = $this->flush ? 'w' : 'a';
		$fp = fopen( '/web/temp/moodle/m2_auto.txt', $mode );

		foreach( $this->to_write as $enrollment ) {
			fputcsv( $fp, $enrollment );
		}//end foreach

		fclose( $fp );
	}//end write_to_flatfile
}//end PSU_Moodle_Enrollment	
