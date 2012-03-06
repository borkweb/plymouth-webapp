<?php

namespace PSU\TeacherCert\ValidationFilterIterator;

use \PSU\TeacherCert\ValidationFilterIterator;

class SchoolType extends ValidationFilterIterator {
	public function __construct( $item, $it, $inverse = false ) {
		parent::__construct( '\PSU\TeacherCert\SchoolType', 'school_type_id', $item, $it, $inverse );
	}//end constructor
}//end PSU\TeacherCert\Schools\ValidationFilterIterator\SchoolType
