<?php

namespace PSU\TeacherCert;

class Gates extends Collection {
	static $_name = 'Gates';
	static $child = 'PSU\\TeacherCert\\Gate';
	static $parent_key = 'gate_system_id';
	static $table = 'gates';

	protected function _get_order() {
		return 'sort_order, name';
	}
}//end class PSU\TeacherCert\Gates
