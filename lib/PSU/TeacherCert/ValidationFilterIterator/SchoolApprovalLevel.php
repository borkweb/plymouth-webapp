<?php

namespace PSU\TeacherCert\ValidationFilterIterator;

use \PSU\TeacherCert\ValidationFilterIterator;

class SchoolApprovalLevel extends ValidationFilterIterator {
	public function __construct( $item, $it, $inverse = false ) {
		parent::__construct( '\PSU\TeacherCert\SchoolApprovalLevel', 'school_approval_level_id', $item, $it, $inverse );
	}//end constructor
}//end PSU\TeacherCert\Schools\ValidationFilterIterator\SchoolApprovalLevel
