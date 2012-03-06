<?php

namespace PSU\TeacherCert;

class Constituents extends Collection {
	static $_name = 'Constituents';
	static $child = 'PSU\TeacherCert\Constituent';
	static $table = 'constituents';

	protected function _get_order() {
		return 'last_name, first_name, mi';
	}
}//end class PSU\TeacherCert\Constituents
