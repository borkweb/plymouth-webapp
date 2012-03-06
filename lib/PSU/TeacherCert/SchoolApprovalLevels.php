<?php

namespace PSU\TeacherCert;

class SchoolApprovalLevels extends Collection {
	static $_name = 'School Approval Levels';
	static $child = 'PSU\\TeacherCert\\SchoolApprovalLevel';
	static $table = 'school_approval_levels';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\SchoolApprovalLevels
