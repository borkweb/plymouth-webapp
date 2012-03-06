<?php

namespace PSU\TeacherCert\Student;

use PSU\TeacherCert;

class ClinicalFaculty extends TeacherCert\ActiveRecord {
	static $table = 'student_clinical_faculty';

	/**
	 * Return the constituent behind this clinical faculty record.
	 */
	public function constituent() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Constituent::get', $this->constituent_id );
	}//end constituent

	public function _prep_args() {
		$args = array(
			'the_id' => $this->id ?: 0,
			'student_gate_system_id' => $this->student_gate_system_id,
			'constituent_id' => $this->constituent_id,
			'association_attribute' => $this->association_attribute,
			'start_date' => $this->start_date,
			'end_date' => $this->end_date,
		);

		return $args;
	}
}//end PSU\TeacherCert\Student\ClinicalFaculty
