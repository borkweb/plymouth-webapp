<?php

namespace PSU\TeacherCert;

class Positions extends Collection {
	static $_name = 'Positions';
	static $child = 'PSU\\TeacherCert\\Position';
	static $table = 'constituent_positions';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\Positions
