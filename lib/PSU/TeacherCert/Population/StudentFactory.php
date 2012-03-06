<?php

namespace PSU\TeacherCert\Population;

use PSU\TeacherCert;

class StudentFactory extends \PSU_Population_UserFactory {
	public function create( $row ) {
		$user = TeacherCert\Student::get( $row );
		return $user;
	}//end create
}//end PSU\TeacherCert\Population\StudentFactory
