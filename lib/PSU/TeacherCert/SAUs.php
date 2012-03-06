<?php

namespace PSU\TeacherCert;

class SAUs extends Collection {
	static $_name = 'SAUs';
	static $child = 'PSU\\TeacherCert\\SAU';
	static $table = 'saus';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\SAUs
