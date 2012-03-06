<?php

namespace PSU\TeacherCert\Model\Student;

use PSU\TeacherCert;

class Faculty extends TeacherCert\Model {
	/**
	 *
	 */
	public function __construct() {
		$this->faculty_pidm = new FormNumber;
		$this->association_attribute = new FormText;
		$this->start_date = new FormDate;
		$this->end_date = new FormDate;

		parent::__construct();
	}//end __construct
}//end PSU\TeacherCert\Model\Student\Faculty
