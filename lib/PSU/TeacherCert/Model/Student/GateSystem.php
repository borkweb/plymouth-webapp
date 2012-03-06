<?php

namespace PSU\TeacherCert\Model\Student;

use PSU\TeacherCert;

class GateSystem extends TeacherCert\Model {
	public function __construct() {
		$this->teaching_term_code = new \PSU\Model\FormText( array( 'label' => 'Student Teaching Term:', 'maxlength' => 6 ) );

		parent::__construct();
	}//end __construct
}//end \PSU\TeacherCert\Model\Student\GateSystem
