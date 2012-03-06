<?php

namespace PSU\TeacherCert;

class GateSystems extends Collection {
	static $_name = 'Gate Systems';
	static $child = 'PSU\\TeacherCert\\GateSystem';
	static $table = 'gate_systems';

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\GateSystems
