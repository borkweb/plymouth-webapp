<?php

namespace PSU\TeacherCert\Student;

use PSU\TeacherCert;

class ClinicalFacultys extends TeacherCert\Collection {
	static $child = 'PSU\\TeacherCert\\Student\\ClinicalFaculty';
	static $parent_key = 'student_gate_system_id';
	static $table = 'student_clinical_faculty';
}//end PSU\TeacherCert\Student\ClinicalFacultys
