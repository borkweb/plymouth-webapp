<?php

namespace PSU\TeacherCert;

class SchoolTypes extends Collection {
	static $_name = 'School Types';
	static $child = 'PSU\\TeacherCert\\SchoolType';
	static $table = 'school_types';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\SchoolTypes
