<?php

namespace PSU\TeacherCert;

class TempDataChecks extends Collection {
	static $_name = 'Temp Data Checks';
	static $child = 'PSU\\TeacherCert\\TempDataCheck';
	static $table = 'temp_data_checks';
	static $child_key = 'key';

	protected function _get_order() {
		return 'new_table, new_column';
	}
}
