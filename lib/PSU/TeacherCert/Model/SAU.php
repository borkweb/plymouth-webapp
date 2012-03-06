<?php

namespace PSU\TeacherCert\Model;

use \PSU\TeacherCert;

class SAU extends \PSU\TeacherCert\Model {
	public function __construct() {
		$this->name = new \PSU\Model\FormText( 'maxlength=60&required=true' );
		$this->street_line1 = new \PSU\Model\FormText( 'label=Street Line 1&maxlength=75' );
		$this->street_line2 = new \PSU\Model\FormText( 'label=Street Line 2&maxlength=75' );
		$this->city = new \PSU\Model\FormText( 'required=true&maxlenth=50' );
		$this->state = new \PSU\Model\FormText( 'maxlength=3' );
		$this->zip = new \PSU\Model\FormText( 'maxlength=30' );
		$this->phone = new \PSU\Model\FormText( 'maxlength=20' );
		$this->fax = new \PSU\Model\FormText( 'maxlength=20' );
		parent::__construct();
	}

	public function validate_city( $field ) {
		if( $_POST && $this->city->value() === null ) {
			throw new \PSU\Model\ValidationException('The City field must be filled in.');
		}//end if
	}//end validate_name
}//end SAU
