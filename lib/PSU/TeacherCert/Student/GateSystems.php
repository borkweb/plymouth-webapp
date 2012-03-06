<?php

namespace PSU\TeacherCert\Student;

class GateSystems extends \PSU\TeacherCert\Collection {
	static $_name = 'Stuent Gate Systems';
	static $parent_key = 'pidm';
	static $table = 'student_gate_systems';
	static $child = 'PSU\\TeacherCert\\Student\\GateSystem';

	/**
	 * Active gate systems.
	 */
	public function active() {
		return new GateSystems\ActiveIterator( $this->getIterator() );
	}//end active
}//end class PSU\TeacherCert\Student\GateSystems
