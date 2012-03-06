<?php

namespace PSU\TeacherCert\Model;

class School extends \PSU\TeacherCert\Model {
	public function __construct() {
		$this->name = new \PSU\Model\FormText( 'maxlength=60&required=true' );
		$this->sau_id = new \PSU\Model\FormSelect( array( 'label' => 'SAU:', 'options' => self::collection( '\PSU\TeacherCert\SAUs' ), ) );
		$this->district_id = new \PSU\Model\FormSelect( array( 'label' => 'District:', 'options' => self::collection( '\PSU\TeacherCert\Districts' ), ) );
		$this->school_type_id = new \PSU\Model\FormSelect( array( 'label' => 'School Type:', 'options' => self::collection( '\PSU\TeacherCert\SchoolTypes' ), ) );
		$this->school_approval_level_id = new \PSU\Model\FormSelect( array( 'label' => 'Approval Level:', 'options' => self::collection( '\PSU\TeacherCert\SchoolApprovalLevels' ), ) );
		$this->grade_span = new \PSU\Model\FormText( 'maxlength=20' );
		$this->enrollment = new \PSU\Model\FormText( 'maxlength=5' );
		$this->street_line1 = new \PSU\Model\FormText( 'maxlength=75' );
		$this->street_line2 = new \PSU\Model\FormText( 'maxlength=75' );
		$this->city = new \PSU\Model\FormText( 'maxlength=50' );
		$this->state = new \PSU\Model\FormText( 'maxlength=3' );
		$this->zip = new \PSU\Model\FormText( 'maxlength=30' );
		$this->phone = new \PSU\Model\FormText( 'maxlength=20' );
		$this->fax = new \PSU\Model\FormText( 'maxlength=20' );
		parent::__construct();
	}
}//end School
