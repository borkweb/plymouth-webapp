<?php

namespace PSU\TeacherCert\Model;

class District extends \PSU\Model {
	public function __construct() {
		$this->name = new \PSU\Model\FormText( 'maxlength=60&required=true' );
		parent::__construct();
	}
}//end District
