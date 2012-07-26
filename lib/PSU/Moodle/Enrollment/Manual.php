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
		if( !is_numeric( $course ) ) {
			throw new Exception( 'Courses must be in the form of a Moodle course id!: '.$course );
		}//end if

		parent::__construct( $course, $population, $role, $args );

	}//end __construct

	public function enroll() {
		foreach( $this->population as $id ) {
			self::add_to_flatfile( $id->scalar , self::courseidnumber( $course ) );
		}//end foreach

		self::write_to_flatfile();
	}//end manual

}//end PSU_Moodle_Enrollment	
