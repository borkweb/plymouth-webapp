<?php

namespace PSU\TeacherCert\Model\Student;

use PSU\TeacherCert;

class Test extends TeacherCert\Model {
	public function __construct() {
		$this->tesc_code = new \PSU\Model\FormSelect( array( 'label' => 'Subject:', 'options' => self::tests(), 'required' => true ) );
		$this->test_score = new \PSU\Model\FormNumber( 'required=1' );
		$this->test_date = new \PSU\Model\FormDate( 'required=1' );

		parent::__construct();
	}//end __construct

	public function tests() {
		static $tests = array(
			array( 'PRXR', 'Reading' ),
			array( 'PRXW', 'Writing' ),
			array( 'PRXM', 'Math' ),
		);

		return $tests;
	}
}//end class PSU\TeacherCert\Model\Student\Test
