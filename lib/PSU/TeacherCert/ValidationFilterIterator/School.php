<?php

namespace PSU\TeacherCert\ValidationFilterIterator;

use \PSU\TeacherCert\ValidationFilterIterator;

class School extends ValidationFilterIterator {
	public function __construct( $item, $it, $inverse = false ) {
		parent::__construct( '\PSU\TeacherCert\School', 'school_id', $item, $it, $inverse );
	}//end constructor
}//end PSU\TeacherCert\Schools\ValidationFilterIterator\School
