<?php

namespace PSU\TeacherCert;

class Districts extends Collection {
	static $_name = 'Districts';
	static $child = 'PSU\\TeacherCert\\District';
	static $table = 'districts';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\Districts
