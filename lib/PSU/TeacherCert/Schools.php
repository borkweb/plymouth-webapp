<?php

namespace PSU\TeacherCert;

class Schools extends Collection {
	static $_name = 'Schools';
	static $child = 'PSU\\TeacherCert\\School';
	static $table = 'schools';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\Schools
