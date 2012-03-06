<?php

namespace PSU\TeacherCert\Model\Student;

use PSU\TeacherCert;

class CooperatingTeacher extends TeacherCert\Model {
	public function __construct() {
		$this->id = new \PSU\Model\FormNumber( 'hidden=1&required=1' );
		$this->student_gate_system_id = new \PSU\Model\FormNumber( 'hidden=1&required=1' );
		$this->constituent_school_id = new \PSU\Model\FormSelect( 'label=Cooperating Teacher:&required=1' );
		$this->association_attribute = new \PSU\Model\FormSelect( array( 'options' => $this->association_attributes() ) );

		parent::__construct();
	}//end __construct

	public function association_attributes() {
		return array( 'SPEDCERT' );
	}
}//end class CooperatingTeacher
