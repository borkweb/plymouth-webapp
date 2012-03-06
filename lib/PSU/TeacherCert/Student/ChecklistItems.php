<?php

namespace PSU\TeacherCert\Student;

class ChecklistItems extends \PSU\TeacherCert\Collection {
	static $child = 'PSU\\TeacherCert\\Student\\ChecklistItem';
	static $parent_key = 'student_gate_system_id';
	static $table = 'student_checklist_item_answers';
	static $child_key = null;
	static $join = array(
		array(
			'type' => 'RIGHT JOIN',
			'table' => 'psu_teacher_cert.v_student_checklist_answers',
			'fields' => array(
				array( 
					'logic' => 'AND',
					'field1' => 'student_checklist_item_answers.student_gate_system_id',
					'field2' => 'v_student_checklist_answers.student_gate_system_id',
				),
				array( 
					'logic' => 'AND',
					'field1' => 'student_checklist_item_answers.checklist_item_id',
					'field2' => 'v_student_checklist_answers.checklist_item_id',
				),
			),
		),
	);

	protected function _get_order() {
		return 'v_student_checklist_answers.checklist_item_name';
	}
}//end class PSU\TeacherCert\Student\ChecklistItems
