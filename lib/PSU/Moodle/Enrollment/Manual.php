<?php

namespace PSU\Moodle\Enrollment; 

use Exception;

class Manual extends \PSU\Moodle\Enrollment {

	/**
	 * _construct
	 *
	 * magic constructor method
	 *
	 * @param    $url    Optional url parameted to post generated xml to.
	 */
	public function __construct( $course, $population, $args = '' ){
		if( !is_numeric( $course ) ) {
			throw new Exception( 'Courses must be in the form of a Moodle course id!: '.$course );
		}//end if

		parent::__construct( $course, $population, $args );

		$this->enrolid = self::enrolid();
	}//end __construct

	public function enroll() {
		foreach( $this->population as $id ) {
			$insert_time = time();

			$args = array(
				0,
				$this->enrolid,
				self::userid( $id->scalar ),
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
