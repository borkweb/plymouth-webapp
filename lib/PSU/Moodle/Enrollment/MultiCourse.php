<?php

namespace PSU\Moodle\Enrollment; 

class MultiCourse extends \PSU\Moodle\Enrollment {

	/**
	 * _construct
	 *
	 * magic constructor method
	 *
	 * @param    $url    Optional url parameted to post generated xml to.
	 */
	public function __construct( $course, $population, $args = '' ){
		if( !is_array( $course ) ) {
			die('Courses must be in an array of Moodle course ids!: '.$course);
		}//end if

		parent::__construct( $course, $population, $args );

	}//end __construct

	public function enroll() {
		foreach( $this->course as $id ) {
			$insert_time = time();
			$courseid = self::courseid( $id );
			$enrolid = self::enrolid( 'multi_course', $courseid );

			$args = array(
				0,
				$enrolid,
				self::userid( $this->population ),
				$insert_time,
				0,
				0,
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
				ON DUPLICATE KEY UPDATE timemodified=".$insert_time;

			\PSU::db('moodle2')->Execute( $sql, $args);
		}//end foreach
	}//end manual

}//end PSU_Moodle_Enrollment	
