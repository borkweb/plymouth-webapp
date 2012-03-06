<?php

namespace PSU\TeacherCert\Student;

use GateSystem as StudentGateSystem;

class Gates extends \PSU\TeacherCert\Collection {
	static $child = 'PSU\\TeacherCert\\Student\\Gate';
	static $child_key = 'gate_id';
	static $parent_key = 'student_gate_system_id';
	static $table = 'student_gates';
	static $join = array(
		array(
			'type' => 'RIGHT JOIN',
			'table' => 'psu_teacher_cert.v_student_gates',
			'fields' => array(
				array( 
					'logic' => 'AND',
					'field1' => 'student_gates.student_gate_system_id',
					'field2' => 'v_student_gates.student_gate_system_id',
				),
			),
		),
	);

	protected function _get_order() {
		return 'v_student_gates.sort_order, v_student_gates.gate_name';
	}
}//end class PSU\TeacherCert\Student\Gates
