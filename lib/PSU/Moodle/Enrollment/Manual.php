<?php

namespace PSU\Moodle\Enrollment; 

use Enrollment\Exception;

class Manual extends \PSU\Moodle\Enrollment {

	/**
	 * _construct
	 *
	 * magic constructor method
	 *
	 * @param    $url    Optional url parameted to post generated xml to.
	 */
	public function __construct( $course, $population, $role = 'student', $args = '' ){

		parent::__construct( $course, $population, $role, $args );

	}//end __construct

	public function enroll() {
		foreach( $this->population as $id ) {
			if( $idnumber = \PSU\Moodle::user_exists( $id->scalar ) ) {
				self::add_to_flatfile( $idnumber , $this->course );
			}//end if
		}//end foreach

		self::write_to_flatfile();
	}//end manual

}//end PSU_Moodle_Enrollment	
