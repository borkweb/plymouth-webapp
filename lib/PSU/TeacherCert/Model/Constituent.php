<?php

namespace PSU\TeacherCert\Model;

class Constituent extends \PSU\Model {
	public function __construct() {
		$this->first_name = new \PSU\Model\FormText( 'maxlength=60&required=true' );
		$this->mi = new \PSU\Model\FormText( 'maxlength=60' );
		$this->last_name = new \PSU\Model\FormText( 'maxlength=60&required=true' );
		$this->prefix = new \PSU\Model\FormText( 'maxlength=20' );
		$this->suffix = new \PSU\Model\FormText( 'maxlength=20' );
		$this->email = new \PSU\Model\FormText( 'maxlength=90' );
		parent::__construct();
	}
}//end Constituent
