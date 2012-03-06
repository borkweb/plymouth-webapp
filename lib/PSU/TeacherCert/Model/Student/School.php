<?php

namespace PSU\TeacherCert\Model\Student;

use PSU\TeacherCert;

class School extends TeacherCert\Model {
	public function __construct() {
		$this->id = new \PSU\Model\FormNumber( 'hidden=1' );
		$this->school_id = new \PSU\Model\FormSelect( array( 'label' => 'School:', 'options' => self::collection('\PSU\TeacherCert\Schools'), 'required' => true ) );
		$this->grade = new \PSU\Model\FormText( 'required=1' );
		$this->interview_ind = new \PSU\Model\FormSelect( array( 'label' => 'Had interview?', 'options' => \PSU\Model\FormSelect::yesno() ) );
		$this->placement = new \PSU\Model\FormText( 'required=1' );
		$this->notes = new \PSU\Model\FormTextarea;

		parent::__construct();
	}//end __construct
}
